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

/* Status wijzigen */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isSpeler && $speler_id) {

    $training_id = $_POST['training_id'] ?? null;
    $status      = $_POST['status'] ?? null;

    if ($training_id && in_array($status, ['aanwezig', 'afwezig'])) {

        $stmt = $pdo->prepare(
            "SELECT id FROM training_aanwezigen
             WHERE speler_id = :speler_id
             AND training_id = :training_id
             LIMIT 1"
        );

        $stmt->execute([
            'speler_id'   => $speler_id,
            'training_id' => $training_id
        ]);

        $exists = $stmt->fetch();

        if ($exists) {
            $stmt = $pdo->prepare(
                "UPDATE training_aanwezigen
                 SET status = :status
                 WHERE speler_id = :speler_id
                 AND training_id = :training_id"
            );
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO training_aanwezigen
                 (speler_id, training_id, status)
                 VALUES (:speler_id, :training_id, :status)"
            );
        }

        $stmt->execute([
            'speler_id'   => $speler_id,
            'training_id' => $training_id,
            'status'      => $status
        ]);
    }
}

/* Tab bepalen */
$tab = $_GET['tab'] ?? 'upcoming';
$today = date('Y-m-d');

if ($tab === 'old') {
    $stmt = $pdo->prepare(
        "SELECT id, date, titel 
         FROM trainingen
         WHERE date < :today
         ORDER BY date DESC"
    );
    $title = "Oude trainingen";
} else {
    $stmt = $pdo->prepare(
        "SELECT id, date, titel 
         FROM trainingen
         WHERE date >= :today
         ORDER BY date ASC"
    );
    $title = "Aankomende trainingen";
}

$stmt->execute(['today' => $today]);
$trainingen = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Trainingen</title>
    <link rel="stylesheet" href="/Goalgetter/view/style.css">
</head>
<body>

<div class="layout">

    <div class="sidebar">
        <h2>Menu</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="wedstrijden.php">Wedstrijden</a>
        <a href="trainingen.php" class="active">Trainingen</a>
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
                <th>Training</th>
                <th>Status</th>
                <?php if ($isSpeler): ?>
                    <th>Actie</th>
                <?php endif; ?>
            </tr>

            <?php foreach ($trainingen as $training): ?>

                <?php
                $status = 'geen status';

                if ($isSpeler && $speler_id) {
                    $stmt = $pdo->prepare(
                        "SELECT status 
                         FROM training_aanwezigen
                         WHERE training_id = :training_id
                         AND speler_id = :speler_id
                         LIMIT 1"
                    );

                    $stmt->execute([
                        'training_id' => $training['id'],
                        'speler_id'   => $speler_id
                    ]);

                    $row = $stmt->fetch();
                    if ($row) {
                        $status = $row['status'];
                    }
                }
                ?>

                <tr>
                    <td><?= htmlspecialchars($training['date']) ?></td>
                    <td><?= htmlspecialchars($training['titel']) ?></td>
                    <td class="status-<?= $status ?>">
                        <?= ucfirst($status) ?>
                    </td>

                    <?php if ($isSpeler): ?>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="training_id" value="<?= $training['id'] ?>">
                                <input type="hidden" name="status" value="aanwezig">
                                <button class="btn-green">Aanwezig</button>
                            </form>

                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="training_id" value="<?= $training['id'] ?>">
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
