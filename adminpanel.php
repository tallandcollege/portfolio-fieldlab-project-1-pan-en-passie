<?php
 include_once("Includes/connection.php");
    ob_start();
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: index.php");
        exit;
    }

    if (isset($_GET['klasid'])) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addStudent'])) {

        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Pan en Passie</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include("Includes/header.php"); ?>
    <div class="admin-intro">
        <h1>Admin Paneel</h1>
        <p>Welkom,
            <?php echo htmlspecialchars($_SESSION['name']); ?>!
        </p>
        <?php if (!empty($_SESSION['students_not_added'])): ?>
            <div class="admin-notice">
                <p>Deze studenten konden niet worden toegevoegd omdat de klas vol is:</p>
                <?php foreach ($_SESSION['students_not_added'] as $student): ?>
                    <p>
                        <?= htmlspecialchars(($student['firstname'] ?? '') . ' ' . ($student['lastname'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                        (<?= htmlspecialchars($student['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>,
                        <?= htmlspecialchars($student['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>)
                    </p>
                <?php endforeach; ?>
            </div>
            <?php unset($_SESSION['students_not_added']); ?>
        <?php endif; ?>
    </div>
    <section class="admin-section">
        <?php include("Includes/AccountZoekWidget.php");
        include("Includes/KlassenWidget.php"); ?>
    </section>
    <?php include("Includes/footer.php"); ?>
</body>

</html>
