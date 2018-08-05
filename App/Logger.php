<?php

namespace App;

class Logger
{
    /*
        // Cheat sheet :) (source: PHP Manual)
        LOG_EMERG	    system is unusable
        LOG_ALERT	    action must be taken immediately
        LOG_CRIT	    critical conditions
        LOG_ERR	        error conditions
        LOG_WARNING	    warning conditions
        LOG_NOTICE	    normal, but significant, condition
        LOG_INFO	    informational message
        LOG_DEBUG	    debug-level message
     */
    static public function log($severity, $message) {
        self::open();
        self::create($severity, $message);
        self::close();
    }

    static private function create($severity, $message) {
        syslog($severity, $message);
    }

    static private function open() {
        $config = new Config();
        $appName = $config->appName;
        openlog($appName, LOG_PID | LOG_PERROR, LOG_LOCAL0);
    }

    static private function close() {
        closelog();
    }

    static public function errors(array $errors) {
        self::open();
        foreach ($errors as $error) {
            self::create(LOG_ERR, $error);
        }
        self::close();
    }

}