<?php

require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../app/user.php';

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

    $lidnummer = 'LID' . time();   // automatisch gegenereerd

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
<html>
<head>
    <title>Registreren</title>
</head>
<body>

<h2>Registreren</h2>

<?php if ($error): ?>
    <p style="color:red;"><?= $error ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p style="color:green;"><?= $success ?></p>
<?php endif; ?>

<form method="POST">

    <label>Voornaam:</label><br>
    <input type="text" name="voornaam" required><br><br>

    <label>Tussenvoegsels:</label><br>
    <input type="text" name="tussenvoegsels"><br><br>

    <label>Achternaam:</label><br>
    <input type="text" name="achternaam" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Wachtwoord:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Registreren</button>
</form>

<p>Al een account? <a href="./view/login.php">Login hier</a></p>

</body>
</html>
