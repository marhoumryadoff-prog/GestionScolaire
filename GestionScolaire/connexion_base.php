<?php
class ConnexionBase {
    public $pdo;  // غيرت من private إلى public
    
    public function __construct() {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "base_etudiants_tp2_2025";
        
        try {
            $this->pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
}
?>