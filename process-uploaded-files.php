<?php

require('autoloader.php');

include_once('App/ProcessUploaded.php');
include_once('App/Database.php');

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

/** Initiate the task */
$process = new \App\ProcessUploaded(new \App\File());

$locked = false; // FIXME  dummy



if (!$locked) {
    /** Run the task */
    $process->handle();
}

