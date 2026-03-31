<?php
include_once("Includes/connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'docent') {
    header("Location: index.php");
    exit;
}

// Haal alle recepten en de nieuwste receptfoto op
$recipes = fetchData("
    SELECT r.id,
           r.name AS naam,
           GROUP_CONCAT(i.Name SEPARATOR ', ') AS ingredienten,
           p.filename AS foto_bestand,
           p.image AS foto_blob,
           p.mime_type
    FROM recipe r
    LEFT JOIN recipeingredient ri ON r.id = ri.recipe_id
    LEFT JOIN ingredient i ON ri.ingredient_id = i.id
    LEFT JOIN photo p ON p.id = (
        SELECT p2.id
        FROM photo p2
        WHERE p2.recipe_id = r.id
        ORDER BY p2.uploaded_at DESC, p2.id DESC
        LIMIT 1
    )
    GROUP BY r.id, p.filename, p.image, p.mime_type
    ORDER BY r.name ASC
");


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
        <h1>Docenten Paneel</h1>
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
                    ?>
                        <?php
                        $recipeImage = 'images/placeholder.png';
                        if (!empty($recipe['foto_bestand'])) {
                            $recipeImage = 'uploads/' . rawurlencode($recipe['foto_bestand']);
                        } elseif (!empty($recipe['foto_blob']) && !empty($recipe['mime_type'])) {
                            $recipeImage = 'data:' . htmlspecialchars($recipe['mime_type'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ';base64,' . base64_encode($recipe['foto_blob']);
                        }
                        ?>
                        <article class="recepten-card" aria-labelledby="recipe-<?= $id ?>-title">
                            <h2 id="recipe-<?= $id ?>-title" class="recepten-title"><?= $naam ?></h2>
                            <div>
                                <img class="img-recept" src="<?= htmlspecialchars($recipeImage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" alt="<?= $naam ?>">
                            </div>
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

                                    <!--<form method="GET" action="receptwijzigen.php" target="_blank">
                                    <input type="hidden" name="id" value="<?= $id ?>">
                                    <button type="submit" class="danger-btn">Delete</button>
                                </form> -->
                                </span>
                            </section>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>
        <?php include("Includes/AccountZoekWidget.php");
        include("Includes/KlassenWidget.php"); ?>
    </section>
    <?php include("Includes/footer.php"); ?>
</body>


</html>