<?php
session_start();
$pdo = include('config.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!$username || !$password) {
        $error = "Vul gebruikersnaam en wachtwoord in.";
    } else {

        $stmt = $pdo->prepare("
            SELECT u.id, u.firstname, u.passwordhash, r.name AS role
            FROM users u
            JOIN role r ON u.role_id = r.id
            WHERE u.username = :username
            LIMIT 1
        ");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['passwordhash'])) {

            // Session zetten
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['name']     = $user['firstname'];
            $_SESSION['role']     = $user['role'];

            // Doorsturen op basis van rol
            switch ($user['role']) {
                case 'admin':
                    header("Location: homapage.php");
                    break;
                case 'teacher':
                    header("Location: homepage.php");
                    break;
                default:
                    header("Location: hoempage.php");
            }
            exit;
        } else {
            $error = "Onjuiste inloggegevens.";
        }
    }
}
