<?php

include_once("includes/connection.php");

// Get user ID from URL or from session
if (isset($_GET['id'])) {
    $userid = (int)$_GET['id'];
} elseif (isset($_SESSION['user_id'])) {
    // $userid = $_SESSION['user_id'];
} else {
    echo "geen geldig id gekregen";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $userid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$photoStmt = $pdo->prepare("
    SELECT filename FROM photo
    WHERE user_id = :user_id
    ORDER BY uploaded_at DESC
    LIMIT 1
");
$photoStmt->execute([':user_id' => $userid]);
$photo = $photoStmt->fetch(PDO::FETCH_ASSOC);

$profileImage = $photo ? 'uploads/' . $photo['filename'] : null;



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
    <main class="profile-main">
        <h1>Profielpagina</h1>
        <p>Welkom,
            <?php echo htmlspecialchars($_SESSION['name']); ?>!
        </p>

        <!-- Profielfoto weergeven -->
        <?php if ($profileImage): ?>
            <img src="<?= htmlspecialchars($profileImage) ?>"
                alt="Profielfoto van <?= htmlspecialchars($user['firstname']) ?>"
                class="profile-image">
        <?php else: ?>
            <p>Geen profielfoto beschikbaar</p>
        <?php endif; ?>

        <a href="edit_profiel.php">edit</a>


        <a href="logout.php">Logout</a>
    </main>
    <?php include("includes/footer.php"); ?>

</body>

</html>