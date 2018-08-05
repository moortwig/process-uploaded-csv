<?php

namespace App;

/** Copy this file into a new file, removing underscore from both file and class name. */
class _Config
{
    // DATABASE PARAMETERS
    public $dbHost = 'db_host';
    public $dbUser = 'username';
    public $dbPass = 'password';
    public $dbName = 'db_name';

    // PATH PARAMETERS
    public $appName = 'App';
    public $basePath;

    // APP FILESYSTEM
    public $lockFolder = 'tmp/';
    public $uploadedFolder = 'files/uploaded/';
    public $processedFolder = 'files/processed/';
    public $failedFolder = 'files/failed/';


    function __construct()
    {
        $this->basePath = $this->getBasePath();
    }

    private function getBasePath()
    {
        $length = strlen($this->appName);
        $basePath = substr(__DIR__, 0, -$length);

        return $basePath;
    }
}