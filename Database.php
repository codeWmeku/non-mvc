<?php

class Database {
    private $host = 'localhost';
    private $db_name = 'bayanihub';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function __construct()
    {
        $this->host = getenv('DB_HOST') ?: $this->host;
        $this->db_name = getenv('DB_NAME') ?: $this->db_name;
        $this->username = getenv('DB_USER') ?: $this->username;
        $this->password = getenv('DB_PASS') ?: $this->password;
    }

    public function connect()
    {
        if ($this->conn) {
            return $this->conn;
        }

        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage(), 3, __DIR__ . '/logs/database.log');
            die("An error occurred while connecting to the database.");
        }

        return $this->conn;
    }
}
?>
