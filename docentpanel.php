<?php session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'docent') {
    header("Location: index.php");
    exit;
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
    <div class="docent-intro">
        <h1>leraren panel</h1>
        <p>Welkom,
            <?php echo htmlspecialchars($_SESSION['name']); ?>!
        </p>
    </div>
    <section class="docent-section">
        <article class="docent-panellist">
            <h2>recepten</h2>

            <iframe src="recepten.php"
                class="docent-iframe"
                sandbox="allow-same-origin allow-scripts allow-popups-to-escape-sandbox allow-forms allow-popups"
                title="Overzicht van recepten"
                frameborder="0"
                scrolling="auto"></iframe>
        </article>
        <article class="docent-panellist">
            <h2>ingediende recepten</h2>
            <div class="docent-panellist-content"></div>
        </article>
        <article class="docent-panellist">
            <h2>Klass</h2>
            <div class="docent-panellist-content"></div>
        </article>




    </section>
    <?php include("Includes/footer.php"); ?>
</body>


</html>