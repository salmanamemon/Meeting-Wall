<?php
require __DIR__ . '/functions.php';
if (currentUser()) { header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $identity = trim((string) ($_POST['identity'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if ($minutes = loginLockMinutes()) {
        $error = "Too many sign-in attempts. Try again in $minutes minutes.";
    } else {
        $stmt = db()->prepare('SELECT id, name, email, password_hash FROM users WHERE email = ? OR username = ? LIMIT 1');
        $stmt->execute([$identity, $identity]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) { clearFailedLogins(); login($user); header('Location: index.php'); exit; }
        $error = recordFailedLogin() ? 'Too many sign-in attempts. Try again in 15 minutes.' : 'Invalid username/email or password.';
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Sign in · Meeting Wall</title><meta name="description" content="Private access to the sales floor Meeting Wall."><meta property="og:type" content="website"><meta property="og:title" content="Meeting Wall"><meta property="og:description" content="Live meeting scoreboard for the sales floor."><meta property="og:url" content="<?= escapeHtml(appUrl('login.php')) ?>"><meta property="og:image" content="<?= escapeHtml(appUrl('assets/images/dar-stories-share.png')) ?>"><meta name="twitter:card" content="summary_large_image"><meta name="twitter:title" content="Meeting Wall"><link rel="icon" type="image/png" href="favicon.png"><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wght@800;900&family=IBM+Plex+Mono:wght@500;600&family=IBM+Plex+Sans:wght@400;600&display=swap"><link rel="stylesheet" href="<?= asset('css/login.css') ?>"></head>
<body class="login-page"><main class="login-shell"><section class="login-intro"><p class="eyebrow">SALES FLOOR</p><h1 class="brand">MEETING <span>WALL</span></h1><p class="lead">The live scoreboard for the sales floor — today's meetings, top closers, and the team goal on one wall.</p><div class="login-note">Secure access for Danube One sales teams</div></section><section class="login-card"><p class="eyebrow">MEMBER ACCESS</p><h2>Welcome back</h2><p class="sub">Enter your details to continue.</p><?php if ($error): ?><p class="error" role="alert"><?= escapeHtml($error) ?></p><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?= escapeHtml(csrf()) ?>"><label>Username or email<input name="identity" required autocomplete="username" placeholder="Enter your username or email" value="<?= escapeHtml($_POST['identity'] ?? '') ?>"></label><label>Password<input type="password" name="password" required autocomplete="current-password" placeholder="Enter your password"></label><button>Sign in <span aria-hidden="true">→</span></button></form></section></main></body></html>
