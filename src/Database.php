<?php

namespace Src;

class Database {
    private $host;
    private $port;
    private $db;
    private $user;
    private $pass;
    
    private $dbConnection = null;

    public function __construct() {
        $this->host = $_ENV['DB_HOST'];
        $this->port = $_ENV['DB_PORT']; // Assuming you have a 'DB_PORT' environment variable
        $this->db   = $_ENV['DB_DATABASE'];
        $this->user = $_ENV['DB_USERNAME'];
        $this->pass = $_ENV['DB_PASSWORD'];

        try {
            $this->dbConnection = new \mysqli($this->host, $this->user, $this->pass, $this->db);
            if ($this->dbConnection->connect_error) {
                die("Connection failed: " . $this->dbConnection->connect_error);
            }
        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    public function connect() {
        return $this->dbConnection;
    }
}