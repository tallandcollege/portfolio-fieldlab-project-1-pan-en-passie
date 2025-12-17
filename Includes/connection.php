<?php

$host = 'localhost';
$dbname = 'pan_en_passie';
$username = 'bit_academy';
$password = 'bit_academy';
$charset = 'utf8mb4';

$dsn = "mysql:host=" . $host . ";dbname=" . $dbname . ";";

try {
    $pdo = new PDO($dsn, $username, $password);
    // set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "<h5>Connection Succesful</h5>";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}