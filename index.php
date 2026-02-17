<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "connect.php";
require_once "./app/trainingen.php";

$db = new Connect();
$pdo = $db->pdo();
$trainingen = new Trainingen($pdo);

echo "<h2>Test dubbele trainingen</h2>";

// Eerste training (zou moeten lukken)
$result1 = $trainingen->create(
    1,
    "18:00",
    "19:30",
    "Avondtraining",
    "2026-02-20",
    "Conditietraining",
    "planned"
);

echo $result1
    ? "Training 1 aangemaakt ✅<br>"
    : "Training 1 geweigerd ❌<br>";


// Tweede training met overlap (zou geweigerd moeten worden)
$result2 = $trainingen->create(
    1,
    "18:30",
    "20:00",
    "Overlappende training",
    "2026-02-20",
    "Test overlap",
    "planned"
);

echo $result2
    ? "Training 2 aangemaakt (FOUT) ❌<br>"
    : "Training 2 correct geweigerd ✅<br>";


// Derde training zonder overlap (zou moeten lukken)
$result3 = $trainingen->create(
    1,
    "19:30",
    "21:00",
    "Late training",
    "2026-02-20",
    "Geen overlap",
    "planned"
);

echo $result3
    ? "Training 3 aangemaakt ✅<br>"
    : "Training 3 geweigerd ❌<br>";