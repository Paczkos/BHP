<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$lang = detect_language();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (authenticate_admin($email, $password)) {
        redirect('index.php');
    } else {
        flash('error', t('auth.invalid_credentials'));
    }
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('admin.panel') ?></title>
    <link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
</head>
<body class="admin-login-body">
    <div class="admin-login-wrapper">
        <div class="admin-login-panel">
            <a class="brand brand--center" href="<?= app_public_url() ?>">
                <span class="brand-mark" aria-hidden="true">
                    <span class="brand-initials">AG</span>
                    <span class="brand-mark-dot"></span>
                    <span class="brand-mark-label">BHP</span>
                </span>
                <span class="brand-text">
                    <span class="brand-title"><?= APP_NAME ?></span>
                    <span class="brand-tagline"><?= t('admin.login_subtitle') ?></span>
                </span>
            </a>
            <h1><?= t('admin.login_intro') ?></h1>
            <p class="admin-login-subtitle"><?= t('admin.login_description') ?></p>
            <?php if ($message = flash('error')): ?>
                <div class="alert error"><?= $message ?></div>
            <?php endif; ?>
            <form method="post" class="form-vertical admin-login-form">
                <div class="form-field">
                    <label for="email"><?= t('auth.email') ?></label>
                    <input type="email" name="email" id="email" required autocomplete="username">
                </div>
                <div class="form-field">
                    <label for="password"><?= t('auth.password') ?></label>
                    <input type="password" name="password" id="password" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn btn-primary btn-full"><?= t('auth.sign_in') ?></button>
            </form>
        </div>
        <aside class="admin-login-aside">
            <div class="admin-login-aside-inner">
                <h2><?= t('admin.login_highlight_title') ?></h2>
                <ul>
                    <li><?= t('admin.login_highlight_one') ?></li>
                    <li><?= t('admin.login_highlight_two') ?></li>
                    <li><?= t('admin.login_highlight_three') ?></li>
                </ul>
            </div>
        </aside>
    </div>
</body>
</html>
