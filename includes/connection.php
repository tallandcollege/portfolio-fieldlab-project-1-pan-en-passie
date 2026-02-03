<?php
$host = 'localhost';
$dbname = 'pan_en_passie';
$username = 'root'; 
$password = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
} catch (PDOException $e) {
    die("Databaseverbinding mislukt: " . $e->getMessage());
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