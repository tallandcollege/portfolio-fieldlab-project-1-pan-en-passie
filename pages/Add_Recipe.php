<?php
include "../includes/connect.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recept Toevoegen</title>
</head>

<body>
    <form method="post">
        <label>Naam recept</label><br>
        <input name="NaamRecept"><br>
        <label>Beschrijving</label><br>
        <textarea id="beschrijving" name="notes" rows="5" cols="30"></textarea><br><br>
        <label>Ingredienten</label><br>
        <ul id="ingredienten">
            <li>
                <input name="ingredient_name[]" placeholder="Ingredient">
                <select name="ingredient_unit[]">
                    <option value="Stuks">Stuks</option>
                    <option value="G">G</option>
                    <option value="KG">KG</option>
                    <option value="ML">ML</option>
                    <option value="L">L</option>


                </select>
                <input name="ingredient_amount[]" type="number">
            </li>
        </ul>

        <button type="button" onclick="addIngredient()">Add Ingredient</button>

        <label>Instructies</label>
        <ul id="instruction-list">
            <li>
                <textarea name="instruction[]" rows="1" cols="30"></textarea>
            </li>
        </ul>

        <button type="button" onclick="addInstruction()">Add Instruction</button>

        <label>Notities</label><br>
        <textarea name="notes" rows="5" cols="30"></textarea><br><br>

        <button type="submit" name="submit">Confirm</button>
    </form>

    <script>
        function addIngredient() {
            const list = document.getElementById("ingredienten");
            const li = document.createElement("li");

            li.innerHTML = `
                <input name="ingredient_name[]" placeholder="Ingredient">
                <select name="ingredient_unit[]">
                    <option value="ML">ML</option>
                    <option value="L">L</option>
                    <option value="G">G</option>
                    <option value="KG">KG</option>
                    <option value="Stuks">Stuks</option>
                </select>
                <input name="ingredient_amount[]" type="number" placeholder="Hoeveelheid">
                <button type="button" class="remove-btn" onclick="removeItem(this)">-</button>
            `;

            list.appendChild(li);
        }

        function addInstruction() {
            const list = document.getElementById("instruction-list");
            const li = document.createElement("li");

            li.innerHTML = `
                <textarea name="instruction[]" rows="1" cols="30"></textarea>
                <button type="button" class="remove-btn" onclick="removeItem(this)">-</button>
            `;

            list.appendChild(li);
        }

        function removeItem(button) {
            button.parentElement.remove();
        }
    </script>
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $conn = connect();

        // Convert instructions array to JSON
        $instructions = json_encode($_POST['instruction']);
        $createdat = date('Y-m-d H:i:s');

        $query = "INSERT INTO recipe (name, description, instructions, createdat)
              VALUES (:name, :description, :instructions, :createdat)";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':name', $_POST['name']);
        $stmt->bindParam(':description', $_POST['description']);
        $stmt->bindParam(':instructions', $instructions);
        $stmt->bindParam(':createdat', $createdat);

        try {
            $stmt->execute();
            echo "<p style='color:green;'>Recept toegevoegd!</p>";
        } catch (PDOException $e) {
            echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
        }
    }
    ?>
</body>

</html>