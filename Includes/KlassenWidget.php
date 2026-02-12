<?php
require_once("Includes/connection.php");

$klassen = [];

function truncateText(string $text, int $maxLen): string
{
    if (strlen($text) <= $maxLen) {
        return $text;
    }
    return substr($text, 0, $maxLen - 1) . "…";
}

// ===== DELETE STUDENTS FROM CLASS =====
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['Delete'])
    && isset($_GET['klasid'])
) {
    $klasid = (int)$_GET['klasid'];

    // checkbox values
    $userIds = $_POST['delete_users'] ?? [];

    if (!is_array($userIds)) $userIds = [];

    // alleen geldige ints
    $userIds = array_values(array_filter(array_map('intval', $userIds), fn($v) => $v > 0));

    if (!empty($userIds)) {
        // maak dynamische placeholders voor IN (...)
        $placeholders = [];
        $params = [':klasid' => $klasid];

        foreach ($userIds as $i => $uid) {
            $ph = ":u{$i}";
            $placeholders[] = $ph;
            $params[$ph] = $uid;
        }

        $sql = "
            DELETE FROM Student
            WHERE class_id = :klasid
              AND user_id IN (" . implode(',', $placeholders) . ")
        ";

        // $conn komt uit Includes/connection.php (PDO)
        executeQuery($sql, $params);
    }

    // refresh zodat je direct de nieuwe lijst ziet en resubmit voorkomt
    header("Location: adminpanel.php?klasid=" . $klasid);
    exit;
}

$baseQuery = "
    SELECT
  id,
  classname,
  description,
  maxstudents,
  createdat,
  createrecipeperms
FROM Class
";


$actions = [
    'klas'   => isset($_GET['klasid']),
    'search' => ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['klassearch'])),
];

// bepaal actie
$action = 'default';

if ($actions['klas']) {
    $action = 'klas';
} elseif ($actions['search']) {
    $action = 'search';
}

switch ($action) {
    case 'search':
        $search = trim($_POST['klassearch'] ?? '');

        // als leeg: fall back naar default
        if ($search === '') {
            $query = $baseQuery . " ORDER BY id DESC";
            $klassen = fetchData($query);
            break;
        }

        $query = $baseQuery . "
            WHERE
            CAST(id AS CHAR) LIKE CONCAT('%', :search, '%')
            OR classname     LIKE CONCAT('%', :search, '%')
            ORDER BY id DESC;
        ";

        $klassen = fetchData($query, [':search' => "%{$search}%"]);
        break;

    case 'klas':

        $klasid = $_GET['klasid'];

        $query = "SELECT
  s.user_id,
  u.firstname,
  u.lastname,
  u.username,
  u.email,
  c.id   AS class_id,
  c.classname,
  r.name AS role_name
FROM Student s
JOIN Users u ON u.id = s.user_id
JOIN Class c ON c.id = s.class_id
JOIN Role  r ON r.id = s.role_id
WHERE s.class_id = :klasid          
ORDER BY u.lastname, u.firstname;";

        $klassen = fetchData($query, [':klasid' => $klasid]);
        break;



    default:
        $query = $baseQuery . " ORDER BY id DESC";
        $klassen = fetchData($query);
        break;
}
?>

<article class="admin-panellist">
    <h2>Klassen</h2>

    <div class="admin-article-content">


        <form method="POST" class="admin-search-form">
            <input
                type="text"
                class="Widget-Search"
                name="klassearch"
                placeholder="Zoek klas (id of naam)"
                value="<?= htmlspecialchars($_POST['klassearch'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
            <button type="submit" style="display:none;">Zoek</button>
        </form>


        <div class="admin-article-listcontent">
            <?php if ($action === 'klas'): ?>

                <?php
                // In 'klas'-mode bevat $klassen student-rijen.
                $students = $klassen;

                // Probeer klasnaam uit eerste rij te halen; fallback: losse class-info ophalen als er 0 students zijn.
                $classInfo = null;
                if (!empty($students)) {
                    $classInfo = [
                        'id' => $students[0]['class_id'] ?? ($_GET['klasid'] ?? ''),
                        'classname' => $students[0]['classname'] ?? ('Klas #' . ($_GET['klasid'] ?? '')),
                    ];
                } else {
                    $q = $baseQuery . " WHERE id = :klasid LIMIT 1";
                    $tmp = fetchData($q, [':klasid' => $_GET['klasid']]);
                    if (!empty($tmp)) $classInfo = $tmp[0];
                    else $classInfo = ['id' => $_GET['klasid'], 'classname' => 'Onbekende klas'];
                }
                ?>

                <!-- 3) DETAIL: 1 KLAS + USERS -->
                <div class="class-detail">
                    <h3><?= htmlspecialchars($classInfo['classname'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="muted">Klas-ID: <?= (int)$classInfo['id'] ?></p>

                    <?php if (empty($students)): ?>
                        <div class="User-Search-item">
                            <p>Geen studenten gevonden in deze klas.</p>
                        </div>
                        <div class="AddStudent-btn-container">
                                <a class="AddStudent-btn" href="adminpanel.php">Terug</a>
                            </div>
                    <?php else: ?>
                        <form method="POST">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Verwijder</th>
                                        <th>Naam</th>
                                        <th>Gebruikersnaam</th>
                                        <th>E-mail</th>
                                        <th>Rol</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $st): ?>
                                        <tr>
                                            <td>
                                                <!-- Als je delete per student wil: geef user_id mee -->
                                                <input type="checkbox" name="delete_users[]" value="<?= (int)$st['user_id'] ?>">
                                            </td>
                                            <td><?= htmlspecialchars(truncateText(($st['lastname'] ?? '') . ', ' . ($st['firstname'] ?? ''), 50), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($st['username'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($st['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($st['role_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <div class="AddStudent-btn-container">
                                <a class="AddStudent-btn" href="adminpanel.php">Terug</a>
                                <input class="AddStudent-btn" type="submit" value="Student verwijderen" name="Delete">
                            </div>
                        </form>
                    <?php endif; ?>
                </div>


            <?php else: ?>

                <!-- 1) DEFAULT: nieuwste klassen (ORDER BY id DESC)
             2) SEARCH: resultaten obv zoekterm -->
                <?php if (empty($klassen)): ?>
                    <div class="User-Search-item">
                        <p>Geen klassen gevonden!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($klassen as $klas): ?>
                        <a class="admin-list-item" href="adminpanel.php?klasid=<?= (int)$klas['id'] ?>">
                            <div class="admin-list-item-main">
                                <h3><?= htmlspecialchars(truncateText($klas['classname'] ?? '', 20), ENT_QUOTES, 'UTF-8') ?></h3>
                            </div>

                            <div class="admin-list-item-meta">
                                <span>Max: <?= (int)($klas['maxstudents'] ?? 0) ?></span>
                                <span>ID: <?= (int)($klas['id'] ?? 0) ?></span>
                                <?php if (!empty($klas['createdat'])): ?>
                                    <span><?= htmlspecialchars($klas['createdat'], ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>

            <?php endif; ?>
        </div>

    </div>

    <?php if (!isset($_GET['klasid'])): ?>
        <a class="admin-btn" href="klasToevoegen.php">Maak een klas</a>
    <?php endif; ?>
</article>
