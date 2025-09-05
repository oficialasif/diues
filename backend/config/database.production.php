<?php
/**
 * Production Database Configuration for DIU Esports Community Portal
 * Configured for PostgreSQL on Render
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
        // Load from environment variables (set by render.yaml)
        $this->host = $_ENV['DB_HOST'] ?? 'dpg-d2qcflre5dus73bt42b0-a.oregon-postgres.render.com';
        $this->db_name = $_ENV['DB_NAME'] ?? 'diu_esports_db';
        $this->username = $_ENV['DB_USERNAME'] ?? 'diu_esports_user';
        $this->password = $_ENV['DB_PASSWORD'] ?? 'N9P2tK3xOtsOKnpZqrk1PmtTPO34eFrA';
        $this->port = $_ENV['DB_PORT'] ?? '5432';
        $this->driver = $_ENV['DB_DRIVER'] ?? 'pgsql';
    }

    /**
     * Get database connection
     */
    public function getConnection() {
        $this->conn = null;

        try {
            if ($this->driver === 'pgsql') {
                // PostgreSQL connection
                $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db_name}";
                $this->conn = new PDO($dsn, $this->username, $this->password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]);
            } else {
                // MySQL connection (fallback)
                $dsn = "mysql:host={$this->host};dbname={$this->db_name}";
                $this->conn = new PDO($dsn, $this->username, $this->password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            }

            error_log("Database connection established successfully");
            return $this->conn;

        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
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
            
            // Convert boolean values to PostgreSQL format
            $processedParams = [];
            foreach ($params as $param) {
                if (is_bool($param)) {
                    $processedParams[] = $param ? 'true' : 'false';
                } else {
                    $processedParams[] = $param;
                }
            }
            
            $result = $stmt->execute($processedParams);
            return $result ? $conn->lastInsertId() : false;
        } catch(PDOException $e) {
            error_log("Execute failed: " . $e->getMessage() . " SQL: " . $sql);
            throw new Exception("Database operation failed: " . $e->getMessage());
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
