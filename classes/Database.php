<?php
/**
 * Database connection class
 */
class Database 
{
    private $connection;
    private $config;
    
    public function __construct(array $config) 
    {
        $this->config = $config;
        $this->connect();
    }
    
    private function connect() 
    {
        try {
            $this->connection = new PDO(
                $this->config['dsn'],
                $this->config['user'],
                $this->config['pass'],
                $this->config['options']
            );
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    public function getConnection(): PDO 
    {
        return $this->connection;
    }
    
    public function beginTransaction(): bool 
    {
        return $this->connection->beginTransaction();
    }
    
    public function commit(): bool 
    {
        return $this->connection->commit();
    }
    
    public function rollback(): bool 
    {
        return $this->connection->rollback();
    }
}