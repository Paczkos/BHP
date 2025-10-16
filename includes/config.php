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
    define('APP_NAME', config_env('APP_NAME', 'Platforma BHP'));
}

if (!defined('APP_URL')) {
    define('APP_URL', config_env('APP_URL', 'http://localhost/BHP/public'));
}

if (!defined('ADMIN_EMAIL')) {
    define('ADMIN_EMAIL', config_env('ADMIN_EMAIL', 'admin@example.com'));
}

if (!defined('MAIL_FROM')) {
    define('MAIL_FROM', config_env('MAIL_FROM', 'no-reply@example.com'));
}
