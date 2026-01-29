<?php
include "includes/connect.php";
$conn = connect();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    try {
        $conn->beginTransaction();

        /* ==========================
           RECEPT OPSLAAN
        ========================== */
        $queryRecipe = "
            INSERT INTO recipe 
            (Name, Description, Instructions, Createdat)
            VALUES (:name, :description, :instructions, :createdat)
        ";

        $stmtRecipe = $conn->prepare($queryRecipe);
        $stmtRecipe->execute([
            ':name'         => $_POST['namerecipe'],
            ':description'  => $_POST['recipe_description'],
            ':instructions' => json_encode($_POST['instruction']),
            ':createdat'    => date('Y-m-d H:i:s')
        ]);

        $recipeID = $conn->lastInsertId();

        /* ==========================
           MATERIALEN VERWERKEN
        ========================== */
        if (!empty($_POST['materiaal'])) {
            $checkMaterialQuery = "SELECT id FROM material WHERE name = :name LIMIT 1";
            $insertMaterialQuery = "INSERT INTO material (name) VALUES (:name)";
            $stmtCheckMaterial  = $conn->prepare($checkMaterialQuery);
            $stmtInsertMaterial = $conn->prepare($insertMaterialQuery);

            $insertRecipeMaterialQuery = "INSERT INTO recipematerial (Recipe_id, material_id) VALUES (:recipe_id, :material_id)";
            $stmtRecipeMaterial = $conn->prepare($insertRecipeMaterialQuery);

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
           INGREDIËNTEN VERWERKEN
        ========================== */
        $checkIngredientQuery = "SELECT id FROM ingredient WHERE name = :name LIMIT 1";
        $insertIngredientQuery = "INSERT INTO ingredient (name, categoryID) VALUES (:name, :categoryID)";
        $stmtCheckIngredient  = $conn->prepare($checkIngredientQuery);
        $stmtInsertIngredient = $conn->prepare($insertIngredientQuery);

        $queryRecipeIngredient = "
            INSERT INTO recipeingredient
            (recipe_id, Ingredient_id, Aantal, Eenheid, IngredientRole)
            VALUES (:recipe_id, :Ingredient_id, :Aantal, :Eenheid, :IngredientRole)
        ";
        $stmtRecipeIngredient = $conn->prepare($queryRecipeIngredient);

        foreach ($_POST['ingredient_name'] as $index => $ingredientName) {
            $ingredientName = trim($ingredientName);
            if (empty($ingredientName)) continue;

            $stmtCheckIngredient->execute([':name' => $ingredientName]);
            $ingredient = $stmtCheckIngredient->fetch(PDO::FETCH_ASSOC);

            if (!$ingredient) {
                $stmtInsertIngredient->execute([
                    ':name'       => $ingredientName,
                    ':categoryID' => 18
                ]);
                $ingredientID = $conn->lastInsertId();
            } else {
                $ingredientID = $ingredient['id'];
            }

            // FIX: Undefined array key probleem
            $naarSmaak = ($_POST['ingredient_naarsmaak'][$index] ?? '') === 'on';

            $stmtRecipeIngredient->execute([
                ':recipe_id'      => $recipeID,
                ':Ingredient_id'  => $ingredientID,
                ':Aantal'         => $naarSmaak ? null : $_POST['ingredient_amount'][$index],
                ':Eenheid'        => $naarSmaak ? '*' : $_POST['ingredient_unit'][$index],
                ':IngredientRole' => $naarSmaak ? 'naarsmaak' : ($_POST['ingredient_role'][$index] ?? 'standaard')
            ]);
        }

        /* ==========================
           AANVULLINGEN VERWERKEN
        ========================== */
        if (!empty($_POST['aanvullingen'])) {
            $queryAanvulling = "
                INSERT INTO aanvulling (Recipe_id, description)
                VALUES (:recipe_id, :description)
            ";
            $stmtAanvulling = $conn->prepare($queryAanvulling);
            $stmtAanvulling->execute([
                ':recipe_id'  => $recipeID,
                ':description' => $_POST['aanvullingen']
            ]);
        }

        $conn->commit();

        // POPUP SUCCESS
        echo "<script>showPopup('Recept succesvol opgeslagen!', 'success');</script>";
    } catch (Exception $e) {
        $conn->rollBack();
        echo "<script>showPopup('Fout: " . addslashes($e->getMessage()) . "', 'error');</script>";
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
            <form method="POST">

                <label class="form-label">Naam recept</label>
                <input name="namerecipe" required>

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