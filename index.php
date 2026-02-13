<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pan en passie</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include(__DIR__ . "/Includes/header.php"); ?>
    <section class="title-section">
        <h1>Pan<br>en<br>Passie</h1>
        <a href="Add_recipe.php">Aan de slag</a>
    </section>
    <section class="info-section">
        <div>
            <h2>Welkom op Pan en Passie waar je alle recepten kan vinden gemaakt door onze chefs van Talland Horeca!</h2>
        </div>
        <div><img src="images/kokfoto1.jpg" alt="Pan en passie"></div>
    </section>
    <section class="selectie-section">
        <h2>Onze selectie van vandaag!</h2>
        <div class="selectie-container">
            <div class="voorgerechten-homepage">
                <h3>Voorgerechten</h3>
            </div>
            <div class="hoofdgerecht-homepage">
                <h3>Hoofdgerechten</h3>
            </div>
            <div class="nagerecht-homepage">
                <h3>Nagerechten</h3>
            </div>
        </div>
    </section>
    <?php include __DIR__ . "/Includes/footer.php"; ?>
</body>

</html>
