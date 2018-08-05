<?php

namespace App;

use App\Config;
use App\Database;
use ErrorException;

class ProcessUploaded
{
    public function initiate()
    {
        return 'initiate' . PHP_EOL;
    }

    public function handle()
    {
        $files = $this->getFiles(); // will always be an array. No files found === empty array

        // Validate the contents
        $rules = [
            'eventDatetime'     => ['timestamp', 'format=yyyy-mm-dd hh:mm:ss', 'required'],
            'eventAction'       => ['string', 'min=1', 'max=20', 'required'],
            'callRef'           => ['integer', 'required'],
            'eventValue'        => ['decimal'],
            'eventCurrencyCode' => ['string', 'length=3', 'required-if:eventValue'],
        ];
        $validator = new Validator();

        foreach ($files as $file) {
            $path = $this->getPath('uploaded');
            $handle = $this->open($path . $file, 'r');

            $content = [$file => $this->getContent($handle)]; // will be empty array if content is wrongly formatted

            if (count($content[$file]) > 0) {
                $errors = $validator->validate($content,
                    $rules); // if there are NO errors in a file, this will be an empty array
                if (count($errors) > 0) {
                    // TODO move to failed
                    Logger::errors($errors);
                } else {
                    $this->processFile($content[$file]);
                    Logger::log(LOG_INFO, 'File: ' . $file . ' has passed validation successfully.');
                }
            } else {
                Logger::log(LOG_ERR, 'File: ' . $file . ' | Validation skipped due to wrong format.');
            }
        }
    }

    private function processFile($file)
    {
        $db = new Database();
        foreach ($file as $row) {
            try {
                $query = $db
                    ->connect()
                    ->prepare('INSERT into uploads (event_datetime, event_action, call_ref, event_value, event_currency_code) VALUES (?,?,?,?,?)');
                $query->execute(array_values($row)); // array_values, because it doesn't like "keyed" arrays
            } catch (\PDOException $e) {
                dd($e->getMessage());
            }
        }
        // TODO move to processed


    }


    /**
     * Extract data from csv files.
     * This will also check for any tab characters in the file and send them over to Failed folder, if present.
     * NOTE: I'm making the assumption tabs are not used within otherwise valid data, eg "
     *
     * @param $handle
     * @return array
     */
    private function getContent($handle)
    {
        // TODO add validation to check that every file has Unix line endings
        $collection = [];
        $header = [];
        $failed = [];

        if ($handle) {
            while (($row = fgetcsv($handle, 1000, ",")) !== false) {

                if (count($header) === 0) {
                    $header = $row;
                } else {
                    if (count($row) > 2) { // There are three types of columns required
                        $collection[] = array_combine($header, $row);
                    } else {
                        if (!is_null($row[0])) { // Exclude blank rows from failed parses
                            $failed[] = $row;
                        }
                    }

                }
            }
            fclose($handle);
        }

        if (count($failed) > 0) {
            // TODO send file to Failed
            // TODO log this

            return []; // if a single row failed, we move the file to Failed and should therefore return an empty array
        }

        return $collection;
    }

    /**
     * Scan for files and filter results from anything besides .csv files.
     *
     * @param $path
     * @return array
     */
    private function scanFolder($path)
    {
        $files = scandir($path);

        return array_filter($files, function ($item) {
            $fileInfo = new \SplFileInfo($item);

            if ($fileInfo->getExtension() === 'csv') {
                return $item;
            }
        });
    }

    /**
     * @return array|bool
     */
    private function getFiles()
    {
        $path = $this->getPath('uploaded');

        return $this->scanFolder($path);
    }

    /*public function db_connect()
    {
        $db = new Database();
        try {
            $query = $db->connect()->query("SELECT * FROM uploads WHERE id = 1");
        } catch (ErrorException  $e) {
            echo $e->getMessage();
        }

        return $query;
    }*/

    /**
     * This is to test crontab works
     */
    public function test_crontab()
    {
        $pid = getmypid();

        syslog(LOG_INFO, 'process initiated');

        return $this->writeToFile($pid, 'crontab_test.log');
    }

    /** Lock file */
    protected function lockProcess()
    {
        // TODO
    }


    /** FILE METHODS */

    /**
     * @param $pid
     * @param $fileName
     * @return mixed
     */
    protected function writeToFile($pid, $fileName)
    {
        $path = $this->getPath();
        $handle = $this->open($path . $fileName, 'a');

        if (is_resource($handle)) {
            fwrite($handle, getmypid() . PHP_EOL);
            $this->close($handle);

            Logger::log(LOG_INFO, 'File has been processed');

            return true;
        } else {
            Logger::log(LOG_ERR, 'Failed to open/create file');

            return false;
        }
    }

    /**
     * @return string
     */
    protected function getPath($folder)
    {
        $config = new Config();
        $path = $config->basePath . $config->{$folder . 'Folder'};

        if (!file_exists($path)) {
            mkdir($path);
        }

        return $path;
    }

    /**
     * @param $fullPath
     * @param $mode
     * @return bool|resource
     */
    protected function open($fullPath, $mode)
    {
        return fopen($fullPath, $mode);
    }

    /**
     *
     * @param $handle
     */
    protected function close($handle)
    {
        fclose($handle);
    }
}