<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <?php

    if (isset($_GET['term'])) {
        $pdo = require_once 'connection.php';


        // Optional safety check
        if (!$pdo instanceof PDO) {
            echo "Connection failed — \$pdo is not a PDO object!";
            var_dump($pdo);
            exit;
        }

        $term = trim($_GET['term']);
        if ($term === '') {
            echo '';
            exit;
        }

        $like = "%{$term}%";
        $stmt = $pdo->prepare("SELECT r.id, r.name as naam, GROUP_CONCAT(i.Name SEPARATOR ', ') as ingredienten, '' as foto FROM recipe r LEFT JOIN RecipeIngredient ri ON r.id = ri.`recipe_id` LEFT JOIN ingredient i ON ri.`ingredient_id` = i.id WHERE r.name LIKE :term GROUP BY r.id LIMIT 10");
        $stmt->execute(['term' => $like]);
        $rows = $stmt->fetchAll();
    }

    $fotopad = 'fotos/';
    if (isset($rows)) {

        if (!$rows) {
            echo '<div class="leeg">Geen resultaten gevonden</div>';
            exit;
        }
        
        foreach ($rows as $resultaten) {
            $naam = htmlspecialchars($resultaten['naam'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $ing  = htmlspecialchars($resultaten['ingredienten'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $foto = $resultaten['foto'] ? htmlspecialchars($fotopad . $resultaten['foto'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : 'fotos/default.png';
            echo "<div class='kaart' data-naam='{$naam}'>
                <img src='{$foto}' alt='Foto van {$naam}'/>
                <div>
                  <div class='naam'>{$naam}</div>
                  <div class='ingredienten'>Ingrediënten: {$ing}</div>
                </div>
              </div>";
        }
    }
    ?>
</body>

</html>