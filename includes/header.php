<?php
require_once __DIR__ . '/functions.php';
$lang = detect_language();
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('app.title') ?></title>
    <link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
<header class="site-header">
    <div class="container">
        <div class="header-inner">
            <a class="brand" href="index.php">
                <span class="brand-mark" aria-hidden="true">
                    <span class="brand-initials">AG</span>
                    <span class="brand-mark-dot"></span>
                    <span class="brand-mark-label">BHP</span>
                </span>
                <span class="brand-text">
                    <span class="brand-title"><?= APP_NAME ?></span>
                    <span class="brand-tagline"><?= t('branding.tagline') ?></span>
                </span>
            </a>
            <div class="nav-groups">
                <div class="lang-switcher" aria-label="<?= t('branding.language_switcher') ?>">
                    <span><?= t('branding.language_switcher') ?></span>
                    <?php foreach (AVAILABLE_LANGUAGES as $code): ?>
                        <a href="?lang=<?= $code ?>" data-lang="<?= $code ?>" class="<?= $code === $lang ? 'active' : '' ?>"><?= strtoupper($code) ?></a>
                    <?php endforeach; ?>
                </div>
                <div class="nav-actions">
                    <?php if (is_logged_in()): ?>
                        <a href="profile.php" class="btn btn-link"><?= t('nav.profile') ?></a>
                        <a href="dashboard.php" class="btn btn-outline"><?= t('nav.dashboard') ?></a>
                        <a href="logout.php" class="btn btn-primary"><?= t('nav.logout') ?></a>
                    <?php else: ?>
                        <a href="<?= asset_url('admin/login.php') ?>" class="btn btn-link"><?= t('nav.admin_login') ?></a>
                        <a href="login.php" class="btn btn-outline"><?= t('nav.login') ?></a>
                        <a href="register.php" class="btn btn-primary"><?= t('nav.register') ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</header>
<main class="page-content">
