<?php
include "includes/connect.php";
$conn = connect();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    try {
        $conn->beginTransaction();
        /*Maakt de MySQL command klaar om informatie toe te voegen aan 'recipe' */
        $queryRecipe = "       
            INSERT INTO recipe 
            (Name, Description, Instructions, Createdat)
            VALUES (:name, :description, :instructions, :createdat)
        ";
        /*Pakt de info uit de form en post het in de database */
        $stmtRecipe = $conn->prepare($queryRecipe);
        $stmtRecipe->execute([
            ':name'         => $_POST['namerecipe'],
            ':description'  => $_POST['recipe_description'],
            ':instructions' => json_encode($_POST['instruction']),
            ':createdat'    => date('Y-m-d H:i:s')
        ]);

        $recipeID = $conn->lastInsertId();

        /*Maakt de MySQL command klaar om informatie toe te voegen aan 'recipe' */
        if (!empty($_POST['materiaal'])) {
            $checkMaterialQuery = "SELECT id FROM material WHERE name = :name LIMIT 1"; /*Controleert of ingevoerde 'material' al in de database zit */
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
                /*Voegt materialen toe aan de database als ze er niet al in zitten */
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

        /*Maakt de MySQL command klaar om informatie toe te voegen aan 'ingredient' */
        $checkIngredientQuery = "SELECT id FROM ingredient WHERE name = :name LIMIT 1"; /*Controleert of ingevoerde 'ingredient' al in de database zit */
        $insertIngredientQuery = "INSERT INTO ingredient (name, categoryID) VALUES (:name, :categoryID)";
        $stmtCheckIngredient  = $conn->prepare($checkIngredientQuery);
        $stmtInsertIngredient = $conn->prepare($insertIngredientQuery);
        /*Maakt de MySQL command klaar om informatie toe te voegen aan 'recipeingredient' */
        $queryRecipeIngredient = "
            INSERT INTO recipeingredient
            (recipe_id, Ingredient_id, Aantal, Eenheid, IngredientRole)
            VALUES (:recipe_id, :Ingredient_id, :Aantal, :Eenheid, :IngredientRole)
        ";
        $stmtRecipeIngredient = $conn->prepare($queryRecipeIngredient);
        /*Controleert of ingevoerde ingredient al zit in 'ingredient' */
        foreach ($_POST['ingredient_name'] as $index => $ingredientName) {
            $ingredientName = trim($ingredientName);
            if (empty($ingredientName)) continue;

            $stmtCheckIngredient->execute([':name' => $ingredientName]);
            $ingredient = $stmtCheckIngredient->fetch(PDO::FETCH_ASSOC);
            /*Zo niet, wordt deze opgeslagen als een nieuw ingredient, met categorie 'invullen' */
            if (!$ingredient) {
                $stmtInsertIngredient->execute([
                    ':name'       => $ingredientName,
                    ':categoryID' => 18
                ]);
                $ingredientID = $conn->lastInsertId();
            } else {
                $ingredientID = $ingredient['id'];
            }

            /*Controleert of de blok naar smaak is ingeklikt. zo wel dan zal de aantal en eenheid onthouden worden met "naar smaak"*/
            $naarSmaak = ($_POST['ingredient_naarsmaak'][$index] ?? '') === 'on';

            $stmtRecipeIngredient->execute([
                ':recipe_id'      => $recipeID,
                ':Ingredient_id'  => $ingredientID,
                ':Aantal'         => $naarSmaak ? null : $_POST['ingredient_amount'][$index],
                ':Eenheid'        => $naarSmaak ? '*' : $_POST['ingredient_unit'][$index],
                ':IngredientRole' => $naarSmaak ? 'naarsmaak' : ($_POST['ingredient_role'][$index] ?? 'standaard')
            ]);
        }

        /*Controleert of aanvullingen leeg is of niet, zo niet wordt deze code uitgevoerd*/
        if (!empty($_POST['aanvullingen'])) {
            $queryAanvulling = "
                INSERT INTO aanvulling (Recipe_id, description)
                VALUES (:recipe_id, :description)
            "; /*^Voegt info toe aan de aanvullingen table*/
            $stmtAanvulling = $conn->prepare($queryAanvulling);
            $stmtAanvulling->execute([
                ':recipe_id'  => $recipeID,
                ':description' => $_POST['aanvullingen']
            ]);
        }

        $conn->commit();

/*recept successvol opgeslagen WIP*/        
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
                <section class="Recipe_name">
                    <input name="namerecipe" required>
                    <select>
                        <option value="Makkelijk">Makkelijk ⭐</option>
                        <option value="Gemiddeld">Gemiddeld ⭐⭐</option>
                        <option value="Moeilijk">Moeilijk ⭐⭐⭐</option>
                    </select>
                </section>
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
        /*WIP*/
        function showPopup(message, type) {
            const popup = document.getElementById('popup-message');
            popup.textContent = message;
            popup.className = 'popup-message ' + type;
            popup.style.display = 'block';
            setTimeout(() => popup.style.display = 'none', 3000);
        }

        /*Zorgt ervoor dat er meer openingen opkomen om ingredienten toe te voegen aan het recept*/
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
        /*Zorgt ervoor dat er meer openingen opkomen om instructies toe te voegen aan het recept*/
        function addInstruction() {
            const li = document.createElement("li");
            li.className = "instruction-item";
            li.innerHTML = `
            <textarea name="instruction[]"></textarea>
            <button type="button" onclick="removeItem(this)">❌</button>
        `;
            document.getElementById("instruction-list").appendChild(li);
        }
        /*Zorgt ervoor dat er meer openingen opkomen om materialen toe te voegen aan het recept*/
        function addMateriaal() {
            const li = document.createElement("li");
            li.className = "materiaal-item";
            li.innerHTML = `
            <input name="materiaal[]" placeholder="Materiaal">
            <button type="button" onclick="removeItem(this)">❌</button>
        `;
            document.getElementById("materialen-list").appendChild(li);
        }
        /*Verwijdert bijbehorende item*/
        function removeItem(button) {
            button.closest("li").remove();
        }
        /*zorgt ervoor dat als naar smaak is aangeklikt, de aantallen blok van je recept leeggaat en niet ingevuld kan worden. je kan weer 
        een aantal toevoegen als deze is uitgeklikt*/
        function toggleAmount(checkbox) {
            const amountInput = checkbox.closest("li").querySelector('input[name="ingredient_amount[]"]');
            if (checkbox.checked) {
                amountInput.value = '';
                amountInput.disabled = true;
            } else {
                amountInput.disabled = false;
            }
        }
        document.querySelectorAll('input[name="ingredient_naarsmaak[]"]').forEach(cb => {
            cb.addEventListener('change', () => toggleAmount(cb));
        });
    </script>
</body>

</html>