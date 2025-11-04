<?php
namespace App\Config;
use PDO;
use PDOException;
class Database {
    private string $host='localhost';
    private string $dbname='nutrihealth';
    private string $user='root';
    private string $port='3307';
    private string $pass='';
    private ?PDO $conn=null;
    public function getConnection(): PDO {
        if ($this->conn===null){
            try{
                $dsn="mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset=utf8mb4";
                $this->conn=new PDO($dsn,$this->user,$this->pass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
            }catch(PDOException $e){ die("Erro de conexão: ".$e->getMessage()); }
        }
        return $this->conn;
    }
}
