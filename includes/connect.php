<?php
session_start();

function connect() {
    $host = 'localhost';
    $dbname = 'pan_en_passie';
    $username = 'bit_academy';
    $password = 'bit_academy';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

    try {
        $pdo = new PDO($dsn, $username, $password);
        // Set the PDO error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo; // <-- Return the PDO object!
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}
?>
