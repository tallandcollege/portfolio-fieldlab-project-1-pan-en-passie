<?php
include_once(__DIR__ . "/connection.php");


$users = [];
$selectedIds = [];

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

// Selected IDs from client (JSON array or comma-separated)
if (isset($_POST['selected_ids'])) {
  $rawSelected = $_POST['selected_ids'];
  if (is_string($rawSelected)) {
    $decoded = json_decode($rawSelected, true);
    if (is_array($decoded)) {
      $selectedIds = $decoded;
    } else {
      $selectedIds = array_filter(array_map('trim', explode(',', $rawSelected)));
    }
  } elseif (is_array($rawSelected)) {
    $selectedIds = $rawSelected;
  }
}

$selectedIds = array_values(array_unique(array_filter(array_map('intval', $selectedIds))));

if (!empty($selectedIds)) {
  $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
  $orderBy = implode(',', array_fill(0, count($selectedIds), '?'));

  $selectedQuery = "
        SELECT u.id, u.firstname, u.lastname, u.username, u.email, r.name AS role
        FROM users u
        JOIN role r ON u.role_id = r.id
        WHERE u.id IN ($placeholders)
        ORDER BY FIELD(u.id, $orderBy)
    ";

  $selectedUsers = fetchData($selectedQuery, array_merge($selectedIds, $selectedIds));

  if (!empty($users)) {
    $selectedLookup = array_flip($selectedIds);
    $users = array_values(array_filter($users, function ($u) use ($selectedLookup) {
      return !isset($selectedLookup[(int)($u['id'] ?? 0)]);
    }));
  }

  $users = array_merge($selectedUsers, $users);
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

  $query = "SELECT * FROM Class WHERE id = :classId LIMIT 1";
  $class = fetchData($query, [':classId' => (int)$classId], true);
  if (empty($class)) {
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
    exit;
  }

  $query = "SELECT COUNT(*) AS current_count FROM Student WHERE class_id = :classId";
  $countResult = fetchData($query, [':classId' => (int)$classId], true);
  $currentStudentAmmount = (int)($countResult['current_count'] ?? 0);

  $userIds = array_values(array_unique(array_map('intval', $userIds)));
  $classId = (int)$classId;
  $_SESSION['students_not_added'] = [];

  // duplicates overslaan
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
    // Maximum ammount of students able to be added to a class.
    $maxstudents = (int)$class['maxstudents'];

    // Loop through user IDs and add them to the class until the max is reached
    foreach ($userIds as $userId) {

      if ($userId <= 0) {
        continue;
      }

      if ($currentStudentAmmount >= $maxstudents) {
        $_SESSION['students_not_added'][] = $userId;
        continue;
      }

      // add user to class
      $rowsInserted = executeQuery($query, [$userId, $classId]);
      // update current amount only if a new row was inserted (not a duplicate)
      if ($rowsInserted > 0) {
        $currentStudentAmmount++;
      }
    }

    if (!empty($_SESSION['students_not_added'])) {
      $placeholders = implode(',', array_fill(0, count($_SESSION['students_not_added']), '?'));
      $userQuery = "
            SELECT id, firstname, lastname, username, email
            FROM users
            WHERE id IN ($placeholders)
        ";
      $notAddedUsers = fetchData($userQuery, $_SESSION['students_not_added']);
      $_SESSION['students_not_added'] = $notAddedUsers;
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
    <form method="POST" id="account-zoek-search-form">
      <input type="text" class="Widget-Search" name="search" placeholder="Zoek gebruiker...">
      <input type="hidden" name="selected_ids" id="account-zoek-selected-ids" value="">
    </form>

    <!-- Selecteer + toevoegen (POST) -->
    <form method="POST" class="admin-article-form">
      <div class="admin-article-listcontent">
        <div class="User-Search-pinned" id="account-zoek-pinned" style="display:none; margin-bottom:12px;"></div>
        <div id="account-zoek-list">

        <?php if (!empty($users)): foreach ($users as $user): ?>
            <div class="User-Search-item" data-user-id="<?= htmlspecialchars($user['id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
              <input
                type="checkbox"
                name="user_ids[]"
                value="<?= htmlspecialchars($user['id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
              <p><?= htmlspecialchars($user['role'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
              <p><?= htmlspecialchars(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
              <p>Gebruikersnaam: <?= htmlspecialchars($user['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
              <p>Email: <?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
          <?php endforeach;
        else: ?>
          <div class="User-Search-item">
            <p>Geen gebruikers gevonden!</p>
          </div>
        <?php endif; ?>

        </div>
      </div>

      <?php if (isset($_GET['klasid'])): ?>
        <input type="hidden" name="klasid" value="<?= htmlspecialchars($_GET['klasid'], ENT_QUOTES, 'UTF-8'); ?>">
        <input class="AddStudent-btn" type="submit" value="Gebruiker toevoegen" name="addStudent">
      <?php endif; ?>
    </form>

  </div>

  <a class="admin-btn" href="chefToevoegen.php">Registreer een gebruiker!</a>
</article>

<script>
  (function () {
    var list = document.getElementById('account-zoek-list');
    var pinned = document.getElementById('account-zoek-pinned');
    var hiddenInput = document.getElementById('account-zoek-selected-ids');
    var searchForm = document.getElementById('account-zoek-search-form');
    if (!list || !pinned || !hiddenInput || !searchForm) return;

    var storageKey = 'account_zoek_selected_ids';
    var selectedIds = new Set();

    function loadSelectedIds() {
      try {
        var raw = sessionStorage.getItem(storageKey);
        if (!raw) return;
        var parsed = JSON.parse(raw);
        if (Array.isArray(parsed)) {
          parsed.forEach(function (id) { selectedIds.add(String(id)); });
        }
      } catch (e) {
        selectedIds.clear();
      }
    }

    function saveSelectedIds() {
      try {
        sessionStorage.setItem(storageKey, JSON.stringify(Array.from(selectedIds)));
      } catch (e) {
        // ignore storage errors
      }
    }

    function syncHiddenInput() {
      hiddenInput.value = JSON.stringify(Array.from(selectedIds));
    }

    function syncCheckedStates() {
      var items = Array.prototype.slice.call(document.querySelectorAll('#account-zoek-list .User-Search-item, #account-zoek-pinned .User-Search-item'));
      items.forEach(function (item) {
        var id = item.getAttribute('data-user-id');
        if (!id) return;
        var checkbox = item.querySelector('input[type="checkbox"][name="user_ids[]"]');
        if (!checkbox) return;
        checkbox.checked = selectedIds.has(id);
      });
    }

    function applyPinnedSplit() {
      var items = Array.prototype.slice.call(document.querySelectorAll('#account-zoek-list .User-Search-item, #account-zoek-pinned .User-Search-item'));
      var selected = [];
      var notSelected = [];

      items.forEach(function (item) {
        var id = item.getAttribute('data-user-id');
        if (id && selectedIds.has(id)) {
          selected.push(item);
        } else {
          notSelected.push(item);
        }
      });

      selected.forEach(function (item) { pinned.appendChild(item); });
      notSelected.forEach(function (item) { list.appendChild(item); });

      var hasPinned = selected.length > 0;
      pinned.style.display = hasPinned ? 'block' : 'none';
    }

    function onCheckboxChange(event) {
      var target = event.target;
      if (!target || target.type !== 'checkbox' || target.name !== 'user_ids[]') return;

      var item = target.closest('.User-Search-item');
      if (!item) return;

      var id = item.getAttribute('data-user-id');
      if (!id) return;

      if (target.checked) {
        selectedIds.add(id);
      } else {
        selectedIds.delete(id);
      }

      saveSelectedIds();
      syncHiddenInput();
      applyPinnedSplit();
    }

    loadSelectedIds();
    syncHiddenInput();  
    syncCheckedStates();
    applyPinnedSplit();

    list.addEventListener('change', onCheckboxChange);
    pinned.addEventListener('change', onCheckboxChange);
    searchForm.addEventListener('submit', syncHiddenInput);
  })();
</script>
