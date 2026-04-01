<?php
include_once "Includes/connection.php";

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

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
            header("Location: index.php");
            exit;
        } else {
            $error = "Onjuiste inloggegevens.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – Pan en Passie</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include(__DIR__ . "/Includes/header.php"); ?>
    <main class="container-fluid d-flex justify-content-center align-items-center vh-100">
        <section class="login p-4 shadow rounded" aria-labelledby="login-title">

            <h1 id="login-title" class="mb-4 text-center">Login</h1>

            <?php
            if (isset($_GET['error'])) {
                echo '<div class="alert alert-danger" role="alert">'
                    . htmlspecialchars($_GET['error']) .
                    '</div>';
            }
            ?>

            <form method="POST" novalidate>

                <!-- Gebruikersnaam -->
                <div class="mb-3">
                    <label for="username" class="form-label">
                        Gebruikersnaam
                    </label>
                    <input id="username" name="username" class="form-control" requiredaria-required="true"
                        autocomplete="username">
                </div>

                <!-- Wachtwoord -->
                <div class="mb-3">
                    <label for="password" class="form-label">
                        Wachtwoord
                    </label>

                    <div class="input-group">
                        <input type="password" id="password" name="password" class="form-control" required
                            aria-required="true" autocomplete="current-password">

                        <button type="button" class="btn btn-outline-secondary" id="togglePassword"
                            aria-label="Toon of verberg wachtwoord" aria-pressed="false">
                            <i class="bi bi-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Inloggen
                </button>

            </form>

        </section>
    </main>

    <script>
        const toggleButton = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const icon = toggleButton.querySelector('i');

        toggleButton.addEventListener('click', () => {
            const isHidden = passwordInput.type === 'password';

            passwordInput.type = isHidden ? 'text' : 'password';
            toggleButton.setAttribute('aria-pressed', String(isHidden));

            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        });
    </script>
    <?php include(__DIR__ . "/Includes/footer.php"); ?>
</body>

</html>
