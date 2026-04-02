<?php
require_once 'connect.php';
require_once './app/user.php';

$connect = new Connect();
$user = new User($connect->pdo());
$user->logout();

header('Location: view/login.php');
exit;
