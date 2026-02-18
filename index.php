<?php
session_start();

require_once "connect.php";
require_once "./app/speler.php";

$db = new Connect();
$pdo = $db->pdo();

$speler = new Speler($pdo);

$stats = $speler->getStatistieken(2);

if ($stats) {
    echo "<pre>";
    print_r($stats);
    echo "</pre>";
} else {
    echo "Speler niet gevonden";
}
echo "Goals: " . $stats['goals'] . "<br>";
echo "Wins: " . $stats['win'] . "<br>";
echo "Draws: " . $stats['draw'] . "<br>";
echo "Losses: " . $stats['loses'] . "<br>";