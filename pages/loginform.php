<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - pen en passie</title>
    <style>
    </style>
</head>

<body>


    <?php
    session_start();
    if (isset($_GET['error'])) {
        echo "<p class='error'>" . htmlspecialchars($_GET['error']) . "</p>";
    }
    ?>
    <div class="container-fluid d-flex justify-content-center align-item-center">
        <div class="row">
            <h1>Login</h1>
            <div class="login">

                <form method="POST" action="../php/login.php" class="mb-4">
                    <p>
                        <label>Gebruikersnaam:</label>
                        <input type="text" name="username" class="form-control" required>

                        <label>Wachtwoord:</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                        <i class="bi bi-eye" id="togglepassword"></i>
                    </p>

                    <button type="submit">Inloggen</button>
                    <a href="register_form.php" class="button"> Registratie </a>
                </form>

            </div>
        </div>
    </div>
    <script>
        const togglepassword = document.querySelector('#togglepassword');
        const password = document.querySelector('#password');
        togglepassword.addEventListener('click', (e) => {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            e.target.classList.toggle('bi-eye');
            e.target.classList.toggle('bi-eye-slash');
        });
    </script>
</body>

</html>