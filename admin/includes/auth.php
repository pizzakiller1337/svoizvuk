<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../db.php';
$link = get_db();

function isAdmin(): bool {
    return isset($_SESSION['admin_id'], $_SESSION['role'])
        && $_SESSION['role'] === 'admin';
}

function requireAdmin(): void {
    if (!isAdmin()) {
        header('Location: /admin/login.php');
        exit;
    }
}

/**
 * Возвращает текущий CSRF-токен (создаёт при необходимости).
 * Используем для защиты GET-операций удаления и т.п. от подсунутых ссылок/картинок.
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_token'];
}

function csrf_check(string $token): bool {
    return !empty($_SESSION['csrf_token'])
        && is_string($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}
