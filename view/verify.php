<?php
session_start();

require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../app/user.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

   $inputCode =
    ($_POST['code1'] ?? '') .
    ($_POST['code2'] ?? '') .
    ($_POST['code3'] ?? '') .
    ($_POST['code4'] ?? '');

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
            header("Location: /Goalgetter/view/users/dashboard.php");
        }

        exit;

    } else {
        $error = "Ongeldige of verlopen code.";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Beveiligingscode – GoalGetter</title>
    <link rel="stylesheet" href="/Goalgetter/view/verify.css">
</head>
<body class="auth-page">

<div class="auth-shell">
    <h1 class="auth-brand">GoalGetter</h1>
    <form method="POST" class="auth-verify-form">
        <h2>Voer je code in</h2>
        <p style="text-align:center;margin:0;color:var(--gg-text-muted);font-size:14px;">We hebben een 4-cijferige code naar je e-mail gestuurd.</p>

        <?php if (!empty($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <div class="code-inputs">
            <input type="text" maxlength="1" name="code1" required inputmode="numeric" pattern="[0-9]*" aria-label="Eerste cijfer">
            <input type="text" maxlength="1" name="code2" required inputmode="numeric" pattern="[0-9]*" aria-label="Tweede cijfer">
            <input type="text" maxlength="1" name="code3" required inputmode="numeric" pattern="[0-9]*" aria-label="Derde cijfer">
            <input type="text" maxlength="1" name="code4" required inputmode="numeric" pattern="[0-9]*" aria-label="Vierde cijfer">
        </div>

        <button type="submit">Verifiëren</button>
    </form>
</div>

<script>
    const inputs = document.querySelectorAll('.code-inputs input');

    inputs.forEach((input, index) => {
        input.addEventListener('input', () => {
            if (input.value && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === "Backspace" && !input.value && index > 0) {
                inputs[index - 1].focus();
            }
        });
    });
</script>

</body>
</html>
