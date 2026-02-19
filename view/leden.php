<?php
session_start();

require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../app/speler.php';

$connect = new Connect();
$pdo = $connect->pdo();

$spelerClass = new Speler($pdo);

/* Spelers ophalen */
$stmt = $pdo->query("
    SELECT 
        s.id,
        p.voornaam,
        p.tussenvoegsels,
        p.achternaam,
        st.goals
    FROM speler s
    JOIN person p ON s.person_id = p.id
    JOIN statistieken st ON s.statistieken_id = st.id
    ORDER BY st.goals DESC
");

$spelers = $stmt->fetchAll();

/* Trainers ophalen */
$stmt = $pdo->query("
    SELECT p.voornaam, p.tussenvoegsels, p.achternaam
    FROM trainer t
    JOIN person p ON t.person_id = p.id
    ORDER BY p.achternaam ASC
");
$trainers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Leden</title>
    <link rel="stylesheet" href="/Goalgetter/view/style.css">
</head>
<body>

<div class="layout">

    <div class="sidebar">
        <h2>Menu</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="wedstrijden.php">Wedstrijden</a>
        <a href="trainingen.php">Trainingen</a>
        <a href="leden.php" class="active">Leden</a>
    </div>

    <div class="content">

<table class="leden-table">
    <tr>
        <th>Naam</th>
        <th>Totaal Goals</th>
        <th>Gemiddelde</th>
    </tr>

    <?php foreach ($spelers as $speler): ?>

        <?php
        $gemiddelde = $spelerClass->getGemiddeldeGoals($speler['id']);
        ?>

        <tr>
            <td>
                <?= trim(
                    htmlspecialchars($speler['voornaam'] . ' ' .
                    $speler['tussenvoegsels'] . ' ' .
                    $speler['achternaam'])
                ) ?>
            </td>

            <td>
                 <?= $speler['goals'] ?>
            </td>

            <td>
                 <?= number_format($gemiddelde, 2) ?>
            </td>
        </tr>

    <?php endforeach; ?>
</table>

        <h2>Trainers</h2>

        <ul class="leden-lijst">
            <?php foreach ($trainers as $trainer): ?>
                <li>
                    <?= htmlspecialchars($trainer['voornaam']) ?>
                    <?= htmlspecialchars($trainer['tussenvoegsels']) ?>
                    <?= htmlspecialchars($trainer['achternaam']) ?>
                </li>
            <?php endforeach; ?>
        </ul>

    </div>

</div>

</body>
</html>
