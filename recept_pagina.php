<?php
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Ongeldig recept ID.";
    exit;
} else {
    $recipeid = (int)$_GET['id'];
}
include_once(__DIR__ . "/Includes/connection.php");

$recipe = fetchData("
    SELECT r.id, r.name, r.description, r.instructions, r.Sterren, u.username
    FROM recipe r
    LEFT JOIN users u ON r.User_id = u.id
    WHERE r.id = :recipe_id
", [':recipe_id' => $recipeid], true);

if (empty($recipe)) {
    echo "Recept niet gevonden.";
    exit;
}

$steps = explode("\n", $recipe['instructions']);


$ingredient = fetchData("
    SELECT 
        i.name, 
        ri.Aantal, 
        ri.Eenheid, 
        ri.ingredientrole
    FROM RecipeIngredient ri
    JOIN Ingredient i ON i.id = ri.Ingredient_id
    WHERE ri.Recipe_id = :recipe_id
", [':recipe_id' => $recipeid]);


$photo = fetchData("
    SELECT id, image, mime_type FROM photo
    WHERE recipe_id = :recipe_id
    ORDER BY uploaded_at DESC
    LIMIT 1
", [':recipe_id' => $recipeid], true);

$recipeImage = null;
if ($photo && $photo['image']) {
    $imageData = base64_encode($photo['image']);
    $recipeImage = 'data:' . ($photo['mime_type'] ?? 'image/jpeg') . ';base64,' . $imageData;
}

$material = fetchData("
    SELECT m.name 
    FROM RecipeMaterial rm 
    JOIN material m ON m.id = rm.material_id 
    WHERE rm.recipe_id = :recipe_id
", [':recipe_id' => $recipeid]);

$notes = fetchData("
    SELECT description
    FROM Aanvulling
    WHERE recipe_id = :recipe_id
", [':recipe_id' => $recipeid]);

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
        <br>
        <div class="recipe-body">
            <div class="left-column">

                <?php if ($recipeImage): ?>
                    <div class="img-container">
                        <img src="<?= htmlspecialchars($recipeImage) ?>" alt="<?= htmlspecialchars($recipe['name']) ?>">
                    </div>
                <?php endif; ?>
                <h1><?= htmlspecialchars($recipe['name']) ?></h1>

                <?php if ($recipe['Sterren']): ?>
                    <h3><?= htmlspecialchars($recipe['Sterren']) ?></h3>
                    <p>Gemaakt door: <?= htmlspecialchars($recipe['username'] ?? 'Onbekend') ?></p>

                <?php endif; ?>
                <?php if (!empty($notes)): ?>
                    <div class="recipe-notes">
                        <?php foreach ($notes as $note): ?>
                            <p><?= htmlspecialchars($note['description']) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="right-column">
                <span>
                    <p class="recipe-desc">
                        <?php if (!empty($recipe['description'])): ?>
                            <?= nl2br(htmlspecialchars($recipe['description'])) ?>
                        <?php endif; ?>
                    </p>
                </span>
                <span>
                    <table>
                        <h2>Ingredienten</h2>

                        <?php foreach ($ingredient as $ing): ?>
                            <tr>
                                <?php 
                                if (empty($ing['Aantal'])) {
                                    $ingredientAantal = 'Naar smaak';
                                    $ingredientEenheid = '*';
                                } else {
                                $ingredientAantal = $ing['Aantal'] ?? '';
                                $ingredientEenheid = $ing['Eenheid'] ?? '';
                                }
                                $ingredientNaam = $ing['name'] ?? '';
                                ?>
                                    

                                <td><?= htmlspecialchars($ingredientAantal) ?></td>
                                <td><?= htmlspecialchars($ingredientEenheid) ?></td>
                                <td><?= htmlspecialchars($ingredientNaam) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tr>
                    </table>
                </span>
                <span>
                    <h2>Materialen</h2>
                    <ul class="materials">
                        <?php foreach ($material as $mat): ?>
                            <li><?= htmlspecialchars($mat['name']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </span>
                <span>
                    <h2>Werkwijze</h2>
                    <ol class="steps">
                        <?php foreach ($steps as $step): ?>
                            <li><?= htmlspecialchars($step) ?></li>
                        <?php endforeach; ?>
                    </ol>
                </span>
                <?php if (!empty($notes)): ?>
                    <div class="recipe-notes-responsive-copy">
                        <?php foreach ($notes as $note): ?>
                            <p><?= htmlspecialchars($note['description']) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
    <?php include("Includes/footer.php"); ?>
</body>

</html>
