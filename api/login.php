<?php
header('Content-Type: application/json');
session_start();

include_once '../backend/conn.php';

// ✅ Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

// ✅ Validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

// ✅ Query
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];

    echo json_encode([
        'success' => true,
        'message' => 'Login successful'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email or password'
    ]);
}