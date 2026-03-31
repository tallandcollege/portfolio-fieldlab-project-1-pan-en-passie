<?php
require_once("Includes/connection.php");

$receptid = $_GET['id'] ?? null;
if (!$receptid || !is_numeric($receptid)) {
    echo "Ongeldig recept ID.";
    exit;
}
$receptid = (int)$receptid;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $instructions = $_POST['instruction'] ?? [];
    if (!is_array($instructions)) {
        $instructions = [$instructions];
    }
    $instructions = array_filter(array_map('trim', $instructions));
    $instructionsText = implode("\n", $instructions);

    $stmt = $pdo->prepare("UPDATE recipe SET name = ?, description = ?, instructions = ? WHERE id = ?");
    $stmt->execute([
        $_POST['naam'],
        $_POST['beschrijving'],
        $instructionsText,
        $receptid
    ]);

    if (!empty($_POST['ingredienten'])) {
        foreach ($_POST['ingredienten'] as $ing) {
            $stmt = $pdo->prepare("
                UPDATE RecipeIngredient 
                SET Ingredient_id=?, Aantal=?, Eenheid=?
                WHERE Recipe_id=? AND Ingredient_id=?
            ");
            $stmt->execute([
                $ing['ingredient_id'],
                $ing['aantal'],
                $ing['eenheid'],
                $receptid,
                $ing['oude_ingredient_id']
            ]);
        }
    }

    if (!empty($_POST['materialen'])) {
        foreach ($_POST['materialen'] as $mat) {
            $stmt = $pdo->prepare("
                UPDATE recipematerial 
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

    if (!empty($_FILES['photo']['name']) && is_uploaded_file($_FILES['photo']['tmp_name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['photo']['type'];

        if (in_array($fileType, $allowedTypes, true)) {
            $targetDir = __DIR__ . '/uploads/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            $filename = uniqid('recipe_', true) . '_' . basename($_FILES['photo']['name']);
            $targetFile = $targetDir . $filename;

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
                $imageData = file_get_contents($targetFile);

                $stmt = $pdo->prepare("SELECT id, filename FROM photo WHERE recipe_id = ? ORDER BY uploaded_at DESC, id DESC LIMIT 1");
                $stmt->execute([$receptid]);
                $existingPhoto = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existingPhoto) {
                    $stmt = $pdo->prepare("UPDATE photo SET image = ?, mime_type = ?, filename = ?, uploaded_at = CURRENT_TIMESTAMP() WHERE id = ?");
                    $stmt->execute([$imageData, $fileType, $filename, $existingPhoto['id']]);

                    if (!empty($existingPhoto['filename']) && $existingPhoto['filename'] !== $filename && file_exists($targetDir . $existingPhoto['filename'])) {
                        unlink($targetDir . $existingPhoto['filename']);
                    }
                } else {
                    $stmt = $pdo->prepare("INSERT INTO photo (recipe_id, image, mime_type, filename) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$receptid, $imageData, $fileType, $filename]);
                }
            }
        }
    }

    // echo "<p class='ok'>Recept succesvol bijgewerkt!</p>";
    header("Location: docentpanel.php?status=ok");
}

$stmt = $pdo->prepare("SELECT * FROM recipe WHERE id=?");
$stmt->execute([$receptid]);
$recept = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT id, image, mime_type, filename FROM photo WHERE recipe_id = ? ORDER BY uploaded_at DESC, id DESC LIMIT 1");
$stmt->execute([$receptid]);
$photoRow = $stmt->fetch(PDO::FETCH_ASSOC);
$currentPhotoSrc = '';
if (!empty($photoRow['filename']) && file_exists(__DIR__ . '/uploads/' . $photoRow['filename'])) {
    $currentPhotoSrc = 'uploads/' . rawurlencode($photoRow['filename']);
} elseif (!empty($photoRow['image']) && !empty($photoRow['mime_type'])) {
    $currentPhotoSrc = 'data:' . htmlspecialchars($photoRow['mime_type'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ';base64,' . base64_encode($photoRow['image']);
}

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

$instructionLines = array_filter(array_map('trim', explode("\n", $recept['Instructions'] ?? '')));
if (empty($instructionLines)) {
    $instructionLines = [''];
}
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <title>Recept Wijzigen</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include("Includes/header.php"); ?>

    <div class="docent-intro">
        <h1>Recept Wijzigen: <?= htmlspecialchars($recept['Name'], ENT_QUOTES) ?></h1>
    </div>
    <div id="popup-message" role="status" aria-live="polite" aria-atomic="true"></div>
    <form method="POST" enctype="multipart/form-data">
        <main class="form-receptwijziging">
            <article class="recept-panellijst">
                <label for="naam">
                    <h2>Naam:</h2>
                </label>
                <input id="naam" type="text" name="naam" value="<?= htmlspecialchars($recept['Name'], ENT_QUOTES) ?>" required>

                <label for="beschrijving">
                    <h2>Beschrijving:</h2>
                </label>
                <textarea id="beschrijving" name="beschrijving"><?= htmlspecialchars($recept['Description'], ENT_QUOTES) ?></textarea>

                <label for="instruction-list">Instructies:</label>
                <ul id="instruction-list" class="instruction-list">
                    <?php foreach ($instructionLines as $index => $line): ?>
                        <li class="instruction-item">
                            <span class="step-number"><?= $index + 1 ?>.</span>
                            <input id="instruction_<?= $index ?>" name="instruction[]" type="text" placeholder="Instructie <?= $index + 1 ?>" class="instruction-input" aria-label="Instructie <?= $index + 1 ?>" value="<?= htmlspecialchars($line, ENT_QUOTES) ?>">
                            <button type="button" onclick="removeItem(this); updateInstructionNumbers()" aria-label="Verwijder instructie <?= $index + 1 ?>">❌</button>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <button class="green-button" type="button" onclick="addInstruction()">Instructie toevoegen</button>
            </article>
            <article class="recept-panellijst">
                <h2>Ingrediënten</h2>
                <table class="Add_Recipe_ingredienten">
                    <thead>
                        <tr class="table-header">
                            <th>Ingrediënt</th>
                            <th>Aantal</th>
                            <th>Eenheid</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="ingredienten">
                        <?php foreach ($ingredienten as $i => $ing): ?>
                            <tr class="ingredient-item">
                                <input type="hidden"
                                    name="ingredienten[<?= $i ?>][oude_ingredient_id]"
                                    value="<?= $ing['Ingredient_id'] ?>">
                                <td>
                                    <select name="ingredienten[<?= $i ?>][ingredient_id]" aria-label="Ingrediënt">
                                        <?php foreach ($alleIngredienten as $ai): ?>
                                            <option value="<?= $ai['id'] ?>"
                                                <?= $ai['id'] == $ing['Ingredient_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($ai['name'], ENT_QUOTES) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="any"
                                        name="ingredienten[<?= $i ?>][aantal]"
                                        aria-label="Aantal ingrediënt"
                                        value="<?= htmlspecialchars($ing['Aantal'], ENT_QUOTES) ?>">
                                </td>
                                <td>
                                    <select name="ingredienten[<?= $i ?>][eenheid]" aria-label="Eenheid ingrediënt">
                                        <option value="st" <?= $ing['Eenheid'] === 'st' ? 'selected' : '' ?>>Stuks</option>
                                        <option value="tl" <?= $ing['Eenheid'] === 'tl' ? 'selected' : '' ?>>Tl</option>
                                        <option value="el" <?= $ing['Eenheid'] === 'el' ? 'selected' : '' ?>>El</option>
                                        <option value="bs" <?= $ing['Eenheid'] === 'bs' ? 'selected' : '' ?>>Bosje</option>
                                        <option value="g" <?= $ing['Eenheid'] === 'g' ? 'selected' : '' ?>>G</option>
                                        <option value="kg" <?= $ing['Eenheid'] === 'kg' ? 'selected' : '' ?>>KG</option>
                                        <option value="ml" <?= $ing['Eenheid'] === 'ml' ? 'selected' : '' ?>>ML</option>
                                        <option value="dl" <?= $ing['Eenheid'] === 'dl' ? 'selected' : '' ?>>DL</option>
                                        <option value="l" <?= $ing['Eenheid'] === 'l' ? 'selected' : '' ?>>L</option>
                                        <option value="fles" <?= $ing['Eenheid'] === 'fles' ? 'selected' : '' ?>>Fles</option>
                                    </select>
                                </td>
                                <td>
                                    <button type="button" onclick="removeItem(this)" aria-label="Verwijder ingrediënt">❌</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button class="green-button" type="button" onclick="addIngredient()">Ingrediënt toevoegen</button>
            </article>
            <article class="recept-panellijst ">
                <h2>Materialen</h2>
                <ul id="materialen-list" class="materialen">
                    <?php foreach ($materialen as $i => $mat): ?>
                        <li class="materiaal-item">
                            <input type="hidden" name="materialen[<?= $i ?>][oude_materiaal_id]" value="<?= $mat['material_id'] ?>">
                            <label for="materiaal_<?= $i ?>">Materiaal:</label>
                            <select id="materiaal_<?= $i ?>" name="materialen[<?= $i ?>][materiaal_id]" aria-label="Materiaal">
                                <?php foreach ($alleMaterialen as $am): ?>
                                    <option value="<?= $am['id'] ?>" <?= $am['id'] == $mat['material_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($am['name'], ENT_QUOTES) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" onclick="removeItem(this)">❌</button>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <button class="green-button" type="button" onclick="addMateriaal()">Materiaal toevoegen</button>
            </article>
            <article class="recept-panellijst">
                <h2>Notities</h2>
                <?php foreach ($notities as $i => $note): ?>
                    <div class="note">
                        <input type="hidden" name="notities[<?= $i ?>][id]" value="<?= $note['id'] ?>">
                        <label for="notitie_<?= $i ?>">Beschrijving:</label>
                        <textarea id="notitie_<?= $i ?>" name="notities[<?= $i ?>][beschrijving]"><?= htmlspecialchars($note['description'], ENT_QUOTES) ?></textarea>
                    </div>
                <?php endforeach; ?>
            </article>

            <article class="recept-panellijst ">
                <h2>Receptfoto</h2>
                <?php if (!empty($currentPhotoSrc)): ?>
                    <img class="img-recept-edit" src="<?= $currentPhotoSrc ?>" alt="Huidige receptfoto" style="max-width:150px; display:block; margin-bottom:10px;">
                <?php else: ?>
                    <p>Er is nog geen foto voor dit recept.</p>
                <?php endif; ?>

                <label for="photo">Nieuwe foto uploaden:</label>
                <input type="file" name="photo" id="photo" accept="image/*">
                <p class="help-text">Kies een nieuwe foto om deze receptfoto te vervangen.</p>
            </article>

            <div class="docent-intro">
                <button type="submit" class="recepten-btn">Klaar</button>
            </div>
        </main>
    </form>

    <?php include(__DIR__ . "/Includes/footer.php"); ?>
</body>

</html>
<script>
    function addIngredient() {
        const tbody = document.getElementById("ingredienten");
        const rowIndex = tbody.children.length;

        const tr = document.createElement("tr");
        tr.className = "ingredient-item";

        tr.innerHTML = `
            <input type="hidden" name="ingredienten[${rowIndex}][oude_ingredient_id]" value="">
            <td>
                <select name="ingredienten[${rowIndex}][ingredient_id]">
                    <?php foreach ($alleIngredienten as $ai): ?>
                        <option value="<?= $ai['id'] ?>"><?= htmlspecialchars($ai['name'], ENT_QUOTES) ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <input type="number" step="any" name="ingredienten[${rowIndex}][aantal]" placeholder="Aantal" aria-label="Aantal ingrediënt">
            </td>
            <td>
                <select name="ingredienten[${rowIndex}][eenheid]" aria-label="Eenheid ingrediënt">
                    <option value="st">Stuks</option>
                    <option value="tl">Tl</option>
                    <option value="el">El</option>
                    <option value="bs">Bosje</option>
                    <option value="g">G</option>
                    <option value="kg">KG</option>
                    <option value="ml">ML</option>
                    <option value="dl">DL</option>
                    <option value="l">L</option>
                    <option value="fles">Fles</option>
                </select>
            </td>
            <td>
                <button type="button" onclick="removeItem(this)" aria-label="Verwijder ingrediënt">❌</button>
            </td>
        `;

        tbody.appendChild(tr);
    }

    function addInstruction() {
        const instructionList = document.getElementById("instruction-list");
        const itemCount = instructionList.querySelectorAll(".instruction-item").length + 1;

        const li = document.createElement("li");
        li.className = "instruction-item";
        li.innerHTML = `
            <span class="step-number">${itemCount}.</span>
            <input name="instruction[]" type="text" placeholder="Instructie ${itemCount}" class="instruction-input" aria-label="Instructie ${itemCount}">
            <button type="button" onclick="removeItem(this); updateInstructionNumbers()" aria-label="Verwijder instructie ${itemCount}">❌</button>
        `;

        instructionList.appendChild(li);
    }

    function addMateriaal() {
        const materialList = document.getElementById("materialen-list");
        const itemIndex = materialList.children.length;

        const li = document.createElement("li");
        li.className = "materiaal-item";
        li.innerHTML = `
            <input type="hidden" name="materialen[${itemIndex}][oude_materiaal_id]" value="">
            <label for="materiaal_${itemIndex}">Materiaal:</label>
            <select id="materiaal_${itemIndex}" name="materialen[${itemIndex}][materiaal_id]" aria-label="Materiaal">
                <?php foreach ($alleMaterialen as $am): ?>
                    <option value="<?= $am['id'] ?>"><?= htmlspecialchars($am['name'], ENT_QUOTES) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" onclick="removeItem(this)" aria-label="Verwijder materiaal">❌</button>
        `;

        materialList.appendChild(li);
    }

    function removeItem(button) {
        const item = button.closest("li, tr");
        if (!item) return;

        const instructionList = document.getElementById("instruction-list");
        const isInstruction = instructionList && instructionList.contains(item);

        item.remove();

        if (isInstruction) {
            updateInstructionNumbers();
        }
    }

    function updateInstructionNumbers() {
        const items = document.querySelectorAll("#instruction-list .instruction-item");

        items.forEach((item, index) => {
            const newNumber = index + 1;
            const stepNumber = item.querySelector(".step-number");
            const input = item.querySelector('input[name="instruction[]"]');

            if (stepNumber) {
                stepNumber.textContent = newNumber + ".";
            }

            if (input) {
                input.placeholder = "Instructie " + newNumber;
            }
        });
    }

    function toggleAmount(checkbox) {
        const row = checkbox.closest("tr");
        if (!row) return;

        const amountInput = row.querySelector('input[type="number"]');
        const eenheidSelect = row.querySelector('select[name*="[eenheid]"]');
        if (!amountInput) return;

        if (checkbox.checked) {
            amountInput.value = "";
            amountInput.disabled = true;
            if (eenheidSelect) {
                eenheidSelect.value = "";
                eenheidSelect.disabled = true;
            }
        } else {
            amountInput.disabled = false;
            if (eenheidSelect) {
                eenheidSelect.disabled = false;
                eenheidSelect.value = "st";
            }
        }
    }
</script>