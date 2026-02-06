<?php
include "includes/connect.php";
$pdo = connect();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include("includes/header.php"); ?>
    <section class="recepten-section">
        <?php
        try{
            echo "<tr>";
            $receptenQuery= "SELECT * FROM recipe"; /*Pakt alle recepten uit de table recipe van de database */
            $recepten = $pdo->query($receptenQuery);
                echo "<table>";
            foreach ($recepten as $rec) {
                echo "<tr>";
                echo "<td class='receptenstuk'>" . "<h2>" . "<a href=recept_pagina.php?id=" . $rec['id'] . ">" . $rec['Name'] . "</a>" . "</h2>" . "</td>";  /*Print alles onder de column "name"*/
                echo "</tr>";
            }
                echo "</table>";
        } catch (PDOException $e) {
            echo "Connection Failed";
        }
        ?>
    </section>
    <?php include("Includes/footer.php"); ?>
</body>
</html>