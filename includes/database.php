<?php
class Database {
    private $host = 'database';
    private $user = 'developer';
    private $pass = 'dev_password';
    private $dbname = 'progetto_database';
    
    private $conn;
    
    public function connect() {
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname}", 
                $this->user, 
                $this->pass
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        } catch(PDOException $e) {
            echo "Errore connessione: " . $e->getMessage();
            return null;
        }
    }
}
?>