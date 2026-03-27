<?php
include_once("includes/connection.php");
$data = fetchData("SELECT * FROM users WHERE role_id = :role", [':role' => 2]);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    error_log("DEBUG: POST received");
    error_log("DEBUG: POST data: " . print_r($_POST, true));

    try {
        $pdo->beginTransaction();

        if (empty($_POST['namerecipe'])) {
            throw new Exception("Receptnaam is verplicht");
        }

        $checkDuplicateQuery = "SELECT id FROM recipe WHERE name = ? AND description = ? LIMIT 1";
        $stmtCheck = $pdo->prepare($checkDuplicateQuery);

        $description = $_POST['recipe_description'] ?? '';
        $instructions = $_POST['instruction'] ?? [];

        if (!is_array($instructions)) {
            $instructions = [$instructions];
        }

        $instructions = array_filter(array_map('trim', $instructions));
        $instructionsText = implode("\n", $instructions);

        $stmtCheck->execute([$_POST['namerecipe'], $description]);
        $existingRecipe = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existingRecipe) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            echo "<script>
                window.addEventListener('load', function () {
                    if (typeof showPopup === 'function') {
                        showPopup('Dit recept bestaat al in de database!', 'error');
                    } else {
                        alert('Dit recept bestaat al in de database!');
                    }
                    setTimeout(function () {
                        window.location.href = 'Add_Recipe.php';
                    }, 2000);
                });
            </script>";
            exit;
        }

        /* ==========================
           RECEPT OPSLAAN
        ========================== */
        $userID = $_SESSION['user_id'] ?? $_POST['user_id'] ?? 1;

        if (empty($userID)) {
            throw new Exception("user_id is required");
        }

        $queryRecipe = "
            INSERT INTO recipe 
            (user_id, name, description, instructions, createdat, Sterren)
            VALUES (:user_id, :name, :description, :instructions, :createdat, :Sterren)
        ";

        $stmtRecipe = $pdo->prepare($queryRecipe);

        $stmtRecipe->execute([
            ':user_id'      => $userID,
            ':name'         => $_POST['namerecipe'] ?? '',
            ':description'  => $description,
            ':instructions' => $instructionsText,
            ':createdat'    => date('Y-m-d H:i:s'),
            ':Sterren'      => $_POST['Sterren']
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
                if (empty($materiaalName)) {
                    continue;
                }

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
            $insertIngredientQuery = "INSERT INTO ingredient (name, categoryid) VALUES (:name, :cat_id)";
            $stmtCheckIngredient  = $pdo->prepare($checkIngredientQuery);
            $stmtInsertIngredient = $pdo->prepare($insertIngredientQuery);

            $queryRecipeIngredient = "
                INSERT INTO recipeingredient 
                (recipe_id, ingredient_id, Aantal, Eenheid, IngredientRole) 
                VALUES (:rec_id, :ing_id, :aantal, :eenheid, :role)
            ";
            $stmtRecipeIngredient = $pdo->prepare($queryRecipeIngredient);

            foreach ($_POST['ingredient_name'] as $index => $ingredientName) {
                $ingredientName = trim($ingredientName);
                if (empty($ingredientName)) {
                    continue;
                }

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
                ':recipe_id'   => $recipeID,
                ':description' => $_POST['aanvullingen']
            ]);
        }

        /* ==========================
           FOTO TOEVOEGEN (LONGBLOB)
        ========================== */
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['photo']['tmp_name'];
            $originalName = $_FILES['photo']['name'];

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $tmpName);
            finfo_close($finfo);

            $allowed = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($mime_type, $allowed)) {
                throw new Exception('Ongeldig afbeeldingsformaat. Alleen JPG/PNG/GIF toegestaan.');
            }

            $imageData = file_get_contents($tmpName);
            if ($imageData === false) {
                throw new Exception('Kon afbeelding niet lezen');
            }

            $query = "
                INSERT INTO photo 
                (user_id, recipe_id, filename, mime_type, image, uploaded_at)
                VALUES (:user_id, :recipe_id, :filename, :mime_type, :image, :uploaded_at)
            ";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':user_id', $userID, PDO::PARAM_INT);
            $stmt->bindParam(':recipe_id', $recipeID, PDO::PARAM_INT);
            $stmt->bindParam(':filename', $originalName, PDO::PARAM_STR);
            $stmt->bindParam(':mime_type', $mime_type, PDO::PARAM_STR);
            $stmt->bindParam(':image', $imageData, PDO::PARAM_LOB);
            $stmt->bindValue(':uploaded_at', date('Y-m-d H:i:s'));
            $stmt->execute();
        }

        $pdo->commit();

        echo "<script>
            showPopup('Recept succesvol opgeslagen!', 'success');
            setTimeout(function () {
                window.location.href = 'recept_pagina.php?id=" . $recipeID . "';
            }, 2000);
        </script>";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $error = $e->getMessage();
        error_log("Add_Recipe Error: " . $error);
        error_log("Backtrace: " . $e->getTraceAsString());

        echo "<script>showPopup('Fout: " . addslashes($error) . "', 'error');</script>";
        echo "<!-- Debug: " . htmlspecialchars($error) . " -->";
        echo "<div class='debug-error-box'>";
        echo "<strong>Debug Fout:</strong><br>";
        echo htmlspecialchars($error);
        echo "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recept toevoegen</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include("includes/header.php"); ?>

    <div id="popup-message" class="popup-message"></div>

    <main class="addRecipe-main">
        <section class="addRecipe-section">
            <form method="POST" enctype="multipart/form-data">
                <label class="form-label">Naam recept</label>
                <input name="namerecipe" placeholder="Naam recept" required>

                <select name="Sterren" class="sterren-dropdown" required>
                    <option value="Beginner ☆☆☆">Beginner ☆☆☆</option>
                    <option value="Makkelijk ⭐☆☆">Makkelijk ⭐☆☆</option>
                    <option value="Gemiddeld ⭐⭐☆">Gemiddeld ⭐⭐☆</option>
                    <option value="Moeilijk ⭐⭐⭐">Moeilijk ⭐⭐⭐</option>
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
                        <tr class="ingredient-item">
                            <td>
                                <input type="checkbox" name="ingredient_naarsmaak[]" onchange="toggleAmount(this)">
                            </td>
                            <td>
                                <input name="ingredient_amount[]" type="number" step="any" placeholder="Aantal">
                            </td>
                            <td>
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
                            </td>
                            <td>
                                <input class="ingredient_name" name="ingredient_name[]" placeholder="Ingrediënt">
                            </td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>

                <button type="button" class="recipe-submit" onclick="addIngredient()">Ingrediënt toevoegen</button>

                <label class="form-label">Instructies</label>
                <ul id="instruction-list">
                    <li class="instruction-item">
                        <span class="step-number">1.</span>
                        <input name="instruction[]" type="text" placeholder="Instructie 1" class="instruction-input">
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
        function showPopup(message, type) {
            const popup = document.getElementById("popup-message");
            popup.textContent = message;
            popup.className = "popup-message " + type;
            popup.style.display = "block";

            setTimeout(() => {
                popup.style.display = "none";
            }, 3000);
        }

        function addIngredient() {
            const tbody = document.getElementById("ingredienten");
            const tr = document.createElement("tr");
            tr.className = "ingredient-item";

            tr.innerHTML = `
                <td>
                    <input type="checkbox" name="ingredient_naarsmaak[]" onchange="toggleAmount(this)">
                </td>
                <td>
                    <input name="ingredient_amount[]" type="number" step="any" placeholder="Aantal">
                </td>
                <td>
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
                </td>
                <td>
                    <input name="ingredient_name[]" placeholder="Ingrediënt">
                </td>
                <td>
                    <button type="button" onclick="removeItem(this)">❌</button>
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
                <input name="instruction[]" type="text" placeholder="Instructie ${itemCount}" class="instruction-input">
                <button type="button" onclick="removeItem(this); updateInstructionNumbers()">❌</button>
            `;

            instructionList.appendChild(li);
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
            const item = button.closest("li, tr");
            if (!item) return;

            const instructionList = document.getElementById("instruction-list");
            const isInstructionItem = instructionList && instructionList.contains(item);

            item.remove();

            if (isInstructionItem) {
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

            const amountInput = row.querySelector('input[name="ingredient_amount[]"]');
            const eenheidSelect = row.querySelector('select[name="ingredient_unit[]"]');
            if (!amountInput) return;

            if (checkbox.checked) {
                amountInput.value = "";
                amountInput.disabled = true;
                eenheidSelect.value = "";
                eenheidSelect.disabled = true;
            } else {
                amountInput.disabled = false;
                eenheidSelect.disabled = false;
                eenheidSelect.value = "st";
            }
        }

        document.querySelectorAll('input[name="ingredient_naarsmaak[]"]').forEach(cb => {
            cb.addEventListener("change", function () {
                toggleAmount(this);
            });
        });
    </script>
</body>
</html>