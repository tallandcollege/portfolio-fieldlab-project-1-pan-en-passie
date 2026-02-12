<?php
session_start();

function connect()
{
    $host = 'localhost';
    $dbname = 'pan_en_passie';
    $username = 'bit_academy';
    $password = 'bit_academy';
    $charset = 'utf8mb4';

    // $host = 'localhost';
    // $dbname = 'st1736424645';
    // $username = 'st1736424645';
    // $password = 'ESibH2t4CMYubiw';
    // $charset = 'utf8mb4';

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


/**
 * Usage:
 *   $data = fetchData("SELECT * FROM users WHERE role_id = :role", [':role' => 2]);
 *   $one  = fetchData("SELECT * FROM users WHERE id = :id", [':id' => 5], true);
 */
function fetchData(string $query, array $params = [], bool $singleRow = false): array
{
    global $pdo; // uses the $pdo from this file

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    if ($singleRow) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row : [];
    }

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function executeQuery(string $query, array $params = []): int {
    global $pdo;
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->rowCount();
}