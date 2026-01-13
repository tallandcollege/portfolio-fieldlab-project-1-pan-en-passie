<?php session_start(); ?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registratie – Pen en Passie</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous">

    <!-- Icons -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include("Includes/header.php"); ?>
    <main class="container-fluid d-flex justify-content-center align-items-center vh-100">
        <section class="registratie p-3 shadow rounded" style="max-width: 50vw;" aria-labelledby="registratie-title">

            <h1 id="registratie-title" class="mb-4 text-center">Account registreren voor een gebruiker</h1>
            <p class="mb-4 text-center">Registreer hier een andere gebruiker, na het klikken op "Gebruiker aanmaken" krijg je een link die je naar de gebruiker kan sturen waar ze hun eigen wachtwoord kunnen aanmaken. </p>
            <?php
            if (isset($_GET['error'])) {
                echo '<div class="alert alert-danger" role="alert">'
                    . htmlspecialchars($_GET['error']) .
                    '</div>';
            }
            ?>

            <form method="POST" action="registratie.php" novalidate>

                <!-- Voornaam -->
                <div class="mb-3">
                    <label for="firstname" class="form-label">Voornaam</label>
                    <input id="firstname" name="firstname" class="form-control" required>
                </div>

                <!-- Achternaam -->
                <div class="mb-3">
                    <label for="lastname" class="form-label">Achternaam</label>
                    <input id="lastname" name="lastname" class="form-control" required>
                </div>

                <!-- Gebruikersnaam -->
                <div class="mb-3">
                    <label for="username" class="form-label">Gebruikersnaam</label>
                    <input id="username" name="username" class="form-control" required autocomplete="username">
                </div>

                <!-- E-mail -->
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input id="email" type="email" name="email" class="form-control" required autocomplete="email">
                </div>

                <!-- Wachtwoord -->
                <div class="mb-3">
                    <label for="password" class="form-label">Tijdelijk wachtwoord</label>

                    <div class="input-group">
                        <input type="password" id="password"
                            name="password"
                            class="form-control"
                            required
                            aria-required="true">

                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            id="togglePassword"
                            aria-label="Toon of verberg wachtwoord"
                            aria-pressed="false">
                            <i class="bi bi-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <!-- Rol -->
                <div class="mb-3">
                    <label for="role_id" class="form-label">Rol</label>
                    <select id="role_id" name="role_id" class="form-select" required>
                        <option value="2">Docent</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success w-100">
                    Gebruiker aanmaken
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
    <?php include("Includes/footer.php"); ?>
</body>

</html>