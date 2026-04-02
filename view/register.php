<?php

require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../app/user.php';
require_once __DIR__ . '/../app/ouders.php';  

$connect = new Connect();
$pdo = $connect->pdo();

$ouders = new Ouders($pdo);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $voornaam        = $_POST['voornaam'] ?? '';
    $tussenvoegsels  = $_POST['tussenvoegsels'] ?? null;
    $achternaam      = $_POST['achternaam'] ?? '';
    $email           = $_POST['email'] ?? '';
    $password        = $_POST['password'] ?? '';

    $lidnummer = 'LID' . time();

    if ($ouders->registerouder(
    $voornaam,
    $tussenvoegsels,
    $achternaam,
    $email,
    $password
    )) {
        $success = "Registratie succesvol! Je kunt nu inloggen.";
    } else {
        $error = "Registratie mislukt.";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registreren – GoalGetter</title>
    <link rel="stylesheet" href="/Goalgetter/view/register.css">
</head>
<body class="auth-page">

<div class="auth-shell">
    <h1 class="auth-brand">GoalGetter</h1>
    <div class="auth-card">
        <h2 class="auth-title">Registreren</h2>

        <?php if ($error): ?>
            <p class="alert alert-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if ($success): ?>
            <p class="alert alert-success"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="voornaam">Voornaam</label>
            <input type="text" id="voornaam" name="voornaam" required autocomplete="given-name">

            <label for="tussenvoegsels">Tussenvoegsels</label>
            <input type="text" id="tussenvoegsels" name="tussenvoegsels" autocomplete="additional-name">

            <label for="achternaam">Achternaam</label>
            <input type="text" id="achternaam" name="achternaam" required autocomplete="family-name">

            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" required autocomplete="email">

            <label for="password">Wachtwoord</label>
            <input type="password" id="password" name="password" required autocomplete="new-password">

            <button type="submit">Registreren</button>
            <div class="extra-links">
                <a href="login.php">Heb je al een account? Log in</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>