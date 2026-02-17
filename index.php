<?php
require_once "connect.php";
require_once "./app/speler.php";

$db = new Connect();
$pdo = $db->pdo();

$speler = new Speler($pdo);

$gemiddelde = $speler->gemiddeldeGoals(1);

echo "Gemiddelde goals per wedstrijd: " . $gemiddelde;