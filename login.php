<?php
require_once 'connect.php';
require_once './app/user.php';

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
<html>
<head>
    <title>Login</title>
</head>
<body>

<h2>Login</h2>

<?php if ($error): ?>
    <p style="color:red;"><?= $error ?></p>
<?php endif; ?>

<form method="POST">
    <label>Email of Lidnummer:</label><br>
    <input type="text" name="identifier" required><br><br>

    <label>Wachtwoord:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Login</button>
</form>

<p>Nog geen account? <a href="register.php">Registreer hier</a></p>

</body>
</html>
