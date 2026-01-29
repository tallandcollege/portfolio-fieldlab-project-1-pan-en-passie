<?php
$pdo = require_once('includes/connection.php');

$receptid = $_GET['id'] ?? null;
if (!$receptid || !is_numeric($receptid)) {
    echo "Ongeldig recept ID.";
    exit;
}
$receptid = (int)$receptid;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE Recipe SET name = ?, description = ?, instructions = ? WHERE id = ?");
    $stmt->execute([
        $_POST['naam'],
        $_POST['beschrijving'],
        $_POST['instructies'],
        $receptid
    ]);

    if (!empty($_POST['ingredienten'])) {
        foreach ($_POST['ingredienten'] as $ing) {
            $stmt = $pdo->prepare("
                UPDATE RecipeIngredient 
                SET Ingredient_id=?, Aantal=?, Eenheid=?, ingredientrole=? 
                WHERE Recipe_id=? AND Ingredient_id=?
            ");
            $stmt->execute([
                $ing['ingredient_id'],
                $ing['aantal'],
                $ing['eenheid'],
                $ing['rol'],
                $receptid,
                $ing['oude_ingredient_id']
            ]);
        }
    }

    if (!empty($_POST['materialen'])) {
        foreach ($_POST['materialen'] as $mat) {
            $stmt = $pdo->prepare("
                UPDATE RecipeMaterial 
                SET material_id=? 
                WHERE recipe_id=? AND material_id=?
            ");
            $stmt->execute([
                $mat['materiaal_id'],
                $receptid,
                $mat['oude_materiaal_id']
            ]);
        }
    }

    if (!empty($_POST['notities'])) {
        foreach ($_POST['notities'] as $note) {
            $stmt = $pdo->prepare("UPDATE Aanvulling SET description=? WHERE id=? AND recipe_id=?");
            $stmt->execute([
                $note['beschrijving'],
                $note['id'],
                $receptid
            ]);
        }
    }

    echo "<p class='ok'>Recept succesvol bijgewerkt!</p>";
}

$stmt = $pdo->prepare("SELECT * FROM Recipe WHERE id=?");
$stmt->execute([$receptid]);
$recept = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT ri.Ingredient_id, i.name, ri.Aantal, ri.Eenheid, ri.ingredientrole
    FROM RecipeIngredient ri
    JOIN Ingredient i ON i.id = ri.Ingredient_id
    WHERE ri.Recipe_id=?
");
$stmt->execute([$receptid]);
$ingredienten = $stmt->fetchAll(PDO::FETCH_ASSOC);

$alleIngredienten = $pdo->query("SELECT id, name FROM Ingredient ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT rm.material_id, m.name
    FROM RecipeMaterial rm
    JOIN Material m ON m.id=rm.material_id
    WHERE rm.recipe_id=?
");
$stmt->execute([$receptid]);
$materialen = $stmt->fetchAll(PDO::FETCH_ASSOC);

$alleMaterialen = $pdo->query("SELECT id, name FROM Material ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT id, description FROM Aanvulling WHERE recipe_id=?");
$stmt->execute([$receptid]);
$notities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <title>Recept Wijzigen</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include("Includes/header.php"); ?>
    <main role="main">
        <div class="docent-intro">
            <h1>Recept Wijzigen: <?= htmlspecialchars($recept['Name'], ENT_QUOTES) ?></h1>
        </div>
        <form method="POST">
            <section class="recept-section">
                <article class="recept-panellijst ">
                    <label>Naam:</label>
                    <input type="text" name="naam" value="<?= htmlspecialchars($recept['Name'], ENT_QUOTES) ?>" required>

                    <label>Beschrijving:</label>
                    <textarea name="beschrijving"><?= htmlspecialchars($recept['Description'], ENT_QUOTES) ?></textarea>

                    <label>Instructies:</label>
                    <textarea name="instructies"><?= htmlspecialchars($recept['Instructions'], ENT_QUOTES) ?></textarea>
                </article>
                <article class="recept-panellijst ">
                    <h2>Ingrediënten</h2>
                    <?php foreach ($ingredienten as $i => $ing): ?>
                        <div class="recpeten-ingredient-lijst">
                            <input type="hidden" name="ingredienten[<?= $i ?>][oude_ingredient_id]" value="<?= $ing['Ingredient_id'] ?>">

                            <label>Ingrediënt:</label>
                            <select name="ingredienten[<?= $i ?>][ingredient_id]">
                                <?php foreach ($alleIngredienten as $ai): ?>
                                    <option value="<?= $ai['id'] ?>" <?= $ai['id'] == $ing['Ingredient_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($ai['name'], ENT_QUOTES) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <label>Aantal:</label>
                            <input type="text" name="ingredienten[<?= $i ?>][aantal]" value="<?= htmlspecialchars($ing['Aantal'], ENT_QUOTES) ?>">

                            <label>Eenheid:</label>
                            <input type="text" name="ingredienten[<?= $i ?>][eenheid]" value="<?= htmlspecialchars($ing['Eenheid'], ENT_QUOTES) ?>">

                            <label>Rol:</label>
                            <input type="text" name="ingredienten[<?= $i ?>][rol]" value="<?= htmlspecialchars($ing['ingredientrole'], ENT_QUOTES) ?>">
                        </div>
                    <?php endforeach; ?>


                    <h2>Materialen</h2>
                    <?php foreach ($materialen as $i => $mat): ?>
                        <div class="material">
                            <input type="hidden" name="materialen[<?= $i ?>][oude_materiaal_id]" value="<?= $mat['material_id'] ?>">
                            <label>Materiaal:</label>
                            <select name="materialen[<?= $i ?>][materiaal_id]">
                                <?php foreach ($alleMaterialen as $am): ?>
                                    <option value="<?= $am['id'] ?>" <?= $am['id'] == $mat['material_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($am['name'], ENT_QUOTES) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endforeach; ?>

                    <h2>Notities</h2>
                    <?php foreach ($notities as $i => $note): ?>
                        <div class="note">
                            <input type="hidden" name="notities[<?= $i ?>][id]" value="<?= $note['id'] ?>">
                            <label>Beschrijving:</label>
                            <textarea name="notities[<?= $i ?>][beschrijving]"><?= htmlspecialchars($note['description'], ENT_QUOTES) ?></textarea>
                        </div>
                    <?php endforeach; ?>
                </article>


            </section>
            <div class="docent-intro">
                <button type="submit" class="registratie-submit">Klaar</button>
            </div>
        </form>
    </main>
    <?php include("Includes/footer.php"); ?>
</body>

</html>