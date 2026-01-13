<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include("Includes/header.php"); ?>
    <div class="admin-intro">
        <h1>Admin Paneel</h1>
        <p>Welkom,
            <?php echo htmlspecialchars($_SESSION['name']); ?>!
        </p>
    </div>
    <section class="admin-section">
        <article class="admin-panellist">
            <h2>Recent gemaakte gebruikers</h2>
            <div class="admin-panellist-content"></div>
            <a class="admin-btn" href="registratie-form.php">Registreer een gebruiker!</a>
        </article>
        <article class="admin-panellist">
            <h2>Recent gemaakte recepten</h2>
            <div class="admin-panellist-content"></div>
        </article>
    </section>
    <?php include("Includes/footer.php"); ?>
</body>

</html>