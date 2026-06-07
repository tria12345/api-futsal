<?php
// backend/config/database.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

class Database {
    private $host = "localhost";
    private $db_name = "futsal_reserve";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        // Auto-detect Clever Cloud MySQL environment variables
        if (getenv("MYSQL_ADDON_HOST")) {
            $this->host = getenv("MYSQL_ADDON_HOST");
            $this->db_name = getenv("MYSQL_ADDON_DB");
            $this->username = getenv("MYSQL_ADDON_USER");
            $this->password = getenv("MYSQL_ADDON_PASSWORD");
        }

        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo json_encode(["status" => "error", "message" => "Database connection error: " . $exception->getMessage()]);
            exit();
        }
        return $this->conn;
    }
}
?>
