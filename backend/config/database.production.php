<?php
/**
 * Production Database Configuration for DIU Esports Community
 * Supports both MySQL and PostgreSQL with environment variables
 */

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    private $driver;
    private $conn;
    
    public function __construct() {
        // Get database configuration from environment variables
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->db_name = $_ENV['DB_NAME'] ?? 'diu_esports';
        $this->username = $_ENV['DB_USERNAME'] ?? 'root';
        $this->password = $_ENV['DB_PASSWORD'] ?? '';
        $this->port = $_ENV['DB_PORT'] ?? '';
        $this->driver = $_ENV['DB_DRIVER'] ?? 'mysql'; // mysql or pgsql
    }

    /**
     * Get database connection
     */
    public function getConnection() {
        if ($this->conn === null) {
            try {
                if ($this->driver === 'pgsql') {
                    // PostgreSQL connection
                    $dsn = "pgsql:host={$this->host}";
                    if ($this->port) {
                        $dsn .= ";port={$this->port}";
                    }
                    $dsn .= ";dbname={$this->db_name}";
                    
                    $this->conn = new PDO($dsn, $this->username, $this->password);
                } else {
                    // MySQL connection
                    $dsn = "mysql:host={$this->host}";
                    if ($this->port) {
                        $dsn .= ";port={$this->port}";
                    }
                    $dsn .= ";dbname={$this->db_name};charset=utf8mb4";
                    
                    $this->conn = new PDO($dsn, $this->username, $this->password);
                    $this->conn->exec("set names utf8mb4");
                }
                
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                
            } catch(PDOException $exception) {
                error_log("Database connection error: " . $exception->getMessage());
                throw new Exception("Database connection failed. Please check your configuration.");
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
            error_log("Query failed: " . $e->getMessage());
            throw new Exception("Database query failed");
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
            error_log("Execute failed: " . $e->getMessage());
            throw new Exception("Database operation failed");
        }
    }
    
    /**
     * Begin a database transaction
     */
    public function beginTransaction() {
        try {
            $conn = $this->getConnection();
            return $conn->beginTransaction();
        } catch(PDOException $e) {
            error_log("Transaction failed: " . $e->getMessage());
            throw new Exception("Transaction failed");
        }
    }
    
    /**
     * Commit a database transaction
     */
    public function commit() {
        try {
            $conn = $this->getConnection();
            return $conn->commit();
        } catch(PDOException $e) {
            error_log("Commit failed: " . $e->getMessage());
            throw new Exception("Commit failed");
        }
    }
    
    /**
     * Rollback a database transaction
     */
    public function rollback() {
        try {
            $conn = $this->getConnection();
            return $conn->rollback();
        } catch(PDOException $e) {
            error_log("Rollback failed: " . $e->getMessage());
            throw new Exception("Rollback failed");
        }
    }

    /**
     * Get database driver type
     */
    public function getDriver() {
        return $this->driver;
    }

    /**
     * Test database connection
     */
    public function testConnection() {
        try {
            $conn = $this->getConnection();
            return [
                'success' => true,
                'driver' => $this->driver,
                'database' => $this->db_name,
                'host' => $this->host
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
?>
