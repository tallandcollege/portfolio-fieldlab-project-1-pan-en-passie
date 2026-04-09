<?php
require_once("connection.php");

// Container voor data (kan klassen OF studenten bevatten afhankelijk van view)
$klassen = [];

/**
 * Kort lange tekst af voor UI
 */
function truncateText(string $text, int $maxLen): string
{
    if (strlen($text) <= $maxLen) return $text;
    return substr($text, 0, $maxLen - 1) . "…";
}


/* ===============================
   DELETE STUDENTS FROM CLASS
   =============================== */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['Delete'])
    && isset($_GET['klasid'])
) {
    // Huidige klas
    $klasid = (int)$_GET['klasid'];

    // Checkbox values (user_id's)
    $userIds = $_POST['delete_users'] ?? [];

    // Safety
    if (!is_array($userIds)) $userIds = [];

    // Filter: alleen geldige ints > 0
    $userIds = array_values(
        array_filter(
            array_map('intval', $userIds),
            fn($v) => $v > 0
        )
    );

    if (!empty($userIds)) {
        // Dynamische IN (...) placeholders maken
        $placeholders = [];
        $params = [':klasid' => $klasid];

        foreach ($userIds as $i => $uid) {
            $ph = ":u{$i}";
            $placeholders[] = $ph;
            $params[$ph] = $uid;
        }

        // Verwijdert student uit klas (niet uit Users!)
        $sql = "
            DELETE FROM Student
            WHERE class_id = :klasid
              AND user_id IN (" . implode(',', $placeholders) . ")
        ";

        executeQuery($sql, $params);
    }

    // Refresh (voorkomt form resubmit)
    header("Location: ?klasid=" . $klasid);
    exit;
}


/* ===============================
   BASE QUERY (alle klassen)
   =============================== */

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


/* ===============================
   BEPAAL VIEW / ACTIE
   =============================== */

$actions = [
    'klas'   => isset($_GET['klasid']), // detail view
    'search' => ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['klassearch'])),
];

$action = 'default';

if ($actions['klas']) {
    $action = 'klas';
} elseif ($actions['search']) {
    $action = 'search';
}


/* ===============================
   DATA OPHALEN
   =============================== */

switch ($action) {

    case 'search':
        $search = trim($_POST['klassearch'] ?? '');

        // Geen zoekterm → default
        if ($search === '') {
            $klassen = fetchData($baseQuery . " ORDER BY id DESC");
            break;
        }

        // Zoek op id of classname
        $query = $baseQuery . "
            WHERE
                CAST(id AS CHAR) LIKE :search
                OR classname LIKE :search
            ORDER BY id DESC
        ";

        $klassen = fetchData($query, [':search' => "%{$search}%"]);
        break;


    case 'klas':
        // Detail: studenten in 1 klas
        $klasid = $_GET['klasid'];

        $query = "
            SELECT
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
            ORDER BY u.lastname, u.firstname
        ";

        $klassen = fetchData($query, [':klasid' => $klasid]);
        break;


    default:
        // Default: alle klassen
        $klassen = fetchData($baseQuery . " ORDER BY id DESC");
        break;
}
?>

<article class="admin-panellist">
    <h2>Klassen</h2>

    <div class="admin-article-content">

        <!-- Zoekveld -->
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
                // In deze view = studentenlijst
                $students = $klassen;

                // Haal klas info uit eerste student of fallback query
                $classInfo = null;

                if (!empty($students)) {
                    $classInfo = [
                        'id' => $students[0]['class_id'],
                        'classname' => $students[0]['classname']
                    ];
                } else {
                    // Als geen studenten → klas alsnog ophalen
                    $tmp = fetchData($baseQuery . " WHERE id = :klasid LIMIT 1", [
                        ':klasid' => $_GET['klasid']
                    ]);

                    $classInfo = $tmp[0] ?? [
                        'id' => $_GET['klasid'],
                        'classname' => 'Onbekende klas'
                    ];
                }
                ?>

                <!-- DETAIL VIEW -->
                <div class="class-detail">
                    <h3><?= htmlspecialchars($classInfo['classname']) ?></h3>
                    <p class="muted">Klas-ID: <?= (int)$classInfo['id'] ?></p>

                    <?php if (empty($students)): ?>
                        <div class="User-Search-item">
                            <p>Geen studenten gevonden in deze klas.</p>
                        </div>

                        <div class="AddStudent-btn-container">
                            <a class="AddStudent-btn" href="?">Terug</a>
                        </div>

                    <?php else: ?>

                        <!-- Delete form -->
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
                                                <input type="checkbox" name="delete_users[]" value="<?= (int)$st['user_id'] ?>">
                                            </td>
                                            <td><?= htmlspecialchars(truncateText($st['lastname'] . ', ' . $st['firstname'], 50)) ?></td>
                                            <td><?= htmlspecialchars($st['username']) ?></td>
                                            <td><?= htmlspecialchars($st['email']) ?></td>
                                            <td><?= htmlspecialchars($st['role_name']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <div class="AddStudent-btn-container">
                                <a class="AddStudent-btn" href="?">Terug</a>
                                <input class="AddStudent-btn" type="submit" value="Student verwijderen" name="Delete">
                            </div>
                        </form>

                    <?php endif; ?>
                </div>

            <?php else: ?>

                <!-- LIST VIEW (default + search) -->
                <?php if (empty($klassen)): ?>
                    <div class="User-Search-item">
                        <p>Geen klassen gevonden!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($klassen as $klas): ?>
                        <a class="admin-list-item" href="?klasid=<?= (int)$klas['id'] ?>">
                            <div class="admin-list-item-main">
                                <h3><?= htmlspecialchars(truncateText($klas['classname'], 20)) ?></h3>
                            </div>

                            <div class="admin-list-item-meta">
                                <span>Max: <?= (int)$klas['maxstudents'] ?></span>
                                <span>ID: <?= (int)$klas['id'] ?></span>
                                <?php if (!empty($klas['createdat'])): ?>
                                    <span><?= htmlspecialchars($klas['createdat']) ?></span>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>

            <?php endif; ?>
        </div>

    </div>

    <!-- Alleen tonen in overzicht -->
    <?php if (!isset($_GET['klasid'])): ?>
        <a class="admin-btn" href="klasToevoegen.php">Maak een klas</a>
    <?php endif; ?>
</article>