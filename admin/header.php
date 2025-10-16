<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/course.php';
require_once __DIR__ . '/../includes/db.php';
$lang = detect_language();
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('admin.panel') ?> | <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
<header class="site-header site-header--admin">
    <div class="container">
        <div class="header-inner">
            <a class="brand" href="index.php">
                <span class="brand-mark" aria-hidden="true">
                    <span class="brand-initials">AG</span>
                    <span class="brand-mark-dot"></span>
                    <span class="brand-mark-label">BHP</span>
                </span>
                <span class="brand-text">
                    <span class="brand-title"><?= t('admin.panel') ?></span>
                    <span class="brand-tagline"><?= APP_NAME ?></span>
                </span>
            </a>
            <nav class="admin-nav">
                <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>"><?= t('admin.users') ?></a>
                <a href="courses.php" class="<?= basename($_SERVER['PHP_SELF']) === 'courses.php' ? 'active' : '' ?>"><?= t('admin.courses') ?></a>
                <a href="results.php" class="<?= basename($_SERVER['PHP_SELF']) === 'results.php' ? 'active' : '' ?>"><?= t('admin.results') ?></a>
                <a href="certificates.php" class="<?= basename($_SERVER['PHP_SELF']) === 'certificates.php' ? 'active' : '' ?>"><?= t('admin.certificates') ?></a>
                <?php if (is_admin()): ?>
                    <a href="logout.php"><?= t('nav.logout') ?></a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</header>
<main class="page-content container">
