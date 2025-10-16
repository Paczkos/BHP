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
    <link rel="stylesheet" href="/BHP/assets/css/style.css">
</head>
<body>
<header>
    <div class="container">
        <nav>
            <div><strong><?= t('admin.panel') ?></strong></div>
            <div class="actions">
                <a href="index.php"><?= t('admin.users') ?></a>
                <a href="courses.php"><?= t('admin.courses') ?></a>
                <a href="results.php"><?= t('admin.results') ?></a>
                <a href="certificates.php"><?= t('admin.certificates') ?></a>
                <?php if (is_admin()): ?>
                    <a href="logout.php"><?= t('nav.logout') ?></a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>
<main class="container">
