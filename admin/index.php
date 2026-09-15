<?php
/**
 * Simple session-based login for the internal appointments dashboard.
 * Deliberately outside the public template shell (no site nav/CTA banners)
 * — this is an operator tool, not a marketing page. Password comes from
 * .env (ADMIN_PASSWORD), same out-of-git-secrets pattern as SMTP/Twilio.
 */
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../config/mail.php'; // gives us load_dotenv_once()/env()
$adminPassword = env('ADMIN_PASSWORD');

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted = (string)($_POST['password'] ?? '');
    if ($adminPassword !== null && $adminPassword !== '' && hash_equals($adminPassword, $submitted)) {
        $_SESSION['scheduler_admin'] = true;
        header('Location: /admin/appointments.php');
        exit;
    }
    $error = 'Incorrect password.';
}

if (!empty($_SESSION['scheduler_admin'])) {
    header('Location: /admin/appointments.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Admin Login | Clean Green Turf</title>
<link rel="stylesheet" href="/assets/css/style.css">
<style>
body { display: flex; align-items: center; justify-content: center; min-height: 100vh; }
.admin-login { background: var(--paper); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: var(--space-6); max-width: 360px; width: 100%; box-shadow: var(--shadow); }
.admin-login h1 { font-size: var(--step-1); margin-top: 0; }
.admin-login__error { color: #b3261e; font-size: var(--step--1); }
</style>
</head>
<body>
<div class="admin-login">
    <h1>Admin Login</h1>
    <?php if ($error): ?><p class="admin-login__error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if ($adminPassword === null || $adminPassword === ''): ?>
        <p>No admin password is configured yet. Add <code>ADMIN_PASSWORD</code> to <code>.env</code> on the server &mdash; see <code>.env.example</code>.</p>
    <?php else: ?>
    <form method="post">
        <div class="quote-form__row">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autofocus>
        </div>
        <button type="submit" class="btn btn--primary btn--block">Log In</button>
    </form>
    <?php endif; ?>
</div>
</body>
</html>
