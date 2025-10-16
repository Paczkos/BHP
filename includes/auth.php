<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';

function find_user_by_email(string $email): ?array
{
    $pdo = get_db_connection();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function register_user(array $data): bool
{
    $pdo = get_db_connection();
    $stmt = $pdo->prepare('INSERT INTO users (first_name, last_name, passport_or_pesel, email, password_hash, language, created_at) VALUES (:first_name, :last_name, :document, :email, :password_hash, :language, NOW())');

    return $stmt->execute([
        'first_name' => $data['first_name'],
        'last_name' => $data['last_name'],
        'document' => $data['passport_or_pesel'],
        'email' => strtolower($data['email']),
        'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
        'language' => $data['language'] ?? 'pl',
    ]);
}

function authenticate_user(string $email, string $password): bool
{
    $user = find_user_by_email(strtolower($email));
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user'] = $user;
        $_SESSION['lang'] = $user['language'] ?? 'pl';
        return true;
    }
    return false;
}

function authenticate_admin(string $email, string $password): bool
{
    $pdo = get_db_connection();
    $stmt = $pdo->prepare('SELECT * FROM admins WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => strtolower($email)]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin'] = $admin;
        return true;
    }
    return false;
}
