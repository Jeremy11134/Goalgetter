<?php
require_once "connect.php";
require_once "./app/club_admin.php";

$db = new Connect();
$pdo = $db->pdo();

$clubAdmin = new ClubAdmin($pdo);

if ($clubAdmin->createFullClubAdmin(
    "Lisa",
    null,
    "Meijer",
    "lisa@example.com",
    "123456",
    "LID5001"
)) {
    echo "Club admin volledig aangemaakt ✅";
} else {
    echo "Er ging iets mis ❌";
}
