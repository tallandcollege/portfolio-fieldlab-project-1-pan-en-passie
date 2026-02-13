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
    <?php include("includes/header.php"); ?>
    <main class="profile-main">
        <h1>Profielpagina</h1>
        <p>Welkom,
            <?php echo htmlspecialchars($_SESSION['name']); ?>!
        </p>
        <a href="logout.php">Logout</a>
    </main>
    <?php include("includes/footer.php"); ?>

</body>

</html>