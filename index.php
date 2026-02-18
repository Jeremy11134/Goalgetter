<?php
session_start();

require_once "connect.php";
require_once "./app/speler.php";

$db = new Connect();
$pdo = $db->pdo();

/* Fake login als speler */
$_SESSION['role'] = 'speler';
$_SESSION['user_id'] = 2; // moet een speler user zijn

$speler = new Speler($pdo);

/* Meld aanwezig */
$result = $speler->meldAanwezigheid(1, 'aanwezig');

if ($result) {
    echo "Status succesvol opgeslagen ✅";
} else {
    echo "Fout ❌";
}