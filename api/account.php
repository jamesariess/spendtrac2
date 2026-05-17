<?php

declare(strict_types=1);

require_once '../backend/conn.php';
require_once '../backend/security.php';

$action = $_GET['action'] ?? '';
$input = require_post_json();

function token_response(string $message, string $token): void
{
    $payload = ['success' => true, 'message' => $message];
    $serverName = $_SERVER['SERVER_NAME'] ?? '';
    if (in_array($serverName, ['localhost', '127.0.0.1'], true)) {
        $payload['devToken'] = $token;
    }
    json_response($payload);
}

try {
    if ($action === 'request_password_reset') {
        $email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
        if (!$email) {
            json_response(['success' => false, 'message' => 'Enter a valid email'], 422);
        }

        $token = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare('REPLACE INTO password_reset_tokens (email, token_hash, created_at) VALUES (:email, :token_hash, NOW())');
        $stmt->execute([':email' => $email, ':token_hash' => hash('sha256', $token)]);
        token_response('Password reset token generated. Send it by email in production.', $token);
    }

    if ($action === 'reset_password') {
        $email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $token = (string) ($input['token'] ?? '');
        $password = (string) ($input['password'] ?? '');

        if (!$email || strlen($password) < 8) {
            json_response(['success' => false, 'message' => 'Invalid reset details'], 422);
        }

        $stmt = $pdo->prepare('SELECT token_hash, created_at FROM password_reset_tokens WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || !hash_equals($row['token_hash'], hash('sha256', $token)) || strtotime($row['created_at']) < time() - 3600) {
            json_response(['success' => false, 'message' => 'Reset token is invalid or expired'], 422);
        }

        $pdo->prepare('UPDATE user SET password = :password WHERE email = :email')->execute([
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':email' => $email,
        ]);
        $pdo->prepare('DELETE FROM password_reset_tokens WHERE email = :email')->execute([':email' => $email]);
        json_response(['success' => true, 'message' => 'Password reset successfully']);
    }

    if ($action === 'send_email_verification') {
        $userId = require_auth();
        $token = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare('REPLACE INTO email_verification_tokens (user_id, token_hash, created_at) VALUES (:user_id, :token_hash, NOW())');
        $stmt->execute([':user_id' => $userId, ':token_hash' => hash('sha256', $token)]);
        token_response('Verification token generated. Send it by email in production.', $token);
    }

    if ($action === 'verify_email') {
        $userId = require_auth();
        $token = (string) ($input['token'] ?? '');
        $stmt = $pdo->prepare('SELECT token_hash FROM email_verification_tokens WHERE user_id = :user_id LIMIT 1');
        $stmt->execute([':user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || !hash_equals($row['token_hash'], hash('sha256', $token))) {
            json_response(['success' => false, 'message' => 'Invalid verification token'], 422);
        }

        $pdo->prepare('UPDATE user SET email_verified_at = NOW() WHERE user_id = :user_id')->execute([':user_id' => $userId]);
        $pdo->prepare('DELETE FROM email_verification_tokens WHERE user_id = :user_id')->execute([':user_id' => $userId]);
        json_response(['success' => true, 'message' => 'Email verified']);
    }

    json_response(['success' => false, 'message' => 'Unknown account action'], 404);
} catch (PDOException $e) {
    error_log('Account API Error: ' . $e->getMessage());
    json_response(['success' => false, 'message' => 'Account service error'], 500);
}

