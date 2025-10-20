<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$user = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $document = trim($_POST['passport_or_pesel'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $language = $_POST['language'] ?? ($user['language'] ?? 'pl');
    if (!in_array($language, AVAILABLE_LANGUAGES, true)) {
        $language = $user['language'] ?? 'pl';
    }
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if ($firstName === '') {
        $errors['first_name'] = t('profile.error_first_name');
    }

    if ($lastName === '') {
        $errors['last_name'] = t('profile.error_last_name');
    }

    if ($document === '') {
        $errors['passport_or_pesel'] = t('profile.error_document');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = t('profile.error_email');
    } elseif (user_email_taken($email, (int) $user['id'])) {
        $errors['email'] = t('auth.email_exists');
    }

    if ($password !== '' || $passwordConfirm !== '') {
        if ($password !== $passwordConfirm) {
            $errors['password'] = t('auth.password_mismatch');
        } elseif (strlen($password) < 8) {
            $errors['password'] = t('profile.error_password_length');
        }
    }

    if (!$errors) {
        $updated = update_user_profile((int) $user['id'], [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'passport_or_pesel' => $document,
            'email' => $email,
            'language' => $language,
        ]);

        if ($updated && $password !== '') {
            update_user_password((int) $user['id'], $password);
        }

        if ($updated) {
            $_SESSION['user'] = array_merge($user, [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'passport_or_pesel' => $document,
                'email' => $email,
                'language' => $language,
            ]);
            $_SESSION['lang'] = $language;

            flash('success', t('profile.updated_success'));
            redirect('profile.php');
        } else {
            $errors['general'] = t('profile.update_failed');
        }
    }
}

$lang = $_SESSION['lang'] ?? ($user['language'] ?? detect_language());
$selectedLanguage = $_POST['language'] ?? $lang;
if (!in_array($selectedLanguage, AVAILABLE_LANGUAGES, true)) {
    $selectedLanguage = $lang;
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container profile-wrapper">
    <h1><?= t('profile.title') ?></h1>
    <p><?= t('profile.subtitle') ?></p>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert error"><?= $errors['general'] ?></div>
    <?php elseif ($msg = flash('success')): ?>
        <div class="alert"><?= $msg ?></div>
    <?php endif; ?>

    <form method="post" class="form-card">
        <div class="form-grid">
            <label>
                <span><?= t('auth.first_name') ?></span>
                <input type="text" name="first_name" value="<?= htmlspecialchars($_POST['first_name'] ?? $user['first_name']) ?>" required>
                <?php if (!empty($errors['first_name'])): ?><small class="form-error"><?= $errors['first_name'] ?></small><?php endif; ?>
            </label>
            <label>
                <span><?= t('auth.last_name') ?></span>
                <input type="text" name="last_name" value="<?= htmlspecialchars($_POST['last_name'] ?? $user['last_name']) ?>" required>
                <?php if (!empty($errors['last_name'])): ?><small class="form-error"><?= $errors['last_name'] ?></small><?php endif; ?>
            </label>
        </div>
        <label>
            <span><?= t('auth.document') ?></span>
            <input type="text" name="passport_or_pesel" value="<?= htmlspecialchars($_POST['passport_or_pesel'] ?? $user['passport_or_pesel']) ?>" required>
            <?php if (!empty($errors['passport_or_pesel'])): ?><small class="form-error"><?= $errors['passport_or_pesel'] ?></small><?php endif; ?>
        </label>
        <label>
            <span><?= t('auth.email') ?></span>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? $user['email']) ?>" required>
            <?php if (!empty($errors['email'])): ?><small class="form-error"><?= $errors['email'] ?></small><?php endif; ?>
        </label>
        <label>
            <span><?= t('auth.language') ?></span>
            <select name="language">
                <?php foreach (AVAILABLE_LANGUAGES as $code): ?>
                    <option value="<?= $code ?>" <?= ($code === $selectedLanguage) ? 'selected' : '' ?>><?= strtoupper($code) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <fieldset class="form-fieldset">
            <legend><?= t('profile.password_section_title') ?></legend>
            <p class="form-help"><?= t('profile.password_section_help') ?></p>
            <div class="form-grid">
                <label>
                    <span><?= t('auth.password') ?></span>
                    <input type="password" name="password" autocomplete="new-password">
                </label>
                <label>
                    <span><?= t('auth.confirm_password') ?></span>
                    <input type="password" name="password_confirm" autocomplete="new-password">
                </label>
            </div>
            <?php if (!empty($errors['password'])): ?><small class="form-error"><?= $errors['password'] ?></small><?php endif; ?>
        </fieldset>
        <button type="submit" class="btn btn-primary" style="margin-top:1rem;">
            <?= t('profile.save_button') ?>
        </button>
    </form>
</div>
<?php
require_once __DIR__ . '/../includes/footer.php';
?>
