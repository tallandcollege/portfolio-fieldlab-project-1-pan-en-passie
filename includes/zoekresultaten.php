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
    $stmt = $pdo->prepare("
        SELECT 
            r.id,
            r.name AS naam,
            GROUP_CONCAT(i.Name SEPARATOR ', ') AS ingredienten,
            p.image AS foto_blob,
            p.mime_type,
            p.filename AS foto_bestand
        FROM recipe r
        LEFT JOIN recipeingredient ri ON r.id = ri.recipe_id
        LEFT JOIN ingredient i ON ri.ingredient_id = i.id
        LEFT JOIN photo p ON p.id = (
            SELECT p2.id
            FROM photo p2
            WHERE p2.recipe_id = r.id
            ORDER BY p2.uploaded_at DESC, p2.id DESC
            LIMIT 1
        )
        WHERE r.name LIKE :term
        GROUP BY r.id, r.name, p.image, p.mime_type, p.filename
        LIMIT 10
    ");
    $stmt->execute(['term' => $like]);
    $rows = $stmt->fetchAll();

    foreach ($rows as $resultaten) {
        $naam = htmlspecialchars($resultaten['naam'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $ing  = htmlspecialchars($resultaten['ingredienten'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        if (!empty($resultaten['foto_blob'])) {
            $recipeImage = 'data:' . ($resultaten['mime_type'] ?? 'image/jpeg') . ';base64,' . base64_encode($resultaten['foto_blob']);
        } elseif (!empty($resultaten['foto_bestand'])) {
            $recipeImage = 'uploads/' . rawurlencode($resultaten['foto_bestand']);
        } else {
            $recipeImage = 'images/placeholder.png';
        }

        $recipeImage = htmlspecialchars($recipeImage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

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
