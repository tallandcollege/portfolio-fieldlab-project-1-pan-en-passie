<?php
include "../includes/connect.php";

$conn = connect();
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    try {
        $conn->beginTransaction();

        /* ==========================
           RECEPT OPSLAAN
        ========================== */
        $queryRecipe = "
            INSERT INTO recipe 
            (namerecipe, recipe_description, instructions, createdat)
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

                // Check of materiaal bestaat
                $stmtCheckMaterial->execute([':name' => $materiaalName]);
                $materiaal = $stmtCheckMaterial->fetch(PDO::FETCH_ASSOC);

                if (!$materiaal) {
                    // Voeg nieuw materiaal toe
                    $stmtInsertMaterial->execute([':name' => $materiaalName]);
                    $materiaalID = $conn->lastInsertId();
                } else {
                    $materiaalID = $materiaal['id'];
                }

                // Voeg koppeling toe in recipematerial
                $stmtRecipeMaterial->execute([
                    ':recipe_id'   => $recipeID,
                    ':material_id' => $materiaalID
                ]);
            }
        }


        /* ==========================
           INGREDIENTEN VERWERKEN
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

            // Check of ingredient bestaat
            $stmtCheckIngredient->execute([':name' => $ingredientName]);
            $ingredient = $stmtCheckIngredient->fetch(PDO::FETCH_ASSOC);

            if (!$ingredient) {
                // Voeg ingredient toe
                $stmtInsertIngredient->execute([
                    ':name'       => $ingredientName,
                    ':categoryID' => 18
                ]);
                $ingredientID = $conn->lastInsertId();
            } else {
                $ingredientID = $ingredient['id'];
            }

            // Voeg relatie toe in recipeingredient met gekoppelde amount, unit en role
            $stmtRecipeIngredient->execute([
                ':recipe_id'      => $recipeID,
                ':Ingredient_id'  => $ingredientID,
                ':Aantal'         => $_POST['ingredient_amount'][$index],
                ':Eenheid'        => $_POST['ingredient_unit'][$index],
                ':IngredientRole' => $_POST['ingredient_role'][$index]
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

        echo "<p style='color:green;'>Recept succesvol opgeslagen!</p>";
    } catch (Exception $e) {
        $conn->rollBack();
        echo "<p style='color:red;'>Fout: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <title>Recept toevoegen</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
<?php include '../includes/header.php'; ?>
    <form method="POST">

        <label>Naam recept</label><br>
        <input name="namerecipe" required><br><br>

        <label>Beschrijving</label><br>
        <textarea name="recipe_description"></textarea><br><br>
        <label>Materialen</label><br>
        <ul id="materialen-list">
            <li class="materiaal-item">
                <input name="materiaal[]" placeholder="Materiaal">
                <button type="button" onclick="removeItem(this)">❌</button>
            </li>
        </ul>
        <button type="button" onclick="addMateriaal()">Materiaal toevoegen</button>
        <br><br>

        <label>Ingrediënten</label><br>
        <ul id="ingredienten">
            <li class="ingredient-item">
                <input name="ingredient_name[]" placeholder="Ingrediënt">
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
                <input name="ingredient_amount[]" type="number" step="any">
                <select name="ingredient_role[]">
                    <option value="hoofdingredient">Hoofdingrediënt</option>
                    <option value="groente">Groente</option>
                    <option value="gehakt">Gehakt</option>
                    <option value="meel">Meel</option>
                    <option value="kruid">Kruid</option>
                    <option value="azijn">Azijn</option>
                    <option value="hulpmiddel">Hulpmiddel</option>
                    <option value="olie">Olie</option>
                    <option value="specerij">Specerij</option>
                    <option value="naar_smaak">Naar smaak</option>
                    <option value="zuur">Zuur</option>
                </select>
                <button type="button" onclick="removeItem(this)">❌</button>
            </li>
        </ul>

        <button type="button" onclick="addIngredient()">Ingrediënt toevoegen</button>
        <br><br>

        <label>Instructies</label>
        <ul id="instruction-list">
            <li class="instruction-item">
                <textarea name="instruction[]"></textarea>
                <button type="button" onclick="removeItem(this)">❌</button>
            </li>
        </ul>
        <button type="button" onclick="addInstruction()">Instructie toevoegen</button>

        <br><br>
        <label>Notities</label><br>
        <textarea name="aanvullingen"></textarea><br><br>

        <button type="submit">Opslaan</button>
    </form>

    <script>
        function addIngredient() {
            const li = document.createElement("li");
            li.className = "ingredient-item";
            li.innerHTML = `
        <input name="ingredient_name[]" placeholder="Ingrediënt">
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
        <input name="ingredient_amount[]" type="number" step="any">
        <select name="ingredient_role[]">
            <option value="hoofdingredient">Hoofdingrediënt</option>
            <option value="groente">Groente</option>
            <option value="gehakt">Gehakt</option>
            <option value="meel">Meel</option>
            <option value="kruid">Kruid</option>
            <option value="azijn">Azijn</option>
            <option value="hulpmiddel">Hulpmiddel</option>
            <option value="olie">Olie</option>
            <option value="specerij">Specerij</option>
            <option value="naar_smaak">Naar smaak</option>
            <option value="zuur">Zuur</option>
        </select>
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
    </script>
    <?php include '../includes/footer.php'; ?>
</body>

</html>