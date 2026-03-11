<?php
/**
 * Database Connection Class
 * Wellnest Mental Health Web Application
 * 
 * Uses PDO for secure database connectivity
 */

class Database {
    private static ?PDO $instance = null;
    
    private const HOST = 'localhost';
    private const DB_NAME = 'griffin_wellnest_db';
    private const USERNAME = 'root';
    private const PASSWORD = ''; // Default XAMPP has no password
    private const CHARSET = 'utf8mb4';
    
    /**
     * Get database connection (Singleton pattern)
     * @return PDO
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    "mysql:host=%s;dbname=%s;charset=%s",
                    self::HOST,
                    self::DB_NAME,
                    self::CHARSET
                );
                
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_PERSISTENT         => true
                ];
                
                self::$instance = new PDO($dsn, self::USERNAME, self::PASSWORD, $options);
                
            } catch (PDOException $e) {
                // Log error (don't expose details in production)
                error_log("Database Connection Error: " . $e->getMessage());
                throw new Exception("Database connection failed. Please try again later.");
            }
        }
        
        return self::$instance;
    }
    
    /**
     * Execute a query with parameters
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind
     * @return PDOStatement
     */
    public static function query(string $sql, array $params = []): PDOStatement {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    /**
     * Fetch single row
     * @param string $sql SQL query
     * @param array $params Parameters to bind
     * @return array|false
     */
    public static function fetchOne(string $sql, array $params = []): array|false {
        return self::query($sql, $params)->fetch();
    }
    
    /**
     * Fetch all rows
     * @param string $sql SQL query
     * @param array $params Parameters to bind
     * @return array
     */
    public static function fetchAll(string $sql, array $params = []): array {
        return self::query($sql, $params)->fetchAll();
    }
    
    /**
     * Insert and return last insert ID
     * @param string $sql SQL query
     * @param array $params Parameters to bind
     * @return int Last insert ID
     */
    public static function insert(string $sql, array $params = []): int {
        self::query($sql, $params);
        return (int) self::getConnection()->lastInsertId();
    }
    
    /**
     * Begin transaction
     */
    public static function beginTransaction(): void {
        self::getConnection()->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public static function commit(): void {
        self::getConnection()->commit();
    }
    
    /**
     * Rollback transaction
     */
    public static function rollback(): void {
        self::getConnection()->rollBack();
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
