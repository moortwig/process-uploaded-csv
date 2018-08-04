<?php

require('autoloader.php');
/** Includes */
include_once('App/ProcessUploaded.php');


/** Initiate the task */
$process = new \App\ProcessUploaded();

$locked = false; // FIXME  dummy

if (!$locked) {
    $process->handle();
}

// TODO
//  Process files (done in separate class)
//  When processed, move file to Processed folder (create if not existing)
//  If file failed, move file to Failed

