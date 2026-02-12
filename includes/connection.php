<?php
session_start();

function connect(): PDO
{
    $host = 'localhost';
    $dbname = 'pan_en_passie';
    $username = 'bit_academy';
    $password = 'bit_academy';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;
}


$pdo = connect();

/**
 * Usage:
 *   $data = fetchData("SELECT * FROM users WHERE role_id = :role", [':role' => 2]);
 *   $one  = fetchData("SELECT * FROM users WHERE id = :id", [':id' => 5], true);
 */
function fetchData(string $query, array $params = [], bool $singleRow = false): array
{
    global $pdo;

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    if ($singleRow) {
        $row = $stmt->fetch();
        return $row ? $row : [];
    }

    return $stmt->fetchAll();
}

function executeQuery(string $query, array $params = []): int
{
    global $pdo;

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->rowCount();
}
