<?php
session_start();
$pdo = include('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $firstname = trim($_POST['firstname'] ?? '');
    $lastname  = trim($_POST['lastname'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $role_id   = (int)($_POST['role_id'] ?? 0);

    if (!$firstname || !$lastname || !$username || !$email || !$password || !$role_id) {
        $error = 'Alle velden zijn verplicht.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users 
            (firstname, lastname, username, email, passwordhash, role_id)
            VALUES (:firstname, :lastname, :username, :email, :passwordhash, :role_id)
        ");

        $stmt->execute([
            ':firstname' => $firstname,
            ':lastname' => $lastname,
            ':username' => $username,
            ':email' => $email,
            ':passwordhash' => $hash,
            ':role_id' => $role_id
        ]);
    }
}


