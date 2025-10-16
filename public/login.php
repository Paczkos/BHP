<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (authenticate_user($email, $password)) {
        flash('success', t('auth.login'));
        redirect('dashboard.php');
    } else {
        flash('error', t('auth.invalid_credentials'));
    }
}
?>
<div class="container">
    <div class="card form-card">
        <h2><?= t('auth.login') ?></h2>
        <?php if ($message = flash('success')): ?>
            <div class="alert"><?= $message ?></div>
        <?php endif; ?>
        <?php if ($message = flash('error')): ?>
            <div class="alert error"><?= $message ?></div>
        <?php endif; ?>
        <form method="post">
            <div>
                <label for="email"><?= t('auth.email') ?></label>
                <input type="email" id="email" name="email" required>
            </div>
            <div>
                <label for="password"><?= t('auth.password') ?></label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary"><?= t('auth.sign_in') ?></button>
        </form>
        <p><?= t('auth.no_account') ?> <a href="register.php"><?= t('auth.register') ?></a></p>
    </div>
</div>
<?php
require_once __DIR__ . '/../includes/footer.php';
?>
