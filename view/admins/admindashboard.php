<?php
session_start();

require_once __DIR__ . '/../../connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'club_admin') {
    header("Location: ../dashboard.php");
    exit;
}

$connect = new Connect();
$pdo = $connect->pdo();

/* Counts */
$aantalSpelers     = $pdo->query("SELECT COUNT(*) FROM speler")->fetchColumn();
$aantalTrainers    = $pdo->query("SELECT COUNT(*) FROM trainer")->fetchColumn();
$aantalWedstrijden = $pdo->query("SELECT COUNT(*) FROM wedstrijden")->fetchColumn();
$aantalTrainingen  = $pdo->query("SELECT COUNT(*) FROM trainingen")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<div class="layout">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2>Admin Menu</h2>

        <a href="admindashboard.php" class="active">Dashboard</a>
        <a href="adminleden.php">Leden</a>
        <a href="adminwedstrijden.php">Wedstrijden</a>
        <a href="admintrainingen.php">Trainingen</a>

        <a href="../login.php">Uitloggen</a>
    </div>

    <!-- CONTENT -->
    <div class="content">

        <!-- ✅ Zelfde header structuur -->
        <div class="header-row">
            <h2>Dashboard</h2>
        </div>

        <p class="welcome-line">Welkom, admin.</p>

        <?php require __DIR__ . '/../partials/notification_panel.php'; ?>

        <!-- ✅ Grid zoals andere pagina's -->
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