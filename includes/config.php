<?php
if (!function_exists('config_env')) {
    function config_env(string $key, $default = null)
    {
        $value = getenv($key);
        return $value === false ? $default : $value;
    }
}

// Database configuration
if (!defined('DB_HOST')) {
    define('DB_HOST', config_env('DB_HOST', 'localhost'));
}

if (!defined('DB_NAME')) {
    define('DB_NAME', config_env('DB_NAME', 'serwer244157_bhp'));
}

if (!defined('DB_USER')) {
    define('DB_USER', config_env('DB_USER', 'serwer244157_bhp'));
}

if (!defined('DB_PASS')) {
    define('DB_PASS', config_env('DB_PASS', 'PAczkos19861986#'));
}

// Application configuration
if (!defined('APP_NAME')) {
    define('APP_NAME', config_env('APP_NAME', 'AGAT GROUP – Platforma BHP'));
}

if (!defined('APP_COMPANY')) {
    define('APP_COMPANY', config_env('APP_COMPANY', 'AGAT GROUP Sp. z o.o.'));
}

if (!function_exists('config_default_app_url')) {
    function config_default_app_url(): string
    {
        if (!empty($_SERVER['HTTP_HOST'])) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' && $_SERVER['HTTPS'] !== '0') ? 'https' : 'http';
            $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
            $scriptDir = rtrim($scriptDir, '/');

            if ($scriptDir === '.' || $scriptDir === '/') {
                $scriptDir = '';
            }

            $segments = $scriptDir !== '' ? explode('/', ltrim($scriptDir, '/')) : [];

            if (!empty($segments)) {
                $lastSegment = end($segments);
                if (in_array($lastSegment, ['public', 'admin'], true)) {
                    array_pop($segments);
                }
            }

            $basePath = $segments ? '/' . implode('/', $segments) : '';
            $baseUrl = rtrim($scheme . '://' . $_SERVER['HTTP_HOST'] . $basePath, '/');

            return $baseUrl ?: 'http://localhost/BHP';
        }

        return 'http://localhost/BHP';
    }
}

if (!defined('APP_URL')) {
    define('APP_URL', rtrim(config_env('APP_URL', config_default_app_url()), '/'));
}

if (!defined('ADMIN_EMAIL')) {
    define('ADMIN_EMAIL', config_env('ADMIN_EMAIL', 'admin@example.com'));
}

if (!defined('MAIL_FROM')) {
    define('MAIL_FROM', config_env('MAIL_FROM', 'no-reply@example.com'));
}

if (!defined('ADMIN_DEFAULT_EMAIL')) {
    define('ADMIN_DEFAULT_EMAIL', config_env('ADMIN_DEFAULT_EMAIL', 'admin@agatgroup.pl'));
}

if (!defined('ADMIN_DEFAULT_PASSWORD_HASH')) {
    define('ADMIN_DEFAULT_PASSWORD_HASH', config_env('ADMIN_DEFAULT_PASSWORD_HASH', '$2y$12$C26FRtP5Lgovzg74Xo7BzuOf9GGvLusOnZRTfn9/VxtXG4.9Pd.7y'));
}
