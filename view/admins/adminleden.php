<?php
session_start();

require_once __DIR__ . '/../../connect.php';
require_once __DIR__ . '/../../app/speler.php';
require_once __DIR__ . '/../../app/trainer.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'club_admin') {
    header("Location: ../dashboard.php");
    exit;
}

$connect = new Connect();
$pdo = $connect->pdo();

$spelerClass  = new Speler($pdo);
$trainerClass = new Trainer($pdo);


if (isset($_GET['delete_trainer'])) {
    $trainerClass->delete((int)$_GET['delete_trainer']);
    header("Location: adminleden.php");
    exit;
}


if (isset($_GET['delete_speler'])) {
    $spelerClass->delete((int)$_GET['delete_speler']);
    header("Location: adminleden.php");
    exit;
}


if (isset($_POST['add_trainer'])) {

    $trainerClass->registerTrainer(
        $_POST['voornaam'],
        $_POST['tussenvoegsels'] ?? '',
        $_POST['achternaam'],
        $_POST['email'],
        $_POST['password'],
        1
    );

    header("Location: adminleden.php");
    exit;
}


if (isset($_POST['add_speler'])) {

    $spelerClass->registerspeler(
        $_POST['voornaam'],
        $_POST['tussenvoegsels'] ?? '',
        $_POST['achternaam'],
        $_POST['email'],
        $_POST['password'],
        $_POST['lidnummer'],
        1
    );

    header("Location: adminleden.php");
    exit;
}


if (isset($_POST['update_stats'])) {

    $stmt = $pdo->prepare("
        UPDATE statistieken
        SET goals = :goals,
            win   = :win,
            draw  = :draw,
            loses = :loses
        WHERE id = :id
    ");

    $stmt->execute([
        'goals' => $_POST['goals'],
        'win'   => $_POST['win'],
        'draw'  => $_POST['draw'],
        'loses' => $_POST['loses'],
        'id'    => $_POST['stat_id']
    ]);

    header("Location: adminleden.php");
    exit;
}



$trainers = $pdo->query("
    SELECT t.id, p.voornaam, p.tussenvoegsels, p.achternaam
    FROM trainer t
    JOIN person p ON t.person_id = p.id
    ORDER BY p.achternaam ASC
")->fetchAll();

$spelers = $pdo->query("
    SELECT s.id,
           p.voornaam,
           p.tussenvoegsels,
           p.achternaam,
           st.id AS stat_id,
           st.goals,
           st.win,
           st.draw,
           st.loses
    FROM speler s
    JOIN person p ON s.person_id = p.id
    JOIN statistieken st ON s.statistieken_id = st.id
    ORDER BY p.achternaam ASC
")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Leden</title>
    <link rel="stylesheet" href="/Goalgetter/view/trainers/style.css">
</head>
<body>

<div class="layout">

    <div class="sidebar">
        <h2>Admin Menu</h2>
        <a href="admindashboard.php">Dashboard</a>
        <a href="adminleden.php">Leden</a>
        <a href="adminwedstrijden.php">Wedstrijden</a>
        <a href="admintrainingen.php" class="active">Trainingen</a>
        <a href="../login.php">Uitloggen</a>
    </div>

    <div class="content">

        <h2>Trainers</h2>
        <button onclick="openTrainerModal()" class="btn-add">+</button>

        <table class="stat-table">
            <tr>
                <th>Naam</th>
                <th>Actie</th>
            </tr>
            <?php foreach ($trainers as $trainer): ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($trainer['voornaam']) ?>
                        <?= htmlspecialchars($trainer['achternaam']) ?>
                    </td>
                    <td>
                        <a href="?delete_trainer=<?= $trainer['id'] ?>" class="btn-delete">🗑</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h2 style="margin-top:40px;">Spelers</h2>
        <button onclick="openSpelerModal()" class="btn-add">+</button>

        <table class="wedstrijd-table">
            <thead>
                <tr>
                    <th>Naam</th>
                    <th>Goals</th>
                    <th>W</th>
                    <th>D</th>
                    <th>L</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($spelers as $speler): ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($speler['voornaam']) ?>
                        <?= htmlspecialchars($speler['achternaam']) ?>
                    </td>
                    <td><?= $speler['goals'] ?></td>
                    <td><?= $speler['win'] ?></td>
                    <td><?= $speler['draw'] ?></td>
                    <td><?= $speler['loses'] ?></td>
                    <td class="actie-buttons">
                        <button type="button" class="btn-edit"
                            onclick="openStatsModal(
                            '<?= $speler['stat_id'] ?>',
                            '<?= $speler['goals'] ?>',
                            '<?= $speler['win'] ?>',
                            '<?= $speler['draw'] ?>',
                            '<?= $speler['loses'] ?>'
                        )">✏️</button>

                        <a href="?delete_speler=<?= $speler['id'] ?>"
                           class="btn-delete"
                           onclick="return confirm('Weet je zeker dat je deze speler wilt verwijderen?');">🗑</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

    </div>
</div>

</body>
</html>