<?php
session_start();
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../app/speler.php';

$connect = new Connect();
$pdo = $connect->pdo();

$isSpeler = isset($_SESSION['role']) && $_SESSION['role'] === 'speler';
$speler_id = null;

if ($isSpeler) {
    $stmt = $pdo->prepare(
        "SELECT id FROM speler WHERE user_id = :user_id LIMIT 1"
    );
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $speler = $stmt->fetch();
    $speler_id = $speler['id'] ?? null;
}

/* Tab bepalen */
$tab = $_GET['tab'] ?? 'upcoming';
$today = date('Y-m-d');

/* Wedstrijden ophalen */
if ($tab === 'old') {
    $stmt = $pdo->prepare(
        "SELECT id, date, titel 
         FROM wedstrijden
         WHERE date < :today
         ORDER BY date DESC"
    );
    $title = "Oude wedstrijden";
} else {
    $stmt = $pdo->prepare(
        "SELECT id, date, titel 
         FROM wedstrijden
         WHERE date >= :today
         ORDER BY date ASC"
    );
    $title = "Aankomende wedstrijden";
}

$stmt->execute(['today' => $today]);
$wedstrijden = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Wedstrijden</title>
    <link rel="stylesheet" href="/Goalgetter/view/style.css">
</head>
<body>

<div class="layout">

    <div class="sidebar">
        <h2>Menu</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="wedstrijden.php" class="active">Wedstrijden</a>
        <a href="trainingen.php">Trainingen</a>
        <a href="leden.php">Leden</a>
    </div>

    <div class="content">

        <!-- Tabs -->
        <div class="tabs">
            <a href="?tab=upcoming" class="<?= $tab === 'upcoming' ? 'active' : '' ?>">
                Aankomende
            </a>

            <a href="?tab=old" class="<?= $tab === 'old' ? 'active' : '' ?>">
                Oude
            </a>
        </div>

        <h2><?= $title ?></h2>

        <table>
            <tr>
                <th>Datum</th>
                <th>Wedstrijd</th>
                <th>Status</th>
                <?php if ($isSpeler): ?>
                    <th>Actie</th>
                <?php endif; ?>
            </tr>

            <?php foreach ($wedstrijden as $wedstrijd): ?>

                <?php
                $status = 'geen status';

                if ($isSpeler && $speler_id) {
                    $stmt = $pdo->prepare(
                        "SELECT status 
                         FROM wedstrijd_aanwezigen
                         WHERE wedstrijd_id = :wedstrijd_id
                         AND speler_id = :speler_id
                         LIMIT 1"
                    );

                    $stmt->execute([
                        'wedstrijd_id' => $wedstrijd['id'],
                        'speler_id'    => $speler_id
                    ]);

                    $row = $stmt->fetch();
                    if ($row) {
                        $status = $row['status'];
                    }
                }
                ?>

                <tr>
                    <td><?= htmlspecialchars($wedstrijd['date']) ?></td>
                    <td><?= htmlspecialchars($wedstrijd['titel']) ?></td>
                    <td class="status-<?= $status ?>">
                        <?= ucfirst($status) ?>
                    </td>

                    <?php if ($isSpeler): ?>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="wedstrijd_id" value="<?= $wedstrijd['id'] ?>">
                                <input type="hidden" name="status" value="aanwezig">
                                <button class="btn-green">Aanwezig</button>
                            </form>

                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="wedstrijd_id" value="<?= $wedstrijd['id'] ?>">
                                <input type="hidden" name="status" value="afwezig">
                                <button class="btn-red">Afwezig</button>
                            </form>
                        </td>
                    <?php endif; ?>

                </tr>
            <?php endforeach; ?>
        </table>

    </div>
</div>

</body>
</html>
