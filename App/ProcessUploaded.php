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
        return getmypid();
//        return $this->db_connect();
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
    protected function getPath()
    {
        $config = new Config();
        $path = $config->basePath . $config->storageFolder;

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