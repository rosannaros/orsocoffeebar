<?php
namespace App\Models;

use PDO;

class Db {
    private $host;
    private $user;
    private $pass;
    private $dbname;

    public function __construct() {
        $this->host   = getenv('DB_HOST') ?: '127.0.0.1';
        $this->user   = getenv('DB_USER') ?: 'root';
        $this->pass   = getenv('DB_PASS') ?: '';
        $this->dbname = getenv('DB_NAME') ?: 'orso_db';
    }

    public function connect() {
        $conn_str = "mysql:host={$this->host};dbname={$this->dbname}";
        $dbConn = new PDO($conn_str, $this->user, $this->pass);
        $dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $dbConn;
    }
}
