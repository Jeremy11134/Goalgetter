<?php
session_start();

require_once __DIR__ . '/../../connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'club_admin') {
    header("Location: ../dashboard.php");
    exit;
}

$connect = new Connect();
$pdo = $connect->pdo();

/* Aantal spelers */
$stmt = $pdo->query("SELECT COUNT(*) FROM speler");
$aantalSpelers = $stmt->fetchColumn();

/* Aantal trainers */
$stmt = $pdo->query("SELECT COUNT(*) FROM trainer");
$aantalTrainers = $stmt->fetchColumn();

/* Aantal wedstrijden */
$stmt = $pdo->query("SELECT COUNT(*) FROM wedstrijden");
$aantalWedstrijden = $stmt->fetchColumn();

/* Aantal trainingen */
$stmt = $pdo->query("SELECT COUNT(*) FROM trainingen");
$aantalTrainingen = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../view/style.css">
</head>
<body>

<div class="layout">

<div class="sidebar">
    <h2>Admin Menu</h2>

    <a href="admindashboard.php" class="active">Dashboard</a>
    <a href="adminleden.php">Leden</a>

    <a href="../trainers/trainerwedstrijden.php">Wedstrijden</a>
    <a href="../trainers/trainertrainingen.php">Trainingen</a>

    <a href="../../logout.php">Uitloggen</a>
</div>

    <div class="content">

        <h2>Welkom Admin 👑</h2>

        <div class="dashboard-grid">

            <div class="dashboard-card">
                <h3>Spelers</h3>
                <p><?= $aantalSpelers ?></p>
            </div>

            <div class="dashboard-card">
                <h3>Trainers</h3>
                <p><?= $aantalTrainers ?></p>
            </div>

            <div class="dashboard-card">
                <h3>Wedstrijden</h3>
                <p><?= $aantalWedstrijden ?></p>
            </div>

            <div class="dashboard-card">
                <h3>Trainingen</h3>
                <p><?= $aantalTrainingen ?></p>
            </div>

        </div>

    </div>
</div>

</body>
</html>