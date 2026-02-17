<?php
require_once "connect.php";
require_once "./app/user.php";

$db = new Connect();
$pdo = $db->pdo();
$user = new User($pdo);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if ($user->login($_POST['identifier'], $_POST['password'])) {
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Onjuiste gegevens";
    }
}
?>

<h2>Login</h2>

<form method="POST">
    <input type="text"
           name="identifier"
           placeholder="Email of Lidnummer"
           required>

    <input type="password"
           name="password"
           placeholder="Wachtwoord"
           required>

    <button type="submit">Inloggen</button>
</form>

<?php if (isset($error)) echo "<p>$error</p>"; ?>