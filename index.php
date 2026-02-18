<?php
session_start();

require_once "connect.php";
require_once "./app/speler.php";

$db = new Connect();
$pdo = $db->pdo();

/* Fake login als speler */
$_SESSION['role'] = 'speler';
$_SESSION['user_id'] = 2;

$speler = new Speler($pdo);

/* Training aanmelden */
$speler->meldAanwezigheid('training', 1, 'afwezig');

/* Wedstrijd afmelden */
$speler->meldAanwezigheid('wedstrijd', 3, 'aanwezig');