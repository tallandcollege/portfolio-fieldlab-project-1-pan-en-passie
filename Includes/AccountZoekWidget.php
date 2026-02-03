<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("Includes/connection.php");
// include("Includes/functions.php"); // waar executeQuery() staat, indien apart

$users = [];

/* ---------- ZOEKEN (POST) ---------- */
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

/* ---------- STUDENT TOEVOEGEN (POST) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addStudent'])) {

    $userIds = $_POST['user_ids'] ?? [];
    $classId = $_POST['klasid'] ?? ($_GET['klasid'] ?? null);

    // Niks aangevinkt of geen klas -> stil terug
    if (empty($userIds) || empty($classId)) {
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit;
    }

    $userIds = array_values(array_unique(array_map('intval', $userIds)));
    $classId = (int)$classId;

    // Stil duplicates overslaan (MySQL/MariaDB)
    $query = "
        INSERT IGNORE INTO Student (user_id, class_id, role_id)
        VALUES (
          ?,
          ?,
          (SELECT id FROM Role WHERE name = 'student' LIMIT 1)
        )
    ";

    $pdo->beginTransaction();
    try {
        foreach ($userIds as $userId) {
            if ($userId <= 0) continue;
            executeQuery($query, [$userId, $classId]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        // geen melding, gewoon terug
    }

    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
    exit;
}
?>

<article class="admin-panellist">
  <h2>Gebruikers</h2>

  <div class="admin-article-content">

    <!-- Zoek form (POST) -->
    <form method="POST">
      <input type="text" class="Widget-Search" name="search" placeholder="Zoek gebruiker...">
    </form>

    <!-- Selecteer + toevoegen (POST) -->
    <form method="POST" class="admin-article-form">
      <div class="admin-article-listcontent">

        <?php if (!empty($users)): foreach ($users as $user): ?>
          <div class="User-Search-item">
            <input
              type="checkbox"
              name="user_ids[]"
              value="<?= htmlspecialchars($user['id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <p><?= htmlspecialchars($user['role'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
            <p><?= htmlspecialchars(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
            <p>Gebruikersnaam: <?= htmlspecialchars($user['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
            <p>Email: <?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
          </div>
        <?php endforeach; else: ?>
          <div class="User-Search-item">
            <p>Geen gebruikers gevonden!</p>
          </div>
        <?php endif; ?>

      </div>

      <?php if (isset($_GET['klasid'])): ?>
        <input type="hidden" name="klasid" value="<?= htmlspecialchars($_GET['klasid'], ENT_QUOTES, 'UTF-8'); ?>">
        <input class="AddStudent-btn" type="submit" value="Gebruiker toevoegen" name="addStudent">
      <?php endif; ?>
    </form>

  </div>

  <a class="admin-btn" href="chefToevoegen.php">Registreer een gebruiker!</a>
</article>
