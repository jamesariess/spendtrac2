<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function bootstrap_remembered_user(PDO $pdo): void
{
    if (!empty($_SESSION['authenticated']) || empty($_COOKIE['spendtrack_remember'])) {
        return;
    }

    $tokenHash = hash('sha256', $_COOKIE['spendtrack_remember']);
    $stmt = $pdo->prepare('SELECT user_id, email FROM user WHERE remember_token = :remember_token LIMIT 1');
    $stmt->execute([':remember_token' => $tokenHash]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        session_regenerate_id(true);
        $_SESSION['authenticated'] = true;
        $_SESSION['user_id'] = (int) $user['user_id'];
        $_SESSION['user_email'] = $user['email'];
    }
}

function json_response(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

function require_post_json(): array
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['success' => false, 'message' => 'Method not allowed'], 405);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        json_response(['success' => false, 'message' => 'Invalid JSON body'], 400);
    }

    return $input;
}

function require_auth(): int
{
    global $pdo;

    if (isset($pdo) && $pdo instanceof PDO) {
        bootstrap_remembered_user($pdo);
    }

    if (empty($_SESSION['authenticated']) || empty($_SESSION['user_id'])) {
        json_response(['success' => false, 'message' => 'Authentication required'], 401);
    }

    return (int) $_SESSION['user_id'];
}

function clean_string(?string $value, int $maxLength): string
{
    $value = trim((string) $value);
    $value = strip_tags($value);

    if (strlen($value) > $maxLength) {
        $value = substr($value, 0, $maxLength);
    }

    return $value;
}

function validate_csrf_token(?string $token = null): void
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        return;
    }

    $incomingToken = $token ?: ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!hash_equals($_SESSION['csrf_token'], $incomingToken)) {
        json_response(['success' => false, 'message' => 'Invalid security token'], 419);
    }
}

function current_user(PDO $pdo): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT user_id, email, role, display_name, currency, locale, timezone, email_verified_at FROM user WHERE user_id = :user_id LIMIT 1');
    $stmt->execute([':user_id' => (int) $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ?: null;
}
