<?php
include "includes/connection.php";


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    error_log("DEBUG: POST received");
    error_log("DEBUG: POST data: " . print_r($_POST, true));

    try {
        $pdo->beginTransaction();

        // Valideer required velden
        if (empty($_POST['namerecipe'])) {
            throw new Exception("Receptnaam is verplicht");
        }

        // Check of recept al bestaat (duplicate prevention)
        $checkDuplicateQuery = "SELECT id FROM recipe WHERE name = ? AND description = ? LIMIT 1";
        $stmtCheck = $pdo->prepare($checkDuplicateQuery);

        $description = $_POST['recipe_description'] ?? '';
        $instructions = $_POST['instruction'] ?? [];
        if (!is_array($instructions)) {
            $instructions = [$instructions];
        }
        $instructionsJson = json_encode($instructions);

        $stmtCheck->execute([$_POST['namerecipe'], $description]);
        $existingRecipe = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existingRecipe) {
            // Rol terug en toon een popup in de browser (of fallback naar alert), daarna redirect naar Add_Recipe
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo "<script>
                window.addEventListener('load', function(){
                    if (typeof showPopup === 'function') {
                        showPopup('Dit recept bestaat al in de database!', 'error');
                    } else {
                        alert('Dit recept bestaat al in de database!');
                    }
                    setTimeout(function(){
                        window.location.href = 'Add_Recipe.php';
                    }, 2000);
                });
            </script>";
            exit;
        }

        /* ==========================
           RECEPT OPSLAAN
        ========================== */
        $userID = $_SESSION['user_id'] ?? $_POST['user_id'] ?? 1; // Default to user 1 (admin)

        if (empty($userID)) {
            throw new Exception("user_id is required");
        }

        $queryRecipe = "
            INSERT INTO recipe 
            (user_id, name, description, instructions, createdat)
            VALUES (:user_id, :name, :description, :instructions, :createdat)
        ";

        $stmtRecipe = $pdo->prepare($queryRecipe);

        $stmtRecipe->execute([
            ':user_id'      => $userID,
            ':name'         => $_POST['namerecipe'] ?? '',
            ':description'  => $description,
            ':instructions' => $instructionsJson,
            ':createdat'    => date('Y-m-d H:i:s')
        ]);

        $recipeID = $pdo->lastInsertId();

        /* ==========================
           MATERIALEN VERWERKEN
        ========================== */
        if (!empty($_POST['materiaal'])) {
            $checkMaterialQuery = "SELECT id FROM material WHERE name = :name LIMIT 1";
            $insertMaterialQuery = "INSERT INTO material (name) VALUES (:name)";
            $stmtCheckMaterial  = $pdo->prepare($checkMaterialQuery);
            $stmtInsertMaterial = $pdo->prepare($insertMaterialQuery);

            $insertRecipeMaterialQuery = "INSERT INTO recipematerial (Recipe_id, material_id) VALUES (:recipe_id, :material_id)";
            $stmtRecipeMaterial = $pdo->prepare($insertRecipeMaterialQuery);

            foreach ($_POST['materiaal'] as $materiaalName) {
                $materiaalName = trim($materiaalName);
                if (empty($materiaalName)) continue;

                $stmtCheckMaterial->execute([':name' => $materiaalName]);
                $materiaal = $stmtCheckMaterial->fetch(PDO::FETCH_ASSOC);

                if (!$materiaal) {
                    $stmtInsertMaterial->execute([':name' => $materiaalName]);
                    $materiaalID = $pdo->lastInsertId();
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
        if (!empty($_POST['ingredient_name'])) {
            $checkIngredientQuery = "SELECT id FROM ingredient WHERE name = :name LIMIT 1";
            $insertIngredientQuery = "INSERT INTO ingredient (name, categoryID) VALUES (:name, :cat_id)";
            $stmtCheckIngredient  = $pdo->prepare($checkIngredientQuery);
            $stmtInsertIngredient = $pdo->prepare($insertIngredientQuery);

            $queryRecipeIngredient = "INSERT INTO recipeingredient (recipe_id, ingredient_id, Aantal, Eenheid, IngredientRole) VALUES (:rec_id, :ing_id, :aantal, :eenheid, :role)";
            $stmtRecipeIngredient = $pdo->prepare($queryRecipeIngredient);

            foreach ($_POST['ingredient_name'] as $index => $ingredientName) {
                $ingredientName = trim($ingredientName);
                if (empty($ingredientName)) continue;

                $stmtCheckIngredient->execute([':name' => $ingredientName]);
                $ingredient = $stmtCheckIngredient->fetch(PDO::FETCH_ASSOC);

                if (!$ingredient) {
                    $stmtInsertIngredient->execute([
                        ':name'   => $ingredientName,
                        ':cat_id' => 18
                    ]);
                    $ingredientID = $pdo->lastInsertId();
                } else {
                    $ingredientID = $ingredient['id'];
                }

                $naarSmaak = ($_POST['ingredient_naarsmaak'][$index] ?? '') === 'on';

                $stmtRecipeIngredient->execute([
                    ':rec_id'   => $recipeID,
                    ':ing_id'   => $ingredientID,
                    ':aantal'   => $naarSmaak ? null : ($_POST['ingredient_amount'][$index] ?? null),
                    ':eenheid'  => $naarSmaak ? '*' : ($_POST['ingredient_unit'][$index] ?? null),
                    ':role'     => $naarSmaak ? 'naarsmaak' : ($_POST['ingredient_role'][$index] ?? 'standaard')
                ]);
            }
        }

        /* ==========================
           AANVULLINGEN VERWERKEN
        ========================== */
        if (!empty($_POST['aanvullingen'])) {
            $queryAanvulling = "
                INSERT INTO aanvulling (Recipe_id, description)
                VALUES (:recipe_id, :description)
            ";
            $stmtAanvulling = $pdo->prepare($queryAanvulling);
            $stmtAanvulling->execute([
                ':recipe_id'  => $recipeID,
                ':description' => $_POST['aanvullingen']
            ]);
        }

        /* ==========================
            FOTO TOEVOEGEN
        ========================== */

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {

            $name = $_FILES['photo']['name'];
            $tmpName = $_FILES['photo']['tmp_name'];
            $fileType = $_FILES['photo']['type'];
            $fileSize = $_FILES['photo']['size'];

            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 5 * 1024 * 1024; // 5 MB

            if (!in_array($fileType, $allowedTypes)) {
                die('Ongeldig bestandstype. Alleen JPG, PNG en GIF toegestaan.');
            }

            if ($fileSize > $maxSize) {
                die('Bestand te groot. Maximaal 5 MB.');
            }

            // Zorg dat de uploads map bestaat
            $targetDir = __DIR__ . '/uploads/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            // Unieke bestandsnaam maken
            $filename = uniqid() . '_' . basename($name);
            $targetFile = $targetDir . $filename;

            if (move_uploaded_file($tmpName, $targetFile)) {
                try {
                    $query = "INSERT INTO photo (user_id, recipe_id, filename, mime_type, uploaded_at) 
                      VALUES (:user_id, :recipe_id, :filename, :mime_type, NOW())";
                    $stmt = $pdo->prepare($query);
                    $stmt->execute([
                        ':user_id'   => $userID,
                        ':recipe_id' => $recipeID,
                        ':filename'  => $filename,
                        ':mime_type' => $fileType
                    ]);

                    echo "Receptfoto succesvol geüpload!";
                } catch (Exception $e) {
                    echo "Fout bij opslaan in database: " . $e->getMessage();
                }
            } else {
                echo "Fout bij uploaden van het bestand.";
            }
        }




        $pdo->commit();

        // Pop-up succes en redirect naar recept pagina
        echo "<script>
            showPopup('Recept succesvol opgeslagen!', 'success');
            setTimeout(function() {
                window.location.href = 'recept_pagina.php?id=" . $recipeID . "';
            }, 2000);
        </script>";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
        error_log("Add_Recipe Error: " . $error);
        echo "<script>showPopup('Fout: " . addslashes($error) . "', 'error');</script>";
        echo "<!-- Debug: " . htmlspecialchars($error) . " -->";
        echo "<div style='background: #fee; padding: 10px; margin: 10px; border: 1px solid red;'>";
        echo "<strong>Debug Fout:</strong><br>";
        echo htmlspecialchars($error);
        echo "</div>";
        error_log("Backtrace: " . $e->getTraceAsString());
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