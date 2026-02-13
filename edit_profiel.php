<?php
include_once(__DIR__ . "/Includes/connection.php");

$userid = $_SESSION['user_id'] ?? 0;
if ($userid == 0) {
    die('Geen ingelogde gebruiker.');
}

// Haal huidige naam en foto op
$stmt = $pdo->prepare("SELECT username, filename FROM users LEFT JOIN photo ON users.id = photo.user_id WHERE users.id = :user_id");
$stmt->execute([':user_id' => $userid]);
$user = $stmt->fetch();
$currentName = $user['username'] ?? '';
$currentPhoto = $user['filename'] ?? '';

// Zorg dat de uploads map bestaat
$targetDir = __DIR__ . '/uploads/';
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';

    // Update naam
    if (!empty($name)) {
        $stmt = $pdo->prepare("UPDATE users SET username = :name WHERE id = :user_id");
        $stmt->execute([
            ':name' => $name,
            ':user_id' => $userid
        ]);
    }

    // Upload foto als er een bestand is gekozen
    if (!empty($_FILES['photo']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5 MB

        $fileType = $_FILES['photo']['type'];
        $fileSize = $_FILES['photo']['size'];

        if (!in_array($fileType, $allowedTypes)) {
            die('Ongeldig bestandstype. Alleen JPG, PNG en GIF toegestaan.');
        }

        if ($fileSize > $maxSize) {
            die('Bestand te groot. Maximaal 5 MB.');
        }

        $filename = uniqid() . '_' . basename($_FILES['photo']['name']);
        $targetFile = $targetDir . $filename;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
            // Verwijder oude foto
            if (!empty($currentPhoto) && file_exists($targetDir . $currentPhoto)) {
                unlink($targetDir . $currentPhoto);
            }

            // Update of insert in photo tabel
            $stmt = $pdo->prepare("SELECT id FROM photo WHERE user_id = :user_id LIMIT 1");
            $stmt->execute([':user_id' => $userid]);
            $existing = $stmt->fetch();

            if ($existing) {
                $stmt = $pdo->prepare("UPDATE photo SET filename = :filename, mime_type = :mime_type WHERE user_id = :user_id");
                $stmt->execute([
                    ':filename' => $filename,
                    ':mime_type' => $fileType,
                    ':user_id' => $userid
                ]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO photo (user_id, filename, mime_type) VALUES (:user_id, :filename, :mime_type)");
                $stmt->execute([
                    ':user_id' => $userid,
                    ':filename' => $filename,
                    ':mime_type' => $fileType
                ]);
            }

            echo "Profielfoto en naam succesvol geüpdatet!";
        } else {
            echo "Er is een fout opgetreden bij het uploaden van de foto.";
        }
    } else {
        echo "Naam succesvol geüpdatet!";
    }

    // Herlaad huidige data na update
    $stmt = $pdo->prepare("SELECT username, filename FROM users LEFT JOIN photo ON users.id = photo.user_id WHERE users.id = :user_id");
    $stmt->execute([':user_id' => $userid]);
    $user = $stmt->fetch();
    $currentName = $user['username'] ?? '';
    $currentPhoto = $user['filename'] ?? '';
}
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profiel Bijwerken</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include(__DIR__ . "/Includes/header.php"); ?>

    <h2>Profiel bijwerken</h2>

    <!-- Huidige profielfoto tonen -->
    <?php if (!empty($currentPhoto) && file_exists('uploads/' . $currentPhoto)): ?>
        <img src="<?= 'uploads/' . htmlspecialchars($currentPhoto) ?>" alt="Profielfoto" style="max-width:150px; display:block; margin-bottom:10px;">
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <label for="name">Naam:</label>
        <input type="text" name="name" id="name" value="<?= htmlspecialchars($currentName) ?>" required>

        <label for="photo">Profielfoto uploaden:</label>
        <input type="file" name="photo" id="photo" accept="image/*">

        <button type="submit">Opslaan</button>
    </form>

    <?php include(__DIR__ . "/Includes/footer.php"); ?>
</body>

</html>
