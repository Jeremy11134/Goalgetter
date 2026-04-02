<?php

require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../app/user.php';


$connect = new Connect();
$pdo = $connect->pdo();

$user = new User($pdo);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $identifier = $_POST['identifier'] ?? '';
    $password   = $_POST['password'] ?? '';

    if ($user->login($identifier, $password)) {
        header("Location: verify.php");   
        exit;
    } else {
        $error = "Ongeldige login gegevens.";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inloggen – GoalGetter</title>
    <link rel="stylesheet" href="/Goalgetter/view/login.css">
</head>
<body class="auth-page">

<div class="auth-shell">
    <h1 class="auth-brand">GoalGetter</h1>
    <div class="auth-card">
        <h2 class="auth-title">Inloggen</h2>

        <?php if ($error): ?>
            <p class="alert alert-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="identifier">Email of lidnummer</label>
            <input type="text" id="identifier" name="identifier" required autocomplete="username">

            <label for="password">Wachtwoord</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">

            <button type="submit">Inloggen</button>

            <div class="extra-links">
                <a href="register.php">Nog geen account? Registreer</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>