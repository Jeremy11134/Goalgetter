<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /Goalgetter/view/login.php');
    exit;
}

require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../app/notifications.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    (new Notifications((new Connect())->pdo()))->markAsRead($id, (int) $_SESSION['user_id']);
}

$role = $_SESSION['role'] ?? 'speler';
if ($role === 'trainer') {
    $home = '/Goalgetter/view/trainers/trainerdashboard.php';
} elseif ($role === 'club_admin') {
    $home = '/Goalgetter/view/admins/admindashboard.php';
} else {
    $home = '/Goalgetter/view/users/dashboard.php';
}

header('Location: ' . $home);
exit;
