<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

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
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('admin.panel') ?></title>
    <link rel="stylesheet" href="/BHP/assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="card form-card">
            <h2><?= t('admin.panel') ?></h2>
            <?php if ($message = flash('error')): ?>
                <div class="alert error"><?= $message ?></div>
            <?php endif; ?>
            <form method="post">
                <div>
                    <label for="email"><?= t('auth.email') ?></label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div>
                    <label for="password"><?= t('auth.password') ?></label>
                    <input type="password" name="password" id="password" required>
                </div>
                <button type="submit" class="btn btn-primary"><?= t('auth.sign_in') ?></button>
            </form>
        </div>
    </div>
</body>
</html>
