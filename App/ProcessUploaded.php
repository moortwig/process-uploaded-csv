<?php

namespace App;

use App\Config;
use App\Database;
use App\File;

class ProcessUploaded
{
    protected $fileHandler;

    public function __construct(File $fileHandler)
    {
        $this->fileHandler = $fileHandler;
    }


    public function initiate()
    {
        return 'initiate' . PHP_EOL;
    }

    public function handle()
    {
        $files = $this->fileHandler->getFiles(); // will always be an array. No files found === empty array

        // Validate the contents
        $rules = [
            'eventDatetime'     => ['timestamp', 'format=yyyy-mm-dd hh:mm:ss', 'required'],
            'eventAction'       => ['string', 'min=1', 'max=20', 'required'],
            'callRef'           => ['integer', 'required'],
            'eventValue'        => ['decimal'],
            'eventCurrencyCode' => ['string', 'length=3', 'required_if=eventValue'],
        ];
        $validator = new Validator();

        foreach ($files as $file) {
            $path = $this->fileHandler->getPath('uploaded');
            $handle = $this->fileHandler->open($path . $file, 'r');

            $content = [
                $file => $this->getContent($handle, $file),
            ]; // will be empty array if content is wrongly formatted

            if (count($content[$file]) > 0) {
                $errors = $validator->validate($content,
                    $rules); // if there are NO errors in a file, this will be an empty array
                if (count($errors) > 0) {
                    Logger::errors($errors);
                    $this->fileHandler->moveFileTo('uploaded', 'failed', $file);
                } else {
                    Logger::log(LOG_INFO, 'File: ' . $file . ' has passed validation successfully.');
                    $this->processFile($content);
                }
            }
        }
    }

    private function processFile($file)
    {
        $fileName = key($file);
        $db = new Database();
        foreach ($file[$fileName] as $row) {
            try {
                $query = $db
                    ->connect()
                    ->prepare('INSERT into uploads (event_datetime, event_action, call_ref, event_value, event_currency_code) VALUES (?,?,?,?,?)');
                $query->execute(array_values($row)); // array_values, because it doesn't like "keyed" arrays
            } catch (\PDOException $e) {
                dd($e->getMessage());
            }
        }
        $this->fileHandler->moveFileTo('uploaded', 'processed', $fileName);
    }


    /**
     * Extract data from csv files.
     * This will also check for any tab characters in the file and send them over to Failed folder, if present.
     * NOTE: I'm making the assumption tabs are not used within otherwise valid data, eg "
     *
     * @param $handle
     * @param $fileName
     * @return array
     */
    private function getContent($handle, $fileName)
    {
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
            $this->fileHandler->close($handle);
        }

        if (count($failed) > 0) {
            $this->fileHandler->moveFileTo('uploaded', 'failed', $fileName);
            Logger::log(LOG_ERR, 'File: ' . $fileName . ' | Not a CSV file.');

            return []; // if a single row failed, we move the file to Failed and should therefore return an empty array
        }

        return $collection;
    }


    /** Lock file */
    protected function lockProcess()
    {
        // TODO
    }
}