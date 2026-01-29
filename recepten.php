<?php
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
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <title>Recepten overzicht</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div id="recepten-wrapper">
        <main role="main">
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
                            <h3 id="ingredients-<?= $id ?>" class="recepten-subtitle">Ingrediënten</h3>
                            <p class="recepten-ingredients"><?= $ing ?: 'Geen ingrediënten bekend' ?></p>
                        </section>

                        <form method="GET" action="receptwijzigen.php" target="_blank">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <button type="submit" class="recepten-btn">Wijzigen</button>
                        </form>
                        <br>
                        <form method="GET" action="receptwijzigen.php" target="_blank">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <button type="submit" class="danger-btn">Delete</button>
                        </form>

                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>
</body>


</html>