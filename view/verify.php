<?php
session_start();

require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../app/user.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $inputCode = $_POST['code'] ?? '';

    if (
        isset($_SESSION['2fa_code']) &&
        $_SESSION['2fa_code'] == $inputCode &&
        time() <= $_SESSION['2fa_expires']
    ) {

        /* Definitief inloggen */
        $_SESSION['user_id'] = $_SESSION['2fa_user_id'];
        $_SESSION['role']    = $_SESSION['2fa_role'];

        /* 2FA data opruimen */
        unset($_SESSION['2fa_code']);
        unset($_SESSION['2fa_user_id']);
        unset($_SESSION['2fa_role']);
        unset($_SESSION['2fa_expires']);

        /* Redirect op basis van rol */
        if ($_SESSION['role'] === 'trainer') {
            header("Location: /Goalgetter/view/trainers/trainerdashboard.php");
        } elseif ($_SESSION['role'] === 'club_admin') {
            header("Location: /Goalgetter/view/admins/admindashboard.php");
        } else {
            header("Location: dashboard.php");
        }

        exit;

    } else {
        $error = "Ongeldige of verlopen code.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>2FA Verificatie</title>
</head>
<body>

<form method="POST">
    <h2>Voer 2FA code in</h2>

    <?php if (!empty($error)): ?>
        <p style="color:red;"><?= $error ?></p>
    <?php endif; ?>

    <input type="text" name="code" maxlength="4" required>
    <button type="submit">Verifiëren</button>
</form>

</body>
</html>
