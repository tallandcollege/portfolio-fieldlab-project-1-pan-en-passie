<?php session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'docent') {
    header("Location: index.php");
    exit;
}

$pdo = require_once('includes/connection.php');

// Haal alle recepten op
$stmt = $pdo->query("
    SELECT r.id, r.name AS naam, GROUP_CONCAT(i.Name SEPARATOR ', ') AS ingredienten, '' AS foto
    FROM recipe r
    LEFT JOIN recipeingredient ri ON r.id = ri.`recipe_id`
    LEFT JOIN ingredient i ON ri.`ingredient_id` = i.id
    GROUP BY r.id
    ORDER BY r.name ASC
");
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$fotopad = 'fotos/';

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
        <?php if (isset($_GET['status']) && $_GET['status'] === 'ok'): ?>
                <p class="ok">Recept succesvol bijgewerkt!</p>
            <?php endif; ?>
    </div>
    <section class="docent-section">
        <article class="docent-panellist">
            <h2>recepten</h2>
            <div id="recepten-wrapper">
                <?php if (empty($recipes)): ?>
                    <p class="recepten-no-results">Geen recepten gevonden.</p>
                <?php else: ?>
                    <?php foreach ($recipes as $recipe):
                        $id = $recipe['id'];
                        $naam = htmlspecialchars($recipe['naam'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        $ing = htmlspecialchars($recipe['ingredienten'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        $foto = $recipe['foto'] ? htmlspecialchars($fotopad . $recipe['foto'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : 'fotos/default.png';
                    ?>
                        <article class="recepten-card" aria-labelledby="recipe-<?= $id ?>-title">
                            <h2 id="recipe-<?= $id ?>-title" class="recepten-title"><?= $naam ?></h2>
                            <img src="<?= $foto ?>" alt="Foto van <?= $naam ?>" class="recepten-img">
                            <section aria-labelledby="ingredients-<?= $id ?>" class="recepten-section">
                                <span>
                                    <h3 id="ingredients-<?= $id ?>" class="recepten-subtitle">Ingrediënten</h3>
                                    <p class="recepten-ingredients"><?= $ing ?: 'Geen ingrediënten bekend' ?></p>
                                </span>
                                <span>
                                <form method="GET" action="receptwijzigen.php" target="_blank">
                                    <input type="hidden" name="id" value="<?= $id ?>">
                                    <button type="submit" class="recepten-btn">Wijzigen</button>
                                </form>
                                <form method="GET" action="receptwijzigen.php" target="_blank">
                                    <input type="hidden" name="id" value="<?= $id ?>">
                                    <button type="submit" class="danger-btn">Delete</button>
                                </form>
                            </span>
                            </section>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
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