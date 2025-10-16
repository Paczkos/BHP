<?php
require_once __DIR__ . '/session.php';

const AVAILABLE_LANGUAGES = ['pl', 'en', 'ru', 'id', 'vi'];

function detect_language(): string
{
    if (!empty($_GET['lang']) && in_array($_GET['lang'], AVAILABLE_LANGUAGES, true)) {
        $_SESSION['lang'] = $_GET['lang'];
    }

    if (!empty($_SESSION['lang'])) {
        return $_SESSION['lang'];
    }

    if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        if (in_array($browserLang, AVAILABLE_LANGUAGES, true)) {
            $_SESSION['lang'] = $browserLang;
            return $browserLang;
        }
    }

    $_SESSION['lang'] = 'pl';
    return 'pl';
}

function load_translations(string $lang): array
{
    $file = __DIR__ . '/../lang/' . $lang . '.json';
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $translations = json_decode($content, true);
        if (is_array($translations)) {
            return $translations;
        }
    }

    return [];
}

function t(string $key, array $placeholders = []): string
{
    static $translations = null;
    if ($translations === null) {
        $lang = detect_language();
        $translations = load_translations($lang);
    }

    $keys = explode('.', $key);
    $value = $translations;
    foreach ($keys as $segment) {
        if (!isset($value[$segment])) {
            $value = $key;
            break;
        }
        $value = $value[$segment];
    }

    if (is_string($value)) {
        foreach ($placeholders as $placeholder => $replacement) {
            $value = str_replace('{' . $placeholder . '}', $replacement, $value);
        }
        return $value;
    }

    return $key;
}
