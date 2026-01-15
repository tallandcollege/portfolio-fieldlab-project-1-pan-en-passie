<?php session_start();

if (isset($_GET['key'])) {
    $key = $_GET['key'];
    if (!isset($_SESSION['registration_key'])) {
        $_SESSION['registration_key'] = hash('sha256', $key);
    }
} elseif (!isset($_SESSION['registration_key'])) {
    header("Location: loginform.php");
    exit;
}

$pdo = include 'includes/connection.php';

$stmt = $pdo->prepare("SELECT * FROM nan_account WHERE UserKey = :UserKey");
$stmt->execute([':UserKey' => $_SESSION['registration_key']]);
$NaNAccount = $stmt->fetch(PDO::FETCH_ASSOC);

function addErrorCode(array &$errors, string $field, string $code): void
{
    if (!isset($errors[$field]) || !in_array($code, $errors[$field], true)) {
        $errors[$field][] = $code;
    }
}

function errorMessage(string $code): string
{
    return match ($code) {
        'required'          => 'Dit veld is verplicht.',
        'invalid'       => 'Wachtwoord moet minimaal 8 tekens bevatten, inclusief een getal en een speciaal teken.',
        'not_equal'         => 'Wachtwoorden komen niet overeen.',
        'db_error'          => 'Database fout.',
        default             => 'Er ging iets mis.',
    };
}
function isValidPassword(string $password): bool
{
    $hasMinLen  = strlen($password) >= 8;
    $hasNumber  = preg_match('/\d/', $password) === 1;
    $hasSpecial = preg_match('/[^\w]/', $password) === 1; // not letter/number/underscore

    return $hasMinLen && $hasNumber && $hasSpecial;
}

$errors = $_SESSION['form_errors'] ?? [];
$old    = $_SESSION['form_old'] ?? [
    'password' => '',
    'confirm_password'  => '',

];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $errors = [];

    foreach (['password', 'confirm_password'] as $field) {
        if (trim((string)($_POST[$field] ?? '')) === '') {
            addErrorCode($errors, $field, 'required');
        }
    }

    if (!isset($errors['password']) && !isValidPassword($_POST['password'] ?? '')) {
        addErrorCode($errors, 'password', 'invalid');
    }

    if (!isset($errors['confirm_password']) && ($_POST['password'] ?? '') !== ($_POST['confirm_password'] ?? '')) {
        addErrorCode($errors, 'confirm_password', 'not_equal');
    }

    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_old']    = $old;
        header("Location: activeerAccount.php");
        exit;
    }
    for ($attempt = 0; $attempt < 5; $attempt++) {

        $firstname = $NaNAccount['firstname']  ?? '';
        $lastname  = $NaNAccount['lastname'] ?? '';
        $username  = $NaNAccount['username'] ?? '';
        $email     = $NaNAccount['email'] ?? '';
        $password  = $_POST['password'] ?? '';
        $role_id   = $NaNAccount['role_id'] ?? 0;

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users 
            (firstname, lastname, username, email, passwordhash, role_id)
            VALUES (:firstname, :lastname, :username, :email, :passwordhash, :role_id)
        ");
        try {
            $stmt->execute([
                ':firstname' => $firstname,
                ':lastname' => $lastname,
                ':username' => $username,
                ':email' => $email,
                ':passwordhash' => $hash,
                ':role_id' => $role_id
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $msg = $e->getMessage();

                addErrorCode($errors, 'general', 'db_error');
                $token = null;
                break;
            }

            addErrorCode($errors, 'general', 'db_error');
            $token = null;
            break;
        }
    }
    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_old']    = $old;
        $stmt = $pdo->prepare("
            SELECT u.id, u.firstname, r.name AS role
            FROM users u
            JOIN role r ON u.role_id = r.id
            WHERE u.username = :username
            LIMIT 1
        ");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['name']     = $user['firstname'];
            $_SESSION['role']     = $user['role'];

            $stmt = $pdo->prepare("DELETE FROM nan_account WHERE UserKey = :UserKey");
            $stmt->execute([':UserKey' => $_SESSION['registration_key']]);
            unset($_SESSION['registration_key']);

            header("Location: index.php");
            exit;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pan en passie</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include("includes/header.php"); ?>
    <main class="registratie-main">
        <section class="registratie-section" aria-labelledby="registratie-title">
            <h1>Hallo, <?php echo htmlspecialchars($NaNAccount['username']); ?></h1>
            <h2>Activeer uw account door een wachtwoord te maken!</h2>

            <form method="POST" class="registratie-form" novalidate>
                <div class="form-group">
                    <label for="password">Wachtwoord:</label>

                    <input type="password" id="password" name="password" required>

                    <?php
                    if (isset($errors['password'])) {
                        foreach ($errors['password'] as $code) {
                            echo '<div class="error-message" role="alert">'
                                . htmlspecialchars(errorMessage($code)) .
                                '</div>';
                        }
                    }
                    ?>

                    <div class="meter" aria-hidden="true">
                        <div class="bar" id="bar"></div>
                    </div>

                    <ul id="rules">
                        <li id="rLen" class="bad">Minimaal 8 tekens</li>
                        <li id="rNum" class="bad">Bevat een getal</li>
                        <li id="rSpec" class="bad">Bevat een speciaal teken</li>
                    </ul>

                    <p id="status" class="bad">Nog niet sterk genoeg.</p>

                    <script>
                        const passwordInput = document.getElementById('password'); // checkt name="password"
                        const rLen = document.getElementById('rLen');
                        const rNum = document.getElementById('rNum');
                        const rSpec = document.getElementById('rSpec');
                        const status = document.getElementById('status');
                        const bar = document.getElementById('bar');

                        // "speciaal teken": alles wat geen letter/cijfer/underscore is
                        const hasNumber = (v) => /\d/.test(v);
                        const hasSpecial = (v) => /[^\w]/.test(v);
                        const hasMinLen = (v) => v.length >= 8;

                        function setRule(el, ok) {
                            el.className = ok ? 'ok' : 'bad';
                        }

                        function update() {
                            const v = passwordInput.value;

                            const okLen = hasMinLen(v);
                            const okNum = hasNumber(v);
                            const okSpec = hasSpecial(v);

                            setRule(rLen, okLen);
                            setRule(rNum, okNum);
                            setRule(rSpec, okSpec);

                            const score = [okLen, okNum, okSpec].filter(Boolean).length; // 0..3
                            bar.style.width = (score / 3) * 100 + '%';
                            bar.style.background = score === 3 ? '#0a7a2f' : (score === 2 ? '#c07a00' : '#b00020');

                            if (score === 3) {
                                status.textContent = 'Top, dit wachtwoord voldoet!';
                                status.className = 'ok';
                            } else {
                                status.textContent = 'Nog niet sterk genoeg.';
                                status.className = 'bad';
                            }
                        }

                        passwordInput.addEventListener('input', update);
                        update();
                    </script>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Bevestig Wachtwoord:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <?php
                if (isset($errors['confirm_password'])) {
                    foreach ($errors['confirm_password'] as $code) {
                        echo '<div class="error-message" role="alert">'
                            . htmlspecialchars(errorMessage($code)) .
                            '</div>';
                    }
                }
                ?>
                <button type="submit" class="registratie-submit">Account Activeren</button>
            </form>
        </section>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>

</html>