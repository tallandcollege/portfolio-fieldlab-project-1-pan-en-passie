<?php
require_once "includes/connection.php";

$conn = connect();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    try {
        $conn->beginTransaction();

        /* ==========================
           BASIS VALIDATIE
        ========================== */

        $recipeName = trim($_POST['namerecipe'] ?? '');

        if (empty($recipeName)) {
            throw new Exception("Receptnaam is verplicht");
        }

        $userID = $_SESSION['user_id'] ?? $_POST['user_id'] ?? null;

        if (empty($userID)) {
            throw new Exception("Gebruiker niet gevonden. Log opnieuw in.");
        }

        // Difficulty beveiligen
        $allowedDifficulties = ['Beginner', 'Makkelijk', 'Gemiddeld', 'Gevorderd'];
        $Difficulty = $_POST['difficulty'] ?? 'Beginner';

        if (!in_array($Difficulty, $allowedDifficulties)) {
            $Difficulty = 'Beginner';
        }

        $description = trim($_POST['recipe_description'] ?? '');

        // Instructies verwerken
        $instructions = $_POST['instruction'] ?? [];
        if (!is_array($instructions)) {
            $instructions = [$instructions];
        }

        // Lege instructies verwijderen
        $instructions = array_filter(array_map('trim', $instructions));
        $instructionsJson = json_encode(array_values($instructions));

        /* ==========================
           DUPLICATE CHECK
        ========================== */

        $stmtCheck = $conn->prepare("
            SELECT id 
            FROM recipe 
            WHERE name = :name AND description = :description 
            LIMIT 1
        ");

        $stmtCheck->execute([
            ':name' => $recipeName,
            ':description' => $description
        ]);

        if ($stmtCheck->fetch()) {
            $conn->rollBack();
            throw new Exception("Dit recept bestaat al.");
        }

        /* ==========================
           RECEPT OPSLAAN
        ========================== */

        $stmtRecipe = $conn->prepare("
            INSERT INTO recipe 
            (user_id, name, description, instructions, createdat, Difficulty)
            VALUES (:user_id, :name, :description, :instructions, :createdat, :Difficulty)
        ");

        $stmtRecipe->execute([
            ':user_id'      => $userID,
            ':name'         => $recipeName,
            ':description'  => $description,
            ':instructions' => $instructionsJson,
            ':createdat'    => date('Y-m-d H:i:s'),
            ':Difficulty'   => $Difficulty
        ]);

        $recipeID = $conn->lastInsertId();

        /* ==========================
           MATERIALEN
        ========================== */

        if (!empty($_POST['materiaal']) && is_array($_POST['materiaal'])) {

            $stmtCheckMaterial = $conn->prepare("SELECT id FROM material WHERE name = :name LIMIT 1");
            $stmtInsertMaterial = $conn->prepare("INSERT INTO material (name) VALUES (:name)");
            $stmtRecipeMaterial = $conn->prepare("
                INSERT INTO recipematerial (Recipe_id, material_id) 
                VALUES (:recipe_id, :material_id)
            ");

            foreach ($_POST['materiaal'] as $materiaalName) {

                $materiaalName = trim($materiaalName);
                if (empty($materiaalName)) continue;

                $stmtCheckMaterial->execute([':name' => $materiaalName]);
                $materiaal = $stmtCheckMaterial->fetch(PDO::FETCH_ASSOC);

                if (!$materiaal) {
                    $stmtInsertMaterial->execute([':name' => $materiaalName]);
                    $materiaalID = $conn->lastInsertId();
                } else {
                    $materiaalID = $materiaal['id'];
                }

                $stmtRecipeMaterial->execute([
                    ':recipe_id'   => $recipeID,
                    ':material_id' => $materiaalID
                ]);
            }
        }

        /* ==========================
           INGREDIËNTEN
        ========================== */

        if (!empty($_POST['ingredient_name']) && is_array($_POST['ingredient_name'])) {

            $stmtCheckIngredient = $conn->prepare("SELECT id FROM ingredient WHERE name = :name LIMIT 1");
            $stmtInsertIngredient = $conn->prepare("
                INSERT INTO ingredient (name, categoryID) 
                VALUES (:name, :cat_id)
            ");

            $stmtRecipeIngredient = $conn->prepare("
                INSERT INTO recipeingredient 
                (recipe_id, ingredient_id, Aantal, Eenheid, IngredientRole) 
                VALUES (:rec_id, :ing_id, :aantal, :eenheid, :role)
            ");

            foreach ($_POST['ingredient_name'] as $index => $ingredientName) {

                $ingredientName = trim($ingredientName);
                if (empty($ingredientName)) continue;

                $stmtCheckIngredient->execute([':name' => $ingredientName]);
                $ingredient = $stmtCheckIngredient->fetch(PDO::FETCH_ASSOC);

                if (!$ingredient) {
                    $stmtInsertIngredient->execute([
                        ':name'   => $ingredientName,
                        ':cat_id' => 18 // "invullen"
                    ]);
                    $ingredientID = $conn->lastInsertId();
                } else {
                    $ingredientID = $ingredient['id'];
                }

                $naarSmaak = isset($_POST['ingredient_naarsmaak'][$index])
                    && $_POST['ingredient_naarsmaak'][$index] === 'on';

                $aantal  = $naarSmaak ? null : ($_POST['ingredient_amount'][$index] ?? null);
                $eenheid = $naarSmaak ? '*'  : ($_POST['ingredient_unit'][$index] ?? null);
                $role    = $naarSmaak ? 'naarsmaak' : 'standaard';

                $stmtRecipeIngredient->execute([
                    ':rec_id'  => $recipeID,
                    ':ing_id'  => $ingredientID,
                    ':aantal'  => $aantal,
                    ':eenheid' => $eenheid,
                    ':role'    => $role
                ]);
            }
        }

        /* ==========================
           AANVULLINGEN
        ========================== */

        if (!empty($_POST['aanvullingen'])) {

            $stmtAanvulling = $conn->prepare("
                INSERT INTO aanvulling (Recipe_id, description)
                VALUES (:recipe_id, :description)
            ");

            $stmtAanvulling->execute([
                ':recipe_id'  => $recipeID,
                ':description' => trim($_POST['aanvullingen'])
            ]);
        }

        /* ==========================
           FOTO
        ========================== */

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {

            $name = $_FILES['photo']['name'];
            $imageData = file_get_contents($_FILES['photo']['tmp_name']);

            $stmtPhoto = $conn->prepare("
                INSERT INTO photos (user_id, recipe_id, name, image)
                VALUES (:user_id, :recipe_id, :name, :image)
            ");

            $stmtPhoto->bindParam(':user_id', $userID, PDO::PARAM_INT);
            $stmtPhoto->bindParam(':recipe_id', $recipeID, PDO::PARAM_INT);
            $stmtPhoto->bindParam(':name', $name, PDO::PARAM_STR);
            $stmtPhoto->bindParam(':image', $imageData, PDO::PARAM_LOB);

            $stmtPhoto->execute();
        }

        $conn->commit();

        header("Location: recept_pagina.php?id=" . $recipeID);
        exit;
    } catch (Exception $e) {

        try {
            if ($conn && $conn->inTransaction()) {
                $conn->rollBack();
            }
        } catch (PDOException $rollbackError) {
            // rollback mislukt omdat connectie weg is
        }

        echo "<div style='color:red;font-weight:bold;'>
            Fout: " . htmlspecialchars($e->getMessage()) . "
          </div>";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recept toevoegen</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* POPUP STIJL */
        .popup-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #333;
            color: #fff;
            padding: 20px 30px;
            border-radius: 10px;
            font-size: 16px;
            display: none;
            z-index: 9999;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        .popup-message.success {
            background-color: #4CAF50;
        }

        .popup-message.error {
            background-color: #f44336;
        }
    </style>
</head>

<body>
    <?php include("includes/header.php"); ?>

    <div id="popup-message" class="popup-message"></div>

    <main class="addRecipe-main">
        <section class="addRecipe-section">
            <form method="POST" enctype="multipart/form-data">

                <label class="form-label">Naam recept</label>
                <input name="namerecipe" required>
                <select name="difficulty">
                    <option value="Beginner">Beginner ☆☆☆</option>
                    <option value="Makkelijk">Makkelijk ⭐☆☆</option>
                    <option value="Gemiddeld">Gemiddeld ⭐⭐☆</option>
                    <option value="Gevorderd">Gevorderd ⭐⭐⭐</option>
                </select>

                <label class="form-label">Beschrijving</label>
                <textarea name="recipe_description"></textarea>

                <label class="form-label">Materialen</label>
                <ul id="materialen-list">
                    <li class="materiaal-item">
                        <input name="materiaal[]" placeholder="Materiaal">
                        <button type="button" onclick="removeItem(this)">❌</button>
                    </li>
                </ul>
                <button type="button" class="recipe-submit" onclick="addMateriaal()">Materiaal toevoegen</button>

                <label class="form-label">Ingrediënten</label>
                <ul id="ingredienten">
                    <li class="ingredient-item">
                        <label class="naarsmaak">
                            <input type="checkbox" name="ingredient_naarsmaak[]" onchange="toggleAmount(this)">
                            Naar smaak
                        </label>

                        <input name="ingredient_amount[]" type="number" step="any">
                        <select name="ingredient_unit[]">
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
                        <input name="ingredient_name[]" placeholder="Ingrediënt">
                        <button type="button" onclick="removeItem(this)">❌</button>
                    </li>
                </ul>
                <button type="button" class="recipe-submit" onclick="addIngredient()">Ingrediënt toevoegen</button>

                <label class="form-label">Instructies</label>
                <ul id="instruction-list">
                    <li class="instruction-item">
                        <textarea name="instruction[]"></textarea>
                        <button type="button" onclick="removeItem(this)">❌</button>
                    </li>
                </ul>
                <button type="button" class="recipe-submit" onclick="addInstruction()">Instructie toevoegen</button>

                <label class="form-label">Notities</label>
                <textarea name="aanvullingen" class="aanvullingen"></textarea>


                <label>Upload foto:</label>
                <input type="file" name="photo" accept="image/*" required>

                <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id'] ?? 1; ?>">


                <button type="submit" class="recipe-submit">Opslaan</button>
            </form>

        </section>
    </main>

    <?php include("includes/footer.php"); ?>

    <script>
        // POPUP FUNCTIE
        function showPopup(message, type) {
            const popup = document.getElementById('popup-message');
            popup.textContent = message;
            popup.className = 'popup-message ' + type;
            popup.style.display = 'block';
            setTimeout(() => popup.style.display = 'none', 3000);
        }

        // DYNAMISCH TOEVOEGEN EN VERWIJDEREN
        function addIngredient() {
            const li = document.createElement("li");
            li.className = "ingredient-item";
            li.innerHTML = `
            <label class="naarsmaak">
                <input type="checkbox" name="ingredient_naarsmaak[]" onchange="toggleAmount(this)">
                Naar smaak
            </label>
            <input name="ingredient_amount[]" type="number" step="any">
            <select name="ingredient_unit[]">
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
            <input name="ingredient_name[]" placeholder="Ingrediënt">
            <button type="button" onclick="removeItem(this)">❌</button>
        `;
            document.getElementById("ingredienten").appendChild(li);
        }

        function addInstruction() {
            const li = document.createElement("li");
            li.className = "instruction-item";
            li.innerHTML = `
            <textarea name="instruction[]"></textarea>
            <button type="button" onclick="removeItem(this)">❌</button>
        `;
            document.getElementById("instruction-list").appendChild(li);
        }

        function addMateriaal() {
            const li = document.createElement("li");
            li.className = "materiaal-item";
            li.innerHTML = `
            <input name="materiaal[]" placeholder="Materiaal">
            <button type="button" onclick="removeItem(this)">❌</button>
        `;
            document.getElementById("materialen-list").appendChild(li);
        }

        function removeItem(button) {
            button.closest("li").remove();
        }

        // NAAAR SMAAK TOGGLE
        function toggleAmount(checkbox) {
            const amountInput = checkbox.closest("li").querySelector('input[name="ingredient_amount[]"]');
            if (checkbox.checked) {
                amountInput.value = '';
                amountInput.disabled = true;
            } else {
                amountInput.disabled = false;
            }
        }

        // INITIËLE NAAAR SMAAK CHECKBOX
        document.querySelectorAll('input[name="ingredient_naarsmaak[]"]').forEach(cb => {
            cb.addEventListener('change', () => toggleAmount(cb));
        });
    </script>
</body>

</html>