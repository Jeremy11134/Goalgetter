<?php
session_start();

require_once __DIR__ . '/../../connect.php';
require_once __DIR__ . '/../../app/user.php';

$connect = new Connect();
$pdo = $connect->pdo();


if (!isset($_SESSION['user_id'])) {
    header("Location: ./view/login.php");
    exit;
}

/* 🔵 Aankomende training */
$stmt = $pdo->query("
    SELECT * FROM trainingen
    WHERE date >= CURDATE()
    ORDER BY date ASC, start ASC
    LIMIT 1
");
$training = $stmt->fetch();

/* 🔴 Aankomende wedstrijd */
$stmt = $pdo->query("
    SELECT * FROM wedstrijden
    WHERE date >= CURDATE()
    ORDER BY date ASC, start ASC
    LIMIT 1
");
$wedstrijd = $stmt->fetch();

/* 🟢 Top 5 spelers */
$stmt = $pdo->query("
    SELECT 
        p.voornaam,
        p.tussenvoegsels,
        p.achternaam,
        st.goals,
        st.win,
        st.draw,
        st.loses
    FROM speler s
    JOIN person p ON s.person_id = p.id
    JOIN statistieken st ON s.statistieken_id = st.id
    ORDER BY st.goals DESC
    LIMIT 5
");
$topSpelers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="/Goalgetter/view/style.css">
</head>
<body>

<div class="layout">

    <!-- 🔵 Sidebar -->
    <div class="sidebar">
        <h2>GoalGetter</h2>

        <a href="trainerdashboard.php">Dashboard</a>
        <a href="trainerwedstrijden.php">Wedstrijden</a>
        <a href="trainertrainingen.php">Trainingen</a>
        <a href="trainerleden.php">Leden</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- 🟢 Content -->
    <div class="content">

        <h1>Dashboard</h1>

        <div class="cards">

            <div class="card">
                <h3>Aankomende Training</h3>
                <?php if ($training): ?>
                    <p><strong><?= $training['titel'] ?></strong></p>
                    <p><?= $training['date'] ?></p>
                    <p><?= $training['start'] ?> - <?= $training['end'] ?></p>
                <?php else: ?>
                    <p>Geen training gepland.</p>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3>Aankomende Wedstrijd</h3>
                <?php if ($wedstrijd): ?>
                    <p><strong><?= $wedstrijd['titel'] ?></strong></p>
                    <p><?= $wedstrijd['date'] ?></p>
                    <p><?= $wedstrijd['start'] ?> - <?= $wedstrijd['end'] ?></p>
                <?php else: ?>
                    <p>Geen wedstrijd gepland.</p>
                <?php endif; ?>
            </div>

        </div>

        <h2>Top 5 Spelers</h2>

        <table>
            <tr>
                <th>Naam</th>
                <th>Goals</th>
                <th>W</th>
                <th>D</th>
                <th>L</th>
            </tr>

            <?php foreach ($topSpelers as $speler): ?>
                <tr>
                    <td>
                        <?= $speler['voornaam'] ?>
                        <?= $speler['tussenvoegsels'] ?>
                        <?= $speler['achternaam'] ?>
                    </td>
                    <td><?= $speler['goals'] ?></td>
                    <td><?= $speler['win'] ?></td>
                    <td><?= $speler['draw'] ?></td>
                    <td><?= $speler['loses'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

    </div>
</div>

</body>
</html>
</html>
