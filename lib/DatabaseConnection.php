<?php
include_once dirname(__FILE__) . '/../config/database.php';

class DatabaseConnection {
    private $conn = null;

    public function connect() {
        if ($this->conn == null) {
            $this->conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
            
            if ($this->conn->connect_error) {
                die("Connection failed: " . $this->conn->connect_error);
            }
        }
        return $this->conn;
    }

    public function close() {
        if ($this->conn != null) {
            $this->conn->close();
            $this->conn = null;
        }
    }
}
?>
