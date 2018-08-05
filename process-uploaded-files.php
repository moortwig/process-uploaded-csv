<?php

require('autoloader.php');

include_once('App/ProcessUploaded.php');
include_once('App/Database.php');


$process = new \App\ProcessUploaded(new \App\File());
$locked = $process->processIsLocked();

if (!$locked) {
    $locked = $process->lockProcess();
    if ($locked) {
        /** Test Database connection */
        // FIXME this test + create db should ONLY be run once, at the start of the task
        $db = new \App\Database();
        try {
            $connection = $db->testConnection();
        } catch (PDOException $e) {
            if ($e->getCode() === 1049) {
                $db->createIfNotExisting();
            } else {
                \App\Logger::log(LOG_CRIT, 'Pid: ' . getmypid() . ' | ' . $e->getMessage());
                die();
            }
        }

        /** Run the task */
        $process->handle();
        $process->done();
    }
}


