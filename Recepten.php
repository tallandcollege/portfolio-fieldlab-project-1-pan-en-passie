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
        <?php
        try{
            echo "<tr>";
            $receptenQuery= "SELECT * FROM recipe"; /*Pakt alle recepten uit de table recipe van de database */
            $recepten = $pdo->query($receptenQuery);
            
            foreach ($recepten as $rec) {
                echo $rec['name'];  /*Print alles onder de column "name"*/
            }
        } catch (PDOException $e) {
            echo "Connection Failed";
        }
        ?>
    <?php include("Includes/footer.php"); ?>
</body>
</html>