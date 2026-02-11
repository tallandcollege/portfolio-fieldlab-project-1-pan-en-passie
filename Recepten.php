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
            WHERE Name LIKE :search 
            OR Description LIKE :search
        ";

        $stmt = $pdo->prepare($receptenQuery);
        $stmt->execute([
            ':search' => "%$search%"
        ]);
        //print iedere bijpassende recept
        foreach ($stmt as $rec) {
            echo "<tr>";
            echo "<td>";
            echo "<div class='receptenstuk'>";
            echo "<a href='recept_pagina.php?id=" . $rec['id'] . "'>";
            echo "<h2>" . htmlspecialchars($rec['Name']) . "</h2>";
            echo "<div class='receptendetails'>" . htmlspecialchars($rec['Description']) . "</div>";
            echo "<h6 class='receptentijd'>" . $rec['Createdat'] . "</h6>";
            echo "</a>";
            echo "</div>";
            echo "</td>";
            echo "</tr>";
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
