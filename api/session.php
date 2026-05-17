<?php

declare(strict_types=1);

require_once '../backend/conn.php';
require_once '../backend/security.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$user = current_user($pdo);

json_response([
    'success' => true,
    'authenticated' => !empty($_SESSION['authenticated']) && $user !== null,
    'csrfToken' => $_SESSION['csrf_token'],
    'user' => $user,
]);

