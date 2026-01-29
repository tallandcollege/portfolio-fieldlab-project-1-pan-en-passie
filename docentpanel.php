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
    </section>
    <?php include("Includes/footer.php"); ?>
</body>


</html>
<script>
    document.getElementById('triggerRecept').addEventListener('click', function() {
        var iframe = document.querySelector('.docent-iframe');

        if (!iframe) return console.log('Iframe niet gevonden!');

        var iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
        if (!iframeDoc) return console.log('Kan niet bij iframe document komen!');

        // Pak de eerste recepten-btn (of kies specifiek op index)
        var button = iframeDoc.querySelector('.recepten-btn');
        if (!button) return console.log('Button niet gevonden in iframe!');

        // Vul eventueel hidden input
        var hiddenInput = iframeDoc.getElementById('comment_id_input');
        if (hiddenInput) hiddenInput.value = 123; // ID van het recept dat je wilt wijzigen

        console.log('Button gevonden, klik wordt uitgevoerd...');
        button.click(); // verstuurt het formulier
    });
</script>