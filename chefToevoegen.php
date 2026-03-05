<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: index.php");
    exit;
}

function addErrorCode(array &$errors, string $field, string $code): void
{
    if (!isset($errors[$field]) || !in_array($code, $errors[$field], true)) {
        $errors[$field][] = $code;
    }
}

function generateToken(int $bytes = 24): string
{
    return rtrim(strtr(base64_encode(random_bytes($bytes)), '+/', '-_'), '=');
}

function errorMessage(string $code): string
{
    return match ($code) {
        'required'          => 'Dit veld is verplicht.',
        'email_invalid'     => 'Vul een geldig e-mailadres in.',
        'role_invalid'      => 'Ongeldige rol gekozen.',
        'username_taken'    => 'Gebruikersnaam is al in gebruik.',
        'email_taken'       => 'Dit e-mailadres is al in gebruik.',
        'token_failed'      => 'Kon geen unieke sleutel genereren, probeer opnieuw.',
        'db_error'          => 'Database fout.',
        default             => 'Er ging iets mis.',
    };
}

$errors = $_SESSION['form_errors'] ?? [];
$old    = $_SESSION['form_old'] ?? [
    'firstname' => '',
    'lastname'  => '',
    'username'  => '',
    'email'     => '',
    'role_id'   => '1',
];

unset($_SESSION['form_errors'], $_SESSION['form_old']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $firstname = trim((string)($_POST['firstname'] ?? ''));
    $lastname  = trim((string)($_POST['lastname'] ?? ''));
    $newusername  = trim((string)($_POST['username'] ?? ''));
    $newemail     = trim((string)($_POST['email'] ?? ''));
    $role_id   = trim((string)($_POST['role_id'] ?? '1'));

    $old = [
        'firstname' => $firstname,
        'lastname'  => $lastname,
        'username'  => $newusername,
        'email'     => $newemail,
        'role_id'   => $role_id,
    ];

    $errors = [];

    foreach (['firstname', 'lastname', 'username', 'email', 'role_id'] as $field) {
        if (trim((string)($_POST[$field] ?? '')) === '') {
            addErrorCode($errors, $field, 'required');
        }
    }

    if ($newemail !== '' && !filter_var($newemail, FILTER_VALIDATE_EMAIL)) {
        addErrorCode($errors, 'email', 'email_invalid');
    }

    $roleInt = (int)$role_id;
    if (!in_array($roleInt, [1, 2, 3], true)) {
        addErrorCode($errors, 'role_id', 'role_invalid');
    }

    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_old']    = $old;
        header("Location: chefToevoegen.php");
        exit;
    }

    include __DIR__ . "/Includes/connection.php";

    $stmt = $pdo->prepare("
    SELECT username, email
    FROM NaN_account
    WHERE username = :username OR email = :email");
    $stmt->execute([
        ':username' => $newusername,
        ':email'    => $newemail,
    ]);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (strcasecmp($row['username'], $newusername) === 0) {
            addErrorCode($errors, 'username', 'username_taken');
        }
        if (strcasecmp($row['email'], $newemail) === 0) {
            addErrorCode($errors, 'email', 'email_taken');
        }
    }


    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_old']    = $old;
        header("Location: chefToevoegen.php");
        exit;
    }

    $token = null;

    for ($attempt = 0; $attempt < 5; $attempt++) {
        $token = generateToken();
        $tokenHash = hash('sha256', $token);

        $stmt = $pdo->prepare("
            INSERT INTO NaN_account
            (firstname, lastname, username, email, UserKey, role_id)
            VALUES (:firstname, :lastname, :username, :email, :UserKey, :role_id)
        ");

        try {
            $stmt->execute([
                ':firstname' => $firstname,
                ':lastname'  => $lastname,
                ':username'  => $newusername,
                ':email'     => $newemail,
                ':UserKey'   => $tokenHash,
                ':role_id'   => $roleInt,
            ]);
            break;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $msg = $e->getMessage();

                // Token collision? -> nieuwe token proberen
                if (stripos($msg, 'UserKey') !== false) {
                    $token = null;
                    continue;
                }

                $isDuplicate = false;

                if (stripos($msg, 'username') !== false) {
                    addErrorCode($errors, 'username', 'username_taken');
                    $isDuplicate = true;
                }
                if (stripos($msg, 'email') !== false) {
                    addErrorCode($errors, 'email', 'email_taken');
                    $isDuplicate = true;
                }

                // Alleen db_error als we NIET hebben herkend dat het om duplicate username/email gaat
                if (!$isDuplicate) {
                    addErrorCode($errors, 'general', 'db_error');
                }

                $token = null;
                break;
            }

            addErrorCode($errors, 'general', 'db_error');
            $token = null;
            break;
        }
    }

    if ($token === null && empty($errors)) {
        addErrorCode($errors, 'general', 'token_failed');
    }

    $_SESSION['success'] = "https://st1736424645.splsites.nl/activeerAccount.php?key=" . rawurlencode($token);
    header("Location: chefToevoegen.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registratie – Pan en Passie</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include("Includes/header.php"); ?>

    <main class="registratie-main">
        <section class="registratie-section" aria-labelledby="registratie-title">
            <h1 id="registratie-title">Account registreren voor een gebruiker</h1>
            <p>Registreer hier een andere gebruiker; na het klikken op "Gebruiker aanmaken" krijg je een link die je kan sturen zodat ze zelf een wachtwoord kunnen instellen.</p>

            <?php if (isset($errors['general'])): ?>
                <div class="error-message">
                    <?php foreach ($errors['general'] as $code) echo htmlspecialchars(errorMessage($code)) . "<br>"; ?>
                </div>
            <?php endif; ?>

            <form method="POST" novalidate>

                <div>
                    <label for="firstname" class="form-label">Voornaam</label>
                    <input id="firstname" name="firstname" value="<?= htmlspecialchars($old['firstname']) ?>" required>
                    <?php
                    if (isset($errors['firstname'])) {
                        foreach ($errors['firstname'] as $code) {
                            echo '<div class="error-message">' . htmlspecialchars(errorMessage($code)) . '</div>';
                        }
                    }
                    ?>
                </div>

                <div>
                    <label for="lastname" class="form-label">Achternaam</label>
                    <input id="lastname" name="lastname" value="<?= htmlspecialchars($old['lastname']) ?>" required>
                    <?php
                    if (isset($errors['lastname'])) {
                        foreach ($errors['lastname'] as $code) {
                            echo '<div class="error-message">' . htmlspecialchars(errorMessage($code)) . '</div>';
                        }
                    }
                    ?>
                </div>

                <div>
                    <label for="username" class="form-label">Gebruikersnaam</label>
                    <input id="username" name="username" autocomplete="username" value="<?= htmlspecialchars($old['username']) ?>" required>
                    <?php
                    if (isset($errors['username'])) {
                        foreach ($errors['username'] as $code) {
                            echo '<div class="error-message">' . htmlspecialchars(errorMessage($code)) . '</div>';
                        }
                    }
                    ?>
                </div>

                <div>
                    <label for="email" class="form-label">E-mail</label>
                    <input id="email" type="email" name="email" autocomplete="email" value="<?= htmlspecialchars($old['email']) ?>" required>
                    <?php
                    if (isset($errors['email'])) {
                        foreach ($errors['email'] as $code) {
                            echo '<div class="error-message">' . htmlspecialchars(errorMessage($code)) . '</div>';
                        }
                    }
                    ?>
                </div>

                <div>
                    <label for="role_id" class="form-label">Rol</label>
                    <select id="role_id" name="role_id" required>
                        <option value="1" <?= $old['role_id'] === '1' ? 'selected' : '' ?>>Student</option>
                        <option value="2" <?= $old['role_id'] === '2' ? 'selected' : '' ?>>Docent</option>
                        <option value="3" <?= $old['role_id'] === '3' ? 'selected' : '' ?>>Admin</option>
                    </select>
                    <?php
                    if (isset($errors['role_id'])) {
                        foreach ($errors['role_id'] as $code) {
                            echo '<div class="error-message">' . htmlspecialchars(errorMessage($code)) . '</div>';
                        }
                    }
                    ?>
                </div>

                <button type="submit" class="registratie-submit">Gebruiker aanmaken</button>

                <?php if (isset($_SESSION['success']))
                    echo '
                    <div>
                        <label for="Succes" class="form-label">
                            Gebruiker succesvol aangemaakt. Stuur deze link naar de gebruiker om hun wachtwoord in te stellen:
                        </label>
                        <input id="Succes" type="text" value="' . htmlspecialchars($_SESSION['success']) . '" readonly>
                        <button id="copyBtn" type="button">Copy</button>
                        <div id="msg" aria-live="polite"></div>
                    </div>';
                ?>

                <script>
                    (() => {
                        const inputEl = document.getElementById("Succes");
                        const btnEl = document.getElementById("copyBtn");
                        const msgEl = document.getElementById("msg");
                        if (!inputEl || !btnEl) return;

                        const flashGreen = () => {
                            const oldBg = btnEl.style.backgroundColor;
                            const oldColor = btnEl.style.color;

                            btnEl.style.backgroundColor = "green";
                            btnEl.style.color = "white";

                            clearTimeout(btnEl._copyTimer);
                            btnEl._copyTimer = setTimeout(() => {
                                btnEl.style.backgroundColor = oldBg;
                                btnEl.style.color = oldColor;
                            }, 1200);
                        };

                        btnEl.addEventListener("click", async () => {
                            const text = inputEl.value;

                            try {
                                await navigator.clipboard.writeText(text);
                                flashGreen();
                            } catch (err) {
                                inputEl.focus();
                                inputEl.select();
                                inputEl.setSelectionRange(0, text.length);

                                const ok = document.execCommand("copy");
                                if (ok) flashGreen();
                            }

                            window.getSelection?.().removeAllRanges?.();
                        });
                    })();
                </script>
            </form>
        </section>
    </main>

    <?php include(__DIR__ . "/Includes/footer.php"); ?>
</body>

</html>
