<?php
// filepath: c:\Users\55199\Downloads\pa_final\pa_final\src\db.php

class Database {
    private $host = 'localhost';
    private $db   = 'energia';
    private $user = 'root';
    private $pass = '';
    private $charset = 'utf8mb4';

    public function connect() {
        $dsn = "mysql:host=$this->host;dbname=$this->db;charset=$this->charset";
        try {
            $pdo = new PDO($dsn, $this->user, $this->pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            echo 'Erro de conexão: ' . $e->getMessage();
            exit;
        }
    }
}

function connectDatabase() {
    $host = 'localhost'; // Change if your database is hosted elsewhere
    $db = 'energia';
    $user = 'root'; // Replace with your database username
    $pass = ''; // Replace with your database password
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
}

function disconnectDatabase($pdo) {
    $pdo = null;
}
?>