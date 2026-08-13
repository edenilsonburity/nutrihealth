<?php
namespace App\Config;

use PDO;
use PDOException;

class Database {
    private string $host;
    private string $dbname;
    private string $user;
    private string $port;
    private string $pass;
    private ?PDO $conn = null;

    public function __construct() {
        // Em produ��o (Render), essas vari�veis v�m do ambiente do servi�o.
        // Localmente (XAMPP), caem nos valores padr�o de sempre.
        $this->host   = getenv('DB_HOST')   ?: 'localhost';
        $this->dbname = getenv('DB_NAME')   ?: 'nutrihealth';
        $this->user   = getenv('DB_USER')   ?: 'root';
        $this->port   = getenv('DB_PORT')   ?: '3307';
        $this->pass   = getenv('DB_PASS')   ?: '';
    }

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
