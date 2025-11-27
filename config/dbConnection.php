<?php
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "estancia";
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        

    } catch (PDOException $e) {
        die("Conexión fallida: " . $e->getMessage());
    }
    function conectar() {
        $host = "localhost";
        $user = "root";
        $pass = "";
        $dbname = "estancia";
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
            
            return new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            
            die("Conexión fallida: " . $e->getMessage());
        }
    }
?>