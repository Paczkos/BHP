<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/lang.php';

function sanitize(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function app_public_url(): string
{
    return rtrim(APP_URL, '/');
}

function app_root_url(): string
{
    $publicUrl = app_public_url();

    if (substr($publicUrl, -7) === '/public') {
        return rtrim(substr($publicUrl, 0, -7), '/');
    }

    return $publicUrl;
}

function asset_url(string $path): string
{
    return app_root_url() . '/' . ltrim($path, '/');
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    if (!empty($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }

    return null;
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user']);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('error', t('auth.login_required'));
        redirect('login.php');
    }
}

function is_admin(): bool
{
    return !empty($_SESSION['admin']);
}

function require_admin(): void
{
    if (!is_admin()) {
        flash('error', t('auth.admin_required'));
        redirect('login.php');
    }
}

function generate_certificate_number(int $certificateId): string
{
    return sprintf('BHP-%s-%06d', date('Y'), $certificateId);
}

function send_notification(string $to, string $subject, string $message): void
{
    $headers = 'From: ' . MAIL_FROM . "\r\n" .
        'Reply-To: ' . MAIL_FROM . "\r\n" .
        'Content-Type: text/plain; charset=UTF-8';

    @mail($to, $subject, $message, $headers);
}
