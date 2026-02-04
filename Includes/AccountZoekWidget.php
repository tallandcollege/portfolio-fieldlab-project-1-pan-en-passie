<?php

if (!isset($_SESSION)) {
    session_start();
}

include("Includes/connection.php");

$users = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $search = trim($_POST['search']);

    if ($search !== '') {
        $searchTerm = "%{$search}%";

        $stmt = $pdo->prepare("
            SELECT u.id, u.firstname, u.lastname, u.username, u.email, r.name AS role
            FROM users u
            JOIN role r ON u.role_id = r.id
            WHERE u.firstname LIKE :search
               OR u.lastname  LIKE :search
               OR u.username  LIKE :search
               OR u.email     LIKE :search
            ORDER BY u.id DESC
        ");
        $stmt->execute([':search' => $searchTerm]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmt = $pdo->prepare("
        SELECT u.id, u.firstname, u.lastname, u.username, u.email, r.name AS role
        FROM users u
        JOIN role r ON u.role_id = r.id
        ORDER BY u.id DESC
        LIMIT 10
    ");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} else {
    $stmt = $pdo->prepare("
        SELECT u.id, u.firstname, u.lastname, u.username, u.email, r.name AS role
        FROM users u
        JOIN role r ON u.role_id = r.id
        ORDER BY u.id DESC
        LIMIT 10
    ");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<article class="admin-panellist">
    <form method="POST">
        <input type="text" class="Zoek-Widget-Search" name="search" placeholder="Zoek gebruiker...">
    </form>
    <div class="admin-panellist-content">
        <form method="GET">

            <?php if (!empty($users)):
                foreach ($users as $user): ?>
                    <div class="User-Search-item">
                        <input
                            type="checkbox"
                            name="user_ids[]"
                            value="<?php echo htmlspecialchars($user['id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <p><?php echo htmlspecialchars($user['role'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><?php echo htmlspecialchars(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p>Gebruikersnaam: <?php echo htmlspecialchars($user['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                        <p>Email: <?php echo htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                <?php endforeach;
            else: ?>
                <div class="User-Search-item">
                    <p>Geen gebruikers gevonden!</p>
                </div>
            <?php endif; ?>

        </form>
    </div>
    <a class="admin-btn" href="chefToevoegen.php">Registreer een gebruiker!</a>
</article>