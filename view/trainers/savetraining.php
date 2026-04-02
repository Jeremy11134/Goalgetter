<?php
session_start();

require_once __DIR__ . '/../../connect.php';
require_once __DIR__ . '/../../app/trainingen.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'trainer') {
    header("Location: ../dashboard.php");
    exit;
}

$connect = new Connect();
$pdo = $connect->pdo();

$trainingen = new Trainingen($pdo);

$id     = $_POST['id'] ?? null;
$date   = $_POST['date'] ?? null;
$start  = $_POST['start'] ?? null;
$end    = $_POST['end'] ?? null;
$titel  = $_POST['titel'] ?? null;
$status = $_POST['status'] ?? 'gepland';

if (!$date || !$start || !$end || !$titel) {
    die("Verplichte velden ontbreken.");
}

try {

    if ($id) {
        $trainingen->update(
            (int)$id,
            $start,
            $end,
            $titel,
            $date,
            null,
            $status
        );
    } else {
        $trainingen->create(
            null,
            $start,
            $end,
            $titel,
            $date,
            null,
            $status
        );
    }

    header("Location: trainertrainingen.php");
    exit;

} catch (Throwable $e) {
    die("Fout bij opslaan: " . $e->getMessage());
}