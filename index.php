<?php
require_once 'connect.php';
require_once './app/user.php';

$connect = new Connect();
$pdo = $connect->pdo();

$user = new User($pdo);

if (!$user->isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$currentUser = $user->currentUser();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>

<h2>Welkom <?= htmlspecialchars($currentUser['email']) ?></h2>

<p>Rol: <?= $currentUser['role'] ?></p>

<form method="POST" action="login.php">
    <button type="submit">Logout</button>
</form>

</body>
</html>
