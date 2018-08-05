<?php

namespace App;

use App\Config;
use PDO;
use PDOException;

class Database
{
    public $db_conn;

    private $dbHost;
    private $dbName;
    private $dbUser;
    private $dbPass;

    public function __construct()
    {
        $config = new Config();

        $this->dbHost = $config->dbHost;
        $this->dbName = $config->dbName;
        $this->dbUser = $config->dbUser;
        $this->dbPass = $config->dbPass;
    }

    /**
     * Connect to database
     *
     * @return PDO
     */
    public function connect()
    {
        try {
            $this->db_conn = new PDO(
                "mysql:host=$this->dbHost;dbname=$this->dbName;charset=utf8",
                $this->dbUser,
                $this->dbPass);

            return $this->db_conn;
        } catch (PDOException $e) {
            echo $e->getMessage();
            die();
        }
    }

    /**
     * Test connection. May return a PDOException, if connection fails.
     *
     * @return PDO
     */
    public function testConnection()
    {
        $this->db_conn = new PDO(
            "mysql:host=$this->dbHost;dbname=$this->dbName;charset=utf8",
            $this->dbUser,
            $this->dbPass);

        return $this->db_conn;
    }

    /**
     * Creates a database and table, if they don't exist already.
     */
    public function createIfNotExisting()
    {
        try {
            $dbh = new PDO("mysql:host=$this->dbHost", $this->dbUser, $this->dbPass);

            $dbh->exec('CREATE DATABASE IF NOT EXISTS `' . $this->dbName . '`');
            $dbh->exec('use ' . $this->dbName);
            $dbh->exec('CREATE TABLE IF NOT EXISTS `uploads` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `event_datetime` datetime NOT NULL,
                  `event_action` varchar(50) COLLATE utf8mb4_bin NOT NULL,
                  `call_ref` int(11) NOT NULL,
                  `event_value` decimal(10,2) DEFAULT NULL,
                  `event_currency_code` varchar(3) COLLATE utf8mb4_bin DEFAULT NULL,
                  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`)
                ) 
                ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;');

        } catch (PDOException $e) {
            dd($e->getMessage());
        }
    }
}
