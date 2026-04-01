<?php
// Include the database connection and helper functions
require_once("includes/connection.php");


/* =========================================================
   GET THE RECIPE ID FROM THE URL
   Example: edit_recipe.php?id=5
========================================================= */

// Try to read the recipe ID from the query string
$receptid = $_GET['id'] ?? null;

// Stop the script if the ID is missing or not numeric
if (!$receptid || !is_numeric($receptid)) {
    echo "Invalid recipe ID.";
    exit;
}

// Convert the recipe ID to an integer for safety
$receptid = (int)$receptid;


/* =========================================================
   HANDLE FORM SUBMISSION
========================================================= */

// Only run the update logic when the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        // Start a transaction so all updates succeed or fail together
        $pdo->beginTransaction();


        /* ---------------------------------------------------------
           PROCESS THE INSTRUCTIONS
        --------------------------------------------------------- */

        // Get all instruction fields from the form
        $instructions = $_POST['instruction'] ?? [];

        // Make sure instructions is always an array
        if (!is_array($instructions)) {
            $instructions = [$instructions];
        }

        // Trim whitespace and remove empty instruction lines
        $instructions = array_filter(array_map('trim', $instructions));

        // Turn the array into one string separated by new lines
        $instructionsText = implode("\n", $instructions);


        /* ---------------------------------------------------------
           UPDATE THE MAIN RECIPE DATA
        --------------------------------------------------------- */

        // Prepare the SQL query for updating the recipe itself
        $stmt = $pdo->prepare("
            UPDATE recipe 
            SET name = ?, description = ?, instructions = ?, Sterren = ?
            WHERE id = ?
        ");

        // Execute the update with the submitted form values
        $stmt->execute([
            $_POST['naam'],
            $_POST['beschrijving'],
            $instructionsText,
            $_POST['Sterren'],
            $receptid
        ]);


        /* ---------------------------------------------------------
   REBUILD ALL INGREDIENTS
--------------------------------------------------------- */

        // First remove all current ingredients for this recipe
        $stmt = $pdo->prepare("DELETE FROM RecipeIngredient WHERE Recipe_id = ?");
        $stmt->execute([$receptid]);

        // Only reinsert ingredients that are still present in the form
        if (!empty($_POST['ingredienten'])) {

            $stmt = $pdo->prepare("
        INSERT INTO RecipeIngredient (Recipe_id, Ingredient_id, Aantal, Eenheid, IngredientRole)
        VALUES (?, ?, ?, ?, ?)
    ");

            // Keep track of ingredient IDs already inserted
            $usedIngredientIds = [];

            foreach ($_POST['ingredienten'] as $ing) {

                // Skip empty ingredient rows
                if (empty($ing['ingredient_id'])) {
                    continue;
                }

                $ingredientId = (int)$ing['ingredient_id'];

                // Skip duplicate ingredient selections
                if (in_array($ingredientId, $usedIngredientIds, true)) {
                    continue;
                }

                $usedIngredientIds[] = $ingredientId;

                // Check whether this ingredient is marked as 'to taste'
                $naarSmaak = ($ing['naarsmaak'] ?? '') === 'on';

                // Insert the current ingredient state
                $stmt->execute([
                    $receptid,
                    $ingredientId,
                    $naarSmaak ? null : ($ing['aantal'] ?? null),
                    $naarSmaak ? '*' : ($ing['eenheid'] ?? null),
                    $naarSmaak ? 'naarsmaak' : 'standaard'
                ]);
            }
        }


        /* ---------------------------------------------------------
   REBUILD ALL MATERIALS
--------------------------------------------------------- */

        // Remove all existing materials for this recipe first
        $stmt = $pdo->prepare("DELETE FROM recipematerial WHERE recipe_id = ?");
        $stmt->execute([$receptid]);

        // Reinsert only the materials that are still present in the form
        if (!empty($_POST['materialen'])) {

            $stmt = $pdo->prepare("
        INSERT INTO recipematerial (recipe_id, material_id)
        VALUES (?, ?)
    ");

            // Keep track of material IDs already inserted
            $usedMaterialIds = [];

            // Loop through every submitted material row
            foreach ($_POST['materialen'] as $mat) {

                // Skip empty rows
                if (empty($mat['materiaal_id'])) {
                    continue;
                }

                $materialId = (int)$mat['materiaal_id'];

                // Skip duplicate material selections
                if (in_array($materialId, $usedMaterialIds, true)) {
                    continue;
                }

                $usedMaterialIds[] = $materialId;

                // Insert the material again
                $stmt->execute([
                    $receptid,
                    $materialId
                ]);
            }
        }

        /* ---------------------------------------------------------
           UPDATE EXISTING NOTES
        --------------------------------------------------------- */

        // Only continue if note data was submitted
        if (!empty($_POST['notities'])) {

            // Loop through every submitted note
            foreach ($_POST['notities'] as $note) {

                // Prepare the update query for the note
                $stmt = $pdo->prepare("
                    UPDATE Aanvulling 
                    SET description = ?
                    WHERE id = ? AND recipe_id = ?
                ");

                // Update the note text
                $stmt->execute([
                    $note['beschrijving'],
                    $note['id'],
                    $receptid
                ]);
            }
        }


        /* ---------------------------------------------------------
           HANDLE A NEW IMAGE UPLOAD
        --------------------------------------------------------- */

        // Only continue if a new file was uploaded successfully
        if (!empty($_FILES['photo']['name']) && is_uploaded_file($_FILES['photo']['tmp_name'])) {

            // List of allowed image MIME types
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

            // Detect the MIME type of the uploaded file
            $fileType = mime_content_type($_FILES['photo']['tmp_name']);

            // Reject the upload if the type is not allowed
            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception("Invalid image type.");
            }

            // Define the upload folder path
            $targetDir = __DIR__ . '/uploads/';

            // Create the uploads folder if it does not exist yet
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            // Generate a unique filename to avoid duplicates
            $filename = uniqid('recipe_', true) . '_' . basename($_FILES['photo']['name']);

            // Full server path of the uploaded file
            $targetFile = $targetDir . $filename;

            // Move the temporary uploaded file to the uploads folder
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {

                // Read the moved file so it can also be stored in the database
                $imageData = file_get_contents($targetFile);

                // Check whether this recipe already has a photo
                $stmt = $pdo->prepare("
                    SELECT id, filename 
                    FROM photo 
                    WHERE recipe_id = ? 
                    ORDER BY uploaded_at DESC 
                    LIMIT 1
                ");
                $stmt->execute([$receptid]);
                $existingPhoto = $stmt->fetch(PDO::FETCH_ASSOC);

                // If a photo already exists, update it
                if ($existingPhoto) {
                    $stmt = $pdo->prepare("
                        UPDATE photo 
                        SET image = ?, mime_type = ?, filename = ?, uploaded_at = CURRENT_TIMESTAMP()
                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $imageData,
                        $fileType,
                        $filename,
                        $existingPhoto['id']
                    ]);
                } else {
                    // Otherwise insert a completely new photo row
                    $stmt = $pdo->prepare("
                        INSERT INTO photo (recipe_id, image, mime_type, filename, uploaded_at)
                        VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP())
                    ");

                    $stmt->execute([
                        $receptid,
                        $imageData,
                        $fileType,
                        $filename
                    ]);
                }
            }
        }


        /* ---------------------------------------------------------
           FINISH THE TRANSACTION
        --------------------------------------------------------- */

        // Save all database changes
        $pdo->commit();

        // Show success feedback and redirect back to the recipe page
        echo "<script>
            window.addEventListener('load', function () {
                if (typeof showPopup === 'function') {
                    showPopup('Recept succesvol bijgewerkt!', 'success');
                } else {
                    alert('Recept succesvol bijgewerkt!');
                }

                setTimeout(function () {
                    window.location.href = 'recept_pagina.php?id={$receptid}';
                }, 2000);
            });
        </script>";
    } catch (Exception $e) {
        // Undo all changes if something failed
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        // Log the error for debugging
        error_log("Edit Recipe Error: " . $e->getMessage());

        // Show the error on screen
        echo "<script>
            window.addEventListener('load', function () {
                if (typeof showPopup === 'function') {
                    showPopup('Error: " . addslashes($e->getMessage()) . "', 'error');
                } else {
                    alert('Error: " . addslashes($e->getMessage()) . "');
                }
            });
        </script>";
    }
}


/* =========================================================
   LOAD THE RECIPE DATA
========================================================= */

// Get the main recipe row
$stmt = $pdo->prepare("SELECT * FROM recipe WHERE id = ?");
$stmt->execute([$receptid]);
$recept = $stmt->fetch(PDO::FETCH_ASSOC);

// Stop the page if the recipe was not found
if (!$recept) {
    echo "Recipe not found.";
    exit;
}


/* =========================================================
   LOAD THE CURRENT RECIPE PHOTO
========================================================= */

// Get the newest photo linked to this recipe
$stmt = $pdo->prepare("
    SELECT id, image, mime_type, filename
    FROM photo
    WHERE recipe_id = ?
    ORDER BY uploaded_at DESC, id DESC
    LIMIT 1
");
$stmt->execute([$receptid]);
$photoRow = $stmt->fetch(PDO::FETCH_ASSOC);

// Default image source is empty
$currentPhotoSrc = '';

// If there is a stored filename and the physical file exists, use that file
if (!empty($photoRow['filename']) && file_exists(__DIR__ . '/uploads/' . $photoRow['filename'])) {
    $currentPhotoSrc = 'uploads/' . rawurlencode($photoRow['filename']);
}
// Otherwise, if image data exists in the database, build a base64 image source
elseif (!empty($photoRow['image']) && !empty($photoRow['mime_type'])) {
    $currentPhotoSrc = 'data:' . htmlspecialchars($photoRow['mime_type'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ';base64,' . base64_encode($photoRow['image']);
}


/* =========================================================
   LOAD THE CURRENT INGREDIENTS FOR THIS RECIPE
========================================================= */

// Get all ingredients linked to this recipe
$stmt = $pdo->prepare("
    SELECT ri.Ingredient_id, i.name, ri.Aantal, ri.Eenheid, ri.IngredientRole
    FROM RecipeIngredient ri
    JOIN Ingredient i ON i.id = ri.Ingredient_id
    WHERE ri.Recipe_id = ?
");
$stmt->execute([$receptid]);
$ingredienten = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Load all ingredients for the dropdown menus
$alleIngredienten = $pdo->query("SELECT id, name FROM Ingredient ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);


/* =========================================================
   LOAD THE CURRENT MATERIALS FOR THIS RECIPE
========================================================= */

// Get all materials linked to this recipe
$stmt = $pdo->prepare("
    SELECT rm.material_id, m.name
    FROM RecipeMaterial rm
    JOIN Material m ON m.id = rm.material_id
    WHERE rm.recipe_id = ?
");
$stmt->execute([$receptid]);
$materialen = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Load all materials for the dropdown menus
$alleMaterialen = $pdo->query("SELECT id, name FROM Material ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);


/* =========================================================
   LOAD THE CURRENT NOTES FOR THIS RECIPE
========================================================= */

// Get all notes linked to this recipe
$stmt = $pdo->prepare("SELECT id, description FROM Aanvulling WHERE recipe_id = ?");
$stmt->execute([$receptid]);
$notities = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================================================
   SPLIT THE INSTRUCTIONS INTO SEPARATE INPUT FIELDS
========================================================= */

// Split the instructions string into separate lines
$instructionLines = array_filter(array_map('trim', explode("\n", $recept['Instructions'])));

// Ensure there is always at least one visible instruction field
if (empty($instructionLines)) {
    $instructionLines = [''];
}
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <!-- Character encoding -->
    <meta charset="UTF-8">

    <!-- Responsive scaling for mobile devices -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Page title -->
    <title>Edit Recipe</title>

    <!-- Main stylesheet -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- Include the shared site header -->
    <?php include("includes/header.php"); ?>

    <!-- Popup container used by JavaScript feedback messages -->
    <div id="popup-message" class="popup-message"></div>

    <!-- Main page wrapper using the same styling classes as the add page -->
    <main class="addRecipe-main">
        <section class="addRecipe-section">

            <!-- Form for editing the recipe -->
            <form method="POST" enctype="multipart/form-data">

                <!-- Recipe name -->
                <label class="form-label">Naam recept</label>
                <input
                    name="naam"
                    placeholder="Naam recept"
                    required
                    value="<?= htmlspecialchars($recept['Name'], ENT_QUOTES) ?>">

                <!-- Recipe difficulty -->
                <label class="form-label">Moeilijkheid</label>
                <select name="Sterren" class="sterren-dropdown" required>
                    <option value="Beginner ☆☆☆" <?= ($recept['Sterren'] === 'Beginner ☆☆☆') ? 'selected' : '' ?>>Beginner ☆☆☆</option>
                    <option value="Makkelijk ⭐☆☆" <?= ($recept['Sterren'] === 'Makkelijk ⭐☆☆') ? 'selected' : '' ?>>Makkelijk ⭐☆☆</option>
                    <option value="Gemiddeld ⭐⭐☆" <?= ($recept['Sterren'] === 'Gemiddeld ⭐⭐☆') ? 'selected' : '' ?>>Gemiddeld ⭐⭐☆</option>
                    <option value="Moeilijk ⭐⭐⭐" <?= ($recept['Sterren'] === 'Moeilijk ⭐⭐⭐') ? 'selected' : '' ?>>Moeilijk ⭐⭐⭐</option>
                </select>

                <!-- Recipe description -->
                <label class="form-label">Beschrijving</label>
                <textarea name="beschrijving"><?= htmlspecialchars($recept['Description'], ENT_QUOTES) ?></textarea>

                <!-- Materials section -->
                <label class="form-label">Materialen</label>
                <ul id="materialen-list">
                    <?php foreach ($materialen as $i => $mat): ?>
                        <li class="materiaal-item">

                            <!-- Hidden input to remember the original material ID -->
                            <input type="hidden" name="materialen[<?= $i ?>][oude_materiaal_id]" value="<?= $mat['material_id'] ?>">

                            <!-- Material dropdown -->
                            <select name="materialen[<?= $i ?>][materiaal_id]">
                                <?php foreach ($alleMaterialen as $am): ?>
                                    <option value="<?= $am['id'] ?>" <?= ($am['id'] == $mat['material_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($am['name'], ENT_QUOTES) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <!-- Remove material row button -->
                            <button type="button" onclick="removeItem(this)">❌</button>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <!-- Button to add another material row -->
                <button type="button" class="recipe-submit" onclick="addMateriaal()">Materiaal toevoegen</button>

                <!-- Ingredients section -->
                <label class="form-label">Ingrediënten</label>
                <table class="Add_Recipe_ingredienten">
                    <thead>
                        <tr class="table-header">
                            <th>Naar smaak?</th>
                            <th>Hoeveelheid</th>
                            <th>Eenheid</th>
                            <th>Ingrediënt</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="ingredienten">
                        <?php foreach ($ingredienten as $i => $ing): ?>
                            <?php $naarSmaak = ($ing['IngredientRole'] === 'naarsmaak'); ?>
                            <tr class="ingredient-item">
                                <!-- Checkbox for "to taste" -->
                                <td>
                                    <input
                                        type="checkbox"
                                        name="ingredienten[<?= $i ?>][naarsmaak]"
                                        onchange="toggleAmount(this)"
                                        <?= $naarSmaak ? 'checked' : '' ?>>
                                </td>

                                <!-- Amount input -->
                                <td>
                                    <input
                                        name="ingredienten[<?= $i ?>][aantal]"
                                        type="number"
                                        step="any"
                                        placeholder="Aantal"
                                        value="<?= $naarSmaak ? '' : htmlspecialchars($ing['Aantal'], ENT_QUOTES) ?>"
                                        <?= $naarSmaak ? 'disabled' : '' ?>>
                                </td>

                                <!-- Unit dropdown -->
                                <td>
                                    <select
                                        name="ingredienten[<?= $i ?>][eenheid]"
                                        <?= $naarSmaak ? 'disabled' : '' ?>>
                                        <option value="st" <?= ($ing['Eenheid'] === 'st') ? 'selected' : '' ?>>Stuks</option>
                                        <option value="tl" <?= ($ing['Eenheid'] === 'tl') ? 'selected' : '' ?>>Tl</option>
                                        <option value="el" <?= ($ing['Eenheid'] === 'el') ? 'selected' : '' ?>>El</option>
                                        <option value="bs" <?= ($ing['Eenheid'] === 'bs') ? 'selected' : '' ?>>Bosje</option>
                                        <option value="g" <?= ($ing['Eenheid'] === 'g') ? 'selected' : '' ?>>G</option>
                                        <option value="kg" <?= ($ing['Eenheid'] === 'kg') ? 'selected' : '' ?>>KG</option>
                                        <option value="ml" <?= ($ing['Eenheid'] === 'ml') ? 'selected' : '' ?>>ML</option>
                                        <option value="dl" <?= ($ing['Eenheid'] === 'dl') ? 'selected' : '' ?>>DL</option>
                                        <option value="l" <?= ($ing['Eenheid'] === 'l') ? 'selected' : '' ?>>L</option>
                                        <option value="fles" <?= ($ing['Eenheid'] === 'fles') ? 'selected' : '' ?>>Fles</option>
                                    </select>
                                </td>

                                <!-- Ingredient dropdown -->
                                <td>
                                    <select name="ingredienten[<?= $i ?>][ingredient_id]">
                                        <?php foreach ($alleIngredienten as $ai): ?>
                                            <option value="<?= $ai['id'] ?>" <?= ($ai['id'] == $ing['Ingredient_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($ai['name'], ENT_QUOTES) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <!-- Hidden field for ingredient role -->
                                    <input type="hidden" name="ingredienten[<?= $i ?>][role]" value="<?= htmlspecialchars($ing['IngredientRole'], ENT_QUOTES) ?>">
                                </td>

                                <!-- Remove ingredient row button -->
                                <td>
                                    <button type="button" onclick="removeItem(this)">❌</button>
                                </td>
                                <!-- Hidden input to remember the original ingredient ID -->
                                <input type="hidden" name="ingredienten[<?= $i ?>][oude_ingredient_id]" value="<?= $ing['Ingredient_id'] ?>">
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Button to add another ingredient row -->
                <button type="button" class="recipe-submit" onclick="addIngredient()">Ingrediënt toevoegen</button>

                <!-- Instructions section -->
                <label class="form-label">Instructies</label>
                <ul id="instruction-list">
                    <?php foreach ($instructionLines as $index => $line): ?>
                        <li class="instruction-item">

                            <!-- Visual step number -->
                            <span class="step-number"><?= $index + 1 ?>.</span>

                            <!-- Instruction text input -->
                            <input
                                name="instruction[]"
                                type="text"
                                placeholder="Instructie <?= $index + 1 ?>"
                                class="instruction-input"
                                value="<?= htmlspecialchars($line, ENT_QUOTES) ?>">

                            <!-- Remove instruction button -->
                            <button type="button" onclick="removeItem(this); updateInstructionNumbers()">❌</button>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <!-- Button to add another instruction -->
                <button type="button" class="recipe-submit" onclick="addInstruction()">Instructie toevoegen</button>

                <!-- Notes section -->
                <label class="form-label">Notities</label>
                <?php foreach ($notities as $i => $note): ?>
                    <div class="note">

                        <!-- Hidden input to remember the original note ID -->
                        <input type="hidden" name="notities[<?= $i ?>][id]" value="<?= $note['id'] ?>">

                        <!-- Note textarea -->
                        <textarea name="notities[<?= $i ?>][beschrijving]" class="aanvullingen"><?= htmlspecialchars($note['description'], ENT_QUOTES) ?></textarea>
                    </div>
                <?php endforeach; ?>

                <!-- Current image preview -->
                <label class="form-label">Huidige foto</label>
                <?php if (!empty($currentPhotoSrc)): ?>
                    <img
                        src="<?= $currentPhotoSrc ?>"
                        alt="Current recipe photo"
                        style="max-width:220px; display:block; margin-bottom:15px;">
                <?php else: ?>
                    <p class="no-photo-error">Dit recept heeft nog geen foto.</p>
                <?php endif; ?>

                <!-- File input for uploading a new image -->
                <label class="form-label">Nieuwe foto uploaden</label>
                <input type="file" name="photo" accept="image/*">

                <!-- Final submit button -->
                <button type="submit" class="recipe-submit">Opslaan</button>
            </form>
        </section>
    </main>

    <!-- Include the shared site footer -->
    <?php include("includes/footer.php"); ?>

    <script>
        // Show a temporary popup message on screen
        function showPopup(message, type) {
            const popup = document.getElementById("popup-message");

            // Set popup text
            popup.textContent = message;

            // Set popup class for styling
            popup.className = "popup-message " + type;

            // Make popup visible
            popup.style.display = "block";

            // Hide popup again after 3 seconds
            setTimeout(() => {
                popup.style.display = "none";
            }, 3000);
        }

        // Add a new ingredient row to the ingredients table
        function addIngredient() {
            const tbody = document.getElementById("ingredienten");

            // Count current ingredient rows to generate the next index
            const rowIndex = tbody.querySelectorAll(".ingredient-item").length;

            // Create a new table row
            const tr = document.createElement("tr");
            tr.className = "ingredient-item";

            // Build the HTML for the new ingredient row
            tr.innerHTML = `
                <td>
                    <input type="checkbox" name="ingredienten[${rowIndex}][naarsmaak]" onchange="toggleAmount(this)">
                </td>
                <td>
                    <input name="ingredienten[${rowIndex}][aantal]" type="number" step="any" placeholder="Aantal">
                </td>
                <td>
                    <select name="ingredienten[${rowIndex}][eenheid]">
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
                    <select name="ingredienten[${rowIndex}][ingredient_id]">
                        <?php foreach ($alleIngredienten as $ai): ?>
                            <option value="<?= $ai['id'] ?>"><?= htmlspecialchars($ai['name'], ENT_QUOTES) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="ingredienten[${rowIndex}][role]" value="standaard">
                </td>
                <td>
                    <button type="button" onclick="removeItem(this)">❌</button>
                </td>
                <input type="hidden" name="ingredienten[${rowIndex}][oude_ingredient_id]" value="">
            `;

            // Add the new row to the table body
            tbody.appendChild(tr);
        }

        // Add a new instruction row to the instruction list
        function addInstruction() {
            const instructionList = document.getElementById("instruction-list");

            // Count current instructions to determine the next step number
            const itemCount = instructionList.querySelectorAll(".instruction-item").length + 1;

            // Create a new list item
            const li = document.createElement("li");
            li.className = "instruction-item";

            // Build the HTML for the new instruction row
            li.innerHTML = `
                <span class="step-number">${itemCount}.</span>
                <input name="instruction[]" type="text" placeholder="Instructie ${itemCount}" class="instruction-input">
                <button type="button" onclick="removeItem(this); updateInstructionNumbers()">❌</button>
            `;

            // Add the new instruction row to the list
            instructionList.appendChild(li);
        }

        // Add a new material row to the materials list
        function addMateriaal() {
            const materialList = document.getElementById("materialen-list");

            // Count current materials to determine the next index
            const itemIndex = materialList.querySelectorAll(".materiaal-item").length;

            // Create a new list item
            const li = document.createElement("li");
            li.className = "materiaal-item";

            // Build the HTML for the new material row
            li.innerHTML = `
                <input type="hidden" name="materialen[${itemIndex}][oude_materiaal_id]" value="">
                <select name="materialen[${itemIndex}][materiaal_id]">
                    <?php foreach ($alleMaterialen as $am): ?>
                        <option value="<?= $am['id'] ?>"><?= htmlspecialchars($am['name'], ENT_QUOTES) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" onclick="removeItem(this)">❌</button>
            `;

            // Add the new material row to the list
            materialList.appendChild(li);
        }

        // Remove a row or item from the page
        function removeItem(button) {
            // Find the closest removable container
            const item = button.closest("li, tr, .note");

            // Stop if no valid parent was found
            if (!item) return;

            // Check if this removed item is an instruction row
            const instructionList = document.getElementById("instruction-list");
            const isInstructionItem = instructionList && instructionList.contains(item);

            // Remove the item from the DOM
            item.remove();

            // If an instruction was removed, update the visible numbering
            if (isInstructionItem) {
                updateInstructionNumbers();
            }
        }

        // Renumber all visible instruction rows after adding or removing one
        function updateInstructionNumbers() {
            const items = document.querySelectorAll("#instruction-list .instruction-item");

            items.forEach((item, index) => {
                const newNumber = index + 1;
                const stepNumber = item.querySelector(".step-number");
                const input = item.querySelector('input[name="instruction[]"]');

                // Update the visible number
                if (stepNumber) {
                    stepNumber.textContent = newNumber + ".";
                }

                // Update the placeholder text
                if (input) {
                    input.placeholder = "Instructie " + newNumber;
                }
            });
        }

        // Enable or disable amount and unit fields when "to taste" is toggled
        function toggleAmount(checkbox) {
            // Find the table row the checkbox belongs to
            const row = checkbox.closest("tr");
            if (!row) return;

            // Find the related amount input, unit select, and hidden role input
            const amountInput = row.querySelector('input[name*="[aantal]"]');
            const unitSelect = row.querySelector('select[name*="[eenheid]"]');
            const roleInput = row.querySelector('input[name*="[role]"]');

            // Stop if required fields were not found
            if (!amountInput || !unitSelect) return;

            // When checked: disable amount and unit fields
            if (checkbox.checked) {
                amountInput.value = "";
                amountInput.disabled = true;
                unitSelect.value = "";
                unitSelect.disabled = true;

                // Set hidden role field to "naarsmaak"
                if (roleInput) {
                    roleInput.value = "naarsmaak";
                }
            } else {
                // When unchecked: enable amount and unit fields again
                amountInput.disabled = false;
                unitSelect.disabled = false;
                unitSelect.value = "st";

                // Set hidden role field back to standard
                if (roleInput) {
                    roleInput.value = "standaard";
                }
            }
        }

        // Attach change listeners to all existing "to taste" checkboxes
        document.querySelectorAll('input[type="checkbox"][name*="[naarsmaak]"]').forEach(cb => {
            cb.addEventListener("change", function() {
                toggleAmount(this);
            });
        });
    </script>
</body>

</html>