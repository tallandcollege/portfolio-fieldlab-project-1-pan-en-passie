<?php
include "includes/connect.php";
$pdo = connect();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zoekresultaten</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
<?php include("includes/header.php"); ?>

<section class="recepten-section">

<?php
$search = $_GET["search"] ?? ""; // voorkomt undefined error

try {

    echo "<table>";

    if (!empty($search)) {
        //zoekt naar ingevoerde info in name of description
        $receptenQuery = "
            SELECT * 
            FROM recipe 
            WHERE name LIKE :search 
            OR description LIKE :search
        ";

        $stmt = $pdo->prepare($receptenQuery);
        $stmt->execute([
            ':search' => "%$search%"
        ]);
        //Checkt of het recept in de database zit
        if($stmt->rowCount() > 0){
        //print iedere bijpassende recept
        foreach ($stmt as $rec) {
            echo "<tr>";
            echo "<td>";
            echo "<div class='receptenstuk'>";
            echo "<a href='recept_pagina.php?id=" . $rec['id'] . "'>";
            echo "<h2>" . htmlspecialchars($rec['name']) . "</h2>";
            echo "<div class='receptendetails'>" . htmlspecialchars($rec['description']) . "</div>";
            echo "<h6 class='receptentijd'>" . $rec['createdat'] . "</h6>";
            echo "</a>";
            echo "</div>";
            echo "</td>";
            echo "</tr>";
        }
        } else {
            echo "Geen bijpassende recept gevonden.";
        }
    } else {
        echo "<tr><td>Geen zoekterm ingevoerd.</td></tr>";
    }

    echo "</table>";

} catch (PDOException $e) {
    echo "Database fout: " . $e->getMessage();
}
?>

</section>

<?php include("includes/footer.php"); ?>
</body>
</html>
