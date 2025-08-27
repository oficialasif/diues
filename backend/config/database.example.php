<?php
/**
 * Database Configuration Template for DIU Esports Community
 * 
 * IMPORTANT: Copy this file to database.php and update with your actual database credentials
 * DO NOT commit the actual database.php file with real credentials to GitHub
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'your_database_name';
    private $username = 'your_username';
    private $password = 'your_password';
    private $conn;

    /**
     * Get database connection
     */
    public function getConnection() {
        if ($this->conn === null) {
            try {
                $this->conn = new PDO(
                    "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                    $this->username,
                    $this->password
                );
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->exec("set names utf8");
            } catch(PDOException $exception) {
                throw new Exception("Connection error: " . $exception->getMessage());
            }
        }

        return $this->conn;
    }

    /**
     * Close database connection
     */
    public function closeConnection() {
        $this->conn = null;
    }

    /**
     * Execute a query and return results
     */
    public function query($sql, $params = []) {
        try {
            $conn = $this->getConnection();
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }

    /**
     * Execute a query and return single row
     */
    public function querySingle($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Execute a query and return all rows
     */
    public function queryAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Execute INSERT, UPDATE, DELETE queries
     */
    public function execute($sql, $params = []) {
        try {
            $conn = $this->getConnection();
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute($params);
            return $result ? $conn->lastInsertId() : false;
        } catch(PDOException $e) {
            throw new Exception("Execute failed: " . $e->getMessage());
        }
    }
}

// Create database instance
$database = new Database();
?>
