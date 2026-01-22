<?php
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Ongeldig recept ID.";
    exit;
} else {
    $recipeid = (int)$_GET['id'];
}
$pdo = require_once('includes/connection.php');

$stmt = $pdo->prepare("SELECT id, name, description, instructions
    FROM Recipe
    WHERE id = ?");
$stmt->execute([$recipeid]);
$recipe = $stmt->fetch(PDO::FETCH_ASSOC);

$steps = explode("\n", $recipe['instructions']);


$stmt = $pdo->prepare("
    SELECT 
        i.name, 
        ri.Aantal, 
        ri.Eenheid, 
        ri.ingredientrole
    FROM RecipeIngredient ri
    JOIN Ingredient i ON i.id = ri.Ingredient_id
    WHERE ri.Recipe_id = ?
");
$stmt->execute([$recipeid]);
$ingredient = $stmt->fetchAll(PDO::FETCH_ASSOC);


$stmt = $pdo->prepare("SELECT m.name 
FROM RecipeMaterial rm 
JOIN material m
 ON m.id = rm.material_id 
WHERE rm.recipe_id = ? ");
$stmt->execute([$recipeid]);
$material = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT description
    FROM Aanvulling
    WHERE recipe_id = ?
");
$stmt->execute([$recipeid]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title><?= htmlspecialchars($recipe['name']) ?></title>
</head>

<body>
    <?php include("includes/header.php"); ?>
    <div>
        <div class="recipe-header">
            <h1><?= htmlspecialchars($recipe['name']) ?></h1>
            <?php if (!empty($notes)): ?>
                <div class="recipe-notes">
                    <?php foreach ($notes as $note): ?>
                        <p><?= htmlspecialchars($note['description']) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <br>
        <div class="recipe-body">
            <div class="left-column">
                <h2>Ingrediënten</h2>
                <ul class="ingredients row">
                    <?php foreach ($ingredient as $ing): ?>


                        <div class="aantal recept-collumn border-right"> <?= floatval(htmlspecialchars($ing['Aantal'])) ?></div>

                        <div class="eenheid recept-collumn border-right"> <?= htmlspecialchars($ing['Eenheid']) ?></div>

                        <div class="name recept-collumn"><?= htmlspecialchars($ing['name']) ?></div>


                    <?php endforeach; ?>
                </ul>

                <h2>Materialen</h2>
                <ul class="materials">
                    <?php foreach ($material as $mat): ?>
                        <li><?= htmlspecialchars($mat['name']) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <br>
            <div class="right-column">
                <h2>Werkwijze</h2>
                <ol class="steps">
                    <?php foreach ($steps as $step): ?>
                        <li><?= htmlspecialchars($step) ?></li>
                    <?php endforeach; ?>
                </ol>
            </div>

        </div>
    </div>
    <div class="recipe-notes">
        <?php foreach ($notes as $note): ?>
            <p><?= htmlspecialchars($note['description']) ?></p>
        <?php endforeach; ?>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>

</html>