<?php
include "../includes/connect.php";
$conn = connect();
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $query = "INSERT INTO recipe 
        (namerecipe, recipe_description, instructions, createdat)
        VALUES (:name, :desc, :inst, :created)";

    $stmt = $conn->prepare($query);

    $stmt->execute([
        ':name'    => $_POST['namerecipe'],
        ':desc'    => $_POST['recipe_description'],
        ':inst'    => json_encode($_POST['instruction']),
        ':created' => date('Y-m-d H:i:s')
    ]);

    echo "<p style='color:green;'>Recept opgeslagen! ID = " . $conn->lastInsertId() . "</p>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Recept toevoegen</title>
</head>
<body>

<form method="POST" action="">
    <label>Naam recept</label><br>
    <input name="namerecipe" required><br>

    <label>Beschrijving</label><br>
    <textarea name="recipe_description"></textarea><br><br>

    <label>Ingredienten</label>
    <ul id="ingredienten">
        <li>
            <input name="ingredient_name[]" placeholder="Ingredient">
            <select name="ingredient_unit[]">
                <option>Stuks</option>
                <option>G</option>
                <option>KG</option>
                <option>ML</option>
                <option>L</option>
            </select>
            <input name="ingredient_amount[]" type="number">
        </li>
    </ul>

    <button type="button" onclick="addIngredient()">Add Ingredient</button>

    <label>Instructies</label>
    <ul id="instruction-list">
        <li><textarea name="instruction[]"></textarea></li>
    </ul>

    <button type="button" onclick="addInstruction()">Add Instruction</button>

    <label>Notities</label><br>
    <textarea name="notes"></textarea><br><br>

    <button type="submit">Confirm</button>
</form>

<script>
function addIngredient() {
    const li = document.createElement("li");
    li.innerHTML = `
        <input name="ingredient_name[]">
        <select name="ingredient_unit[]">
            <option>ML</option><option>L</option><option>G</option><option>KG</option><option>Stuks</option>
        </select>
        <input name="ingredient_amount[]" type="number">
    `;
    document.getElementById("ingredienten").appendChild(li);
}

function addInstruction() {
    const li = document.createElement("li");
    li.innerHTML = `<textarea name="instruction[]"></textarea>`;
    document.getElementById("instruction-list").appendChild(li);
}
</script>

</body>
</html>
