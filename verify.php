<?php
session_start();
require_once 'connect.php';
require_once './app/user.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $inputCode = $_POST['code'] ?? '';

    if (
        isset($_SESSION['2fa_code']) &&
        $_SESSION['2fa_code'] == $inputCode &&
        time() <= $_SESSION['2fa_expires']
    ) {

        $_SESSION['user_id'] = $_SESSION['2fa_user_id'];

        unset($_SESSION['2fa_code']);
        unset($_SESSION['2fa_user_id']);
        unset($_SESSION['2fa_expires']);

        header("Location: index.php");
        exit;

    } else {
        $error = "Ongeldige of verlopen code.";
    }
}
?>

<form method="POST">
    <h2>Voer 2FA code in</h2>
    <?php if (!empty($error)) echo "<p style='color:red'>$error</p>"; ?>
    <input type="text" name="code" maxlength="4" required>
    <button type="submit">Verifiëren</button>
</form>