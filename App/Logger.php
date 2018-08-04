<?php

namespace App;

class Logger
{
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

}