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
    <link rel="stylesheet" href="/BHP/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
<header>
    <div class="container">
        <nav>
            <div>
                <strong><?= APP_NAME ?></strong>
            </div>
            <div class="actions">
                <?php foreach (AVAILABLE_LANGUAGES as $code): ?>
                    <a href="?lang=<?= $code ?>" data-lang="<?= $code ?>"><?= strtoupper($code) ?></a>
                <?php endforeach; ?>
                <?php if (is_logged_in()): ?>
                    <a href="dashboard.php"><?= t('nav.dashboard') ?></a>
                    <a href="logout.php"><?= t('nav.logout') ?></a>
                <?php else: ?>
                    <a href="login.php"><?= t('nav.login') ?></a>
                    <a href="register.php" class="btn btn-primary"><?= t('nav.register') ?></a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>
<main>
