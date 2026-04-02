<?php

require_once 'connect.php';

require_once './app/user.php';



$connect = new Connect();

$pdo = $connect->pdo();



$user = new User($pdo);



if (!$user->isLoggedIn()) {

    header("Location: view/login.php");

    exit;

}



$currentUser = $user->currentUser();

?>



<!DOCTYPE html>

<html lang="nl">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>GoalGetter</title>

    <link rel="stylesheet" href="/Goalgetter/view/style.css">

</head>

<body class="simple-page">



<div class="simple-card">

    <h2>Welkom, <?= htmlspecialchars($currentUser['email']) ?></h2>

    <p>Rol: <?= htmlspecialchars((string) $currentUser['role']) ?></p>

    <form method="POST" action="logout.php">

        <button type="submit">Uitloggen</button>

    </form>

</div>



</body>

</html>

