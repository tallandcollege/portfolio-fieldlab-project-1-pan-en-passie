<?php
$pdo = require_once('includes/connection.php');

// Haal alle recepten op
$stmt = $pdo->query("
    SELECT r.id, r.name AS naam, GROUP_CONCAT(i.Name SEPARATOR ', ') AS ingredienten, '' AS foto
    FROM recipe r
    LEFT JOIN recipeingredient ri ON r.id = ri.`recipe_id`
    LEFT JOIN ingredient i ON ri.`ingredient_id` = i.id
    GROUP BY r.id
    ORDER BY r.name ASC
");
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$fotopad = 'fotos/';
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <title>Recepten overzicht</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    
</body>


</html>