<?php
session_start();

require_once "connect.php";
require_once "./app/trainer.php";

$db = new Connect();
$pdo = $db->pdo();

/* Fake login voor test */
$_SESSION['role'] = 'trainer';

$trainer = new Trainer($pdo);

if ($trainer->createTraining(
    1,
    "18:00",
    "19:30",
    "Avondtraining",
    "2026-03-10",
    "Focus op passing",
    "planned"
)) {
    echo "Training aangemaakt ✅";
} else {
    echo "Geen toegang of fout ❌";
}