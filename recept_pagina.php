<?php
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Ongeldig recept ID.";
    exit;
} else {
    $recipeid = (int)$_GET['id'];
}
include_once(__DIR__ . "/Includes/connection.php");

$stmt = $pdo->prepare("
    SELECT r.id, r.name, r.description, r.instructions, r.Sterren, u.username
    FROM recipe r
    LEFT JOIN users u ON r.User_id = u.id
    WHERE r.id = ?
");
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


$photoStmt = $pdo->prepare("
    SELECT id, image, mime_type FROM photo
    WHERE recipe_id = :recipe_id
    ORDER BY uploaded_at DESC
    LIMIT 1
");
$photoStmt->execute([':recipe_id' => $recipeid]);
$photo = $photoStmt->fetch(PDO::FETCH_ASSOC);

$recipeImage = null;
if ($photo && $photo['image']) {
    $imageData = base64_encode($photo['image']);
    $recipeImage = 'data:' . ($photo['mime_type'] ?? 'image/jpeg') . ';base64,' . $imageData;
}



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
    <?php include(__DIR__ . "/Includes/header.php"); ?>
    <div>
        <div class="recipe-header">
            <h1><?= htmlspecialchars($recipe['name']) ?></h1>
            <?php if ($recipeImage): ?>
                <img style="    height: 100px;" src="<?= htmlspecialchars($recipeImage) ?>" alt="<?= htmlspecialchars($recipe['name']) ?>">
            <?php endif; ?>
            <?php if (!empty($notes)): ?>
                <div class="recipe-notes">
                    <?php foreach ($notes as $note): ?>
                        <p><?= htmlspecialchars($note['description']) ?></p>
                        <p>Gemaakt door: <?= htmlspecialchars($recipe['username'] ?? 'Onbekend') ?></p>
                        <br>
                        <h6><?= htmlspecialchars($recipe['Sterren']) ?></h6>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <br>
        <div class="recipe-body">
            <div class="left-column">
                <h2>Ingredienten</h2>
                <ul class="ingredients">
                    <?php foreach ($ingredient as $ing): ?>
                        <li class="list" style="display: flex; justify-content: space-between; gap: 15px;">
                            <div class="aantal" style="flex: 0 0 60px; text-align: right;"><?= htmlspecialchars($ing['Aantal']) ?></div>
                            <div class="eenheid" style="flex: 0 0 80px;"><?= htmlspecialchars($ing['Eenheid']) ?></div>
                            <div class="name" style="flex: 1;"><?= htmlspecialchars($ing['name']) ?></div>
                        </li>
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
    <?php include __DIR__ . "/Includes/footer.php"; ?>
</body>

</html>