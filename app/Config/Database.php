<?php
namespace App\Config;

use PDO;
use PDOException;

class Database {
    private string $host   = 'localhost';
    private string $dbname = 'nutrihealth';
    private string $user   = 'root';
    private string $port   = '3306';
    private string $pass   = '';
    private ?PDO $conn     = null;

    public function getConnection(): PDO {
        if ($this->conn === null) {
            try {
                $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset=utf8mb4";
                $this->conn = new PDO($dsn, $this->user, $this->pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false, // evita SQL injection em alguns casos bizarros
                ]);
            } catch (PDOException $e) {
                die('Connection error: ' . $e->getMessage());
            }
        }
        return $this->conn;
    }
}
