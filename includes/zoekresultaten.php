<?php
// returns only the markup for the search results; intended to be fetched via JavaScript

if (isset($_GET['term'])) {
    require_once __DIR__ . "/connection.php";
    $pdo = connect();

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
    $stmt = $pdo->prepare("SELECT r.id, r.name as naam, GROUP_CONCAT(i.Name SEPARATOR ', ') as ingredienten, p.filename as foto FROM recipe r LEFT JOIN recipeingredient ri ON r.id = ri.`recipe_id` LEFT JOIN ingredient i ON ri.`ingredient_id` = i.id LEFT JOIN photo p ON r.id = p.recipe_id WHERE r.name LIKE :term GROUP BY r.id LIMIT 10");
    $stmt->execute(['term' => $like]);
    $rows = $stmt->fetchAll();

    foreach ($rows as $resultaten) {
        $naam = htmlspecialchars($resultaten['naam'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $ing  = htmlspecialchars($resultaten['ingredienten'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $recipeImage = !empty($resultaten['foto'])
            ? htmlspecialchars('uploads/' . $resultaten['foto'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            : 'images/placeholder.png';

        // links are relative to the page including the search results (header), so no ../ needed
        echo "<a href='recept_pagina.php?id={$resultaten['id']}'>
    <div class='kaart' data-naam='{$naam}'>
      <img src='{$recipeImage}' alt='Foto van {$naam}'/>
      <div>
        <div class='naam'>{$naam}</div>
        <div class='ingredienten'>Ingrediënten: {$ing}</div>
      </div>
    </div>
  </a>";
    }
}
