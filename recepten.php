<?php
include "includes/connection.php";
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
    <?php include "includes/header.php"; ?>

    <section class="recepten-section">

        <?php
        $search = $_GET["search"] ?? ""; // voorkomt undefined error

        try {

            echo "<table>";

            if (!empty($search)) {
                //zoekt naar ingevoerde info in name of description
                $receptenQuery = "
                SELECT recipe.*, users.username
                FROM recipe
                LEFT JOIN users ON recipe.User_id = users.id
                WHERE recipe.Name LIKE :search1
                OR recipe.Description LIKE :search2
                ";

                $stmt = $pdo->prepare($receptenQuery);
                $stmt->execute([
                    ':search1' => "%$search%",
                    ':search2' => "%$search%"
                ]);
                //Checkt of het recept in de database zit
                if ($stmt->rowCount() > 0) {
                    //print iedere bijpassende recept
                    foreach ($stmt as $rec) {
                        echo "<tr>";
                        echo "<td>";
                        echo "<div class='receptenstuk'>";
                        echo "<a href='recept_pagina.php?id=" . $rec['id'] . "'>";
                        echo "<h2>" . htmlspecialchars($rec['Name']) . "</h2>";
                        echo "<div class='receptendetails'>" . htmlspecialchars($rec['Description']) . "</div>";
                        echo "<h5 class='receptentijd'>" . $rec['Createdat'] . " " . $rec['Sterren'] . "</h5>";
                        echo "<h6 class='receptentijd'>" . htmlspecialchars($rec['username']) . "</h6>";
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

    <?php include "includes/footer.php"; ?>
</body>

</html>