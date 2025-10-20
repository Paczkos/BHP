<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'first_name' => sanitize($_POST['first_name'] ?? ''),
        'last_name' => sanitize($_POST['last_name'] ?? ''),
        'passport_or_pesel' => sanitize($_POST['passport_or_pesel'] ?? ''),
        'email' => sanitize($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'language' => sanitize($_POST['language'] ?? 'pl'),
    ];

    if ($data['password'] !== $data['confirm_password']) {
        flash('error', t('auth.password_mismatch'));
    } elseif (find_user_by_email($data['email'])) {
        flash('error', t('auth.email_exists'));
    } else {
        if (register_user($data)) {
            flash('success', t('auth.success_register'));
            redirect('login.php');
        } else {
            flash('error', t('alerts.registration_failed'));
        }
    }
}
?>
<div class="container">
    <div class="card form-card">
        <h2><?= t('auth.register') ?></h2>
        <?php if ($message = flash('error')): ?>
            <div class="alert error"><?= $message ?></div>
        <?php endif; ?>
        <form method="post">
            <div>
                <label for="first_name"><?= t('auth.first_name') ?></label>
                <input type="text" id="first_name" name="first_name" required>
            </div>
            <div>
                <label for="last_name"><?= t('auth.last_name') ?></label>
                <input type="text" id="last_name" name="last_name" required>
            </div>
            <div>
                <label for="passport_or_pesel"><?= t('auth.document') ?></label>
                <input type="text" id="passport_or_pesel" name="passport_or_pesel" required>
            </div>
            <div>
                <label for="email"><?= t('auth.email') ?></label>
                <input type="email" id="email" name="email" required>
            </div>
            <div>
                <label for="language"><?= t('auth.language') ?></label>
                <select id="language" name="language">
                    <?php foreach (AVAILABLE_LANGUAGES as $code): ?>
                        <option value="<?= $code ?>"><?= strtoupper($code) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="password"><?= t('auth.password') ?></label>
                <input type="password" id="password" name="password" required minlength="8">
            </div>
            <div>
                <label for="confirm_password"><?= t('auth.confirm_password') ?></label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
            </div>
            <button type="submit" class="btn btn-primary"><?= t('auth.create_account') ?></button>
        </form>
        <p><?= t('auth.already_have_account') ?> <a href="login.php"><?= t('auth.login') ?></a></p>
    </div>
</div>
<?php
require_once __DIR__ . '/../includes/footer.php';
?>
