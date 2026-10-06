<?php
declare(strict_types=1);
require __DIR__ . '/_init.php';
admin_headers();
admin_session();

$hash = trim((string) cfg('admin_pass_hash', ''));
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $hash !== '') {
    csrf_check();
    if (admin_locked_out()) {
        $error = 'Too many wrong attempts. Please wait 15 minutes and try again.';
    } else {
        $user = trim((string) ($_POST['user'] ?? ''));
        $pass = (string) ($_POST['pass'] ?? '');
        if (hash_equals(trim((string) cfg('admin_user', 'olive')), $user) && password_verify($pass, $hash)) {
            admin_clear_fails();
            session_regenerate_id(true);
            $_SESSION['admin_user'] = $user;
            $_SESSION['admin_last'] = time();
            unset($_SESSION['csrf']);
            header('Location: /admin/', true, 303);
            exit;
        }
        admin_record_fail();
        $error = 'Wrong username or password.';
    }
}
if (admin_logged_in()) {
    header('Location: /admin/');
    exit;
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Log in | Olive admin</title>
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset('admin/admin.css')) ?>">
</head>
<body class="login">
<main class="login__box">
  <p class="login__logo"><?= logo_svg('olive-logo') ?></p>
  <h1>Website admin</h1>
  <?php if ($hash === ''): ?>
    <p class="alert alert--bad">The admin is locked. Set <code>admin_pass_hash</code> in config.php on this server.</p>
  <?php else: ?>
    <?php if ($error): ?><p class="alert alert--bad" role="alert"><?= e($error) ?></p><?php endif; ?>
    <form method="post" action="/admin/login.php">
      <?= csrf_field() ?>
      <label>Username <input name="user" autocomplete="username" required></label>
      <label>Password <input name="pass" type="password" autocomplete="current-password" required></label>
      <button class="btn" type="submit">Log in</button>
    </form>
  <?php endif; ?>
</main>
</body>
</html>
