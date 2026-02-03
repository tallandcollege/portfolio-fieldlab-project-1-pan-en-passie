<?php
if (!isset($_SESSION)) {
    session_start();
}

include("Includes/connection.php");

$errors = [];
$success = false;

// sticky values
$classname = '';
$description = '';
$maxstudents = '';
$createrecipeperms = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classname = trim($_POST['classname'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $maxstudents = trim($_POST['maxstudents'] ?? '');
    $createrecipeperms = isset($_POST['createrecipeperms']) ? 1 : 0;

    // Validatie
    if ($classname === '') {
        $errors[] = "Klasnaam is verplicht.";
    }

    if ($maxstudents === '' || !ctype_digit($maxstudents) || (int)$maxstudents < 1) {
        $errors[] = "Max. studenten moet een geheel getal zijn van minimaal 1.";
    }

    if (empty($errors)) {
        $query = "
            INSERT INTO Class (classname, description, maxstudents, createrecipeperms)
            VALUES (:classname, :description, :maxstudents, :createrecipeperms)
        ";

        // Als je executeQuery hebt toegevoegd:
        $rows = executeQuery($query, [
            ':classname' => $classname,
            ':description' => $description,
            ':maxstudents' => (int)$maxstudents,
            ':createrecipeperms' => $createrecipeperms
        ]);

        if ($rows > 0) {
            $success = true;

            // form leegmaken
            $classname = '';
            $description = '';
            $maxstudents = '';
            $createrecipeperms = 0;

            header('adminpanel.php');
        } else {
            $errors[] = "Opslaan mislukt, probeer het opnieuw.";
        }
    }
}
?>

<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <title>Klas aanmaken</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include("Includes/header.php") ?>
    <main>

<section class="registratie-section" aria-labelledby="registratie-title">
            <h1 id="registratie-title">Account registreren voor een gebruiker</h1>

<?php if ($success): ?>
    <p style="color: green;">Klas is succesvol aangemaakt!</p>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <ul style="color: red;">
        <?php foreach ($errors as $err): ?>
            <li><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="POST" novalidate>
    <div>
        <label for="classname">Naam van de klas</label>
        <input
            type="text"
            id="classname"
            name="classname"
            required
            value="<?php echo htmlspecialchars($classname, ENT_QUOTES, 'UTF-8'); ?>"
        />
    </div>

    <div>
        <label for="description">Omschrijving</label>
        <textarea
            id="description"
            name="description"
            rows="3"
            placeholder="Korte omschrijving (optioneel)"
        ><?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></textarea>
    </div>

    <div>
        <label for="maxstudents">Max. studenten</label>
        <input
            type="number"
            id="maxstudents"
            name="maxstudents"
            min="1"
            step="1"
            required
            placeholder="Bijv. 30"
            value="<?php echo htmlspecialchars($maxstudents, ENT_QUOTES, 'UTF-8'); ?>"
        />
    </div>

    <div>
        <label>
            <input
                type="checkbox"
                name="createrecipeperms"
                value="1"
                <?php echo ($createrecipeperms == 1) ? 'checked' : ''; ?>
            />
            Leerlingen mogen recepten aanmaken
        </label>
    </div>

    <button type="submit" class="registratie-submit">Klas aanmaken</button>
</form>
</section>
</main>
<?php include("Includes/footer.php") ?>
</body>
</html>
