<?php
session_start();
require_once __DIR__ . '/../../connect.php';
require_once __DIR__ . '/../../app/wedstrijden.php';

$connect = new Connect();
$pdo = $connect->pdo();

$wedstrijden = new Wedstrijden($pdo);

$id = $_POST['id'] ?? null;
$date = $_POST['date'];
$titel = $_POST['titel'];
$status = $_POST['status'];

if ($id) {
    $wedstrijden->update($id, null, null, null, null, $titel, $date, null, $status);
} else {
    $wedstrijden->create(null, null, null, null, $titel, $date, null, $status);
}

header("Location: trainerwedstrijden.php");
exit;
