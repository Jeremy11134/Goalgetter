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
<html>
<head>
    <title>Login</title>
     <link rel="stylesheet" href="/Goalgetter/view/login.css">
</head>
<body>

<h2>Login</h2>

<?php if ($error): ?>
    <p style="color:red;"><?= $error ?></p>
<?php endif; ?>

<div class="login-container">
    <form method="POST">
        <label>Email of Lidnummer:</label><br>
        <input type="text" name="identifier" required><br><br>

        <label>Wachtwoord:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Login</button>

        <div class="extra-links">
            <a href="register.php">Nog geen account? Registreer</a>
        </div>
    </form>
</div>

</body>
</html>
