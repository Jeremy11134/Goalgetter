<?php
require_once "connect.php";
require_once "./app/ouders.php";

$db = new Connect();
$pdo = $db->pdo();

$ouders = new Ouders($pdo);

if ($ouders->createFullOuder(
    "Piet",
    null,
    "De Vries",
    "piet@example.com",
    "test123",
    "LID2001"
)) {
    echo "Ouder succesvol aangemaakt ✅";
} else {
    echo "Er ging iets mis ❌";
}
