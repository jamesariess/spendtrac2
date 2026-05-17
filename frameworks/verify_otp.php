<?php

session_start();
header('Content-Type: application/json');
include '../backend/conn.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){

$input = json_decode(file_get_contents('php://input'), true);
$enteredOtp = $input['otp'] ?? '';
session_regenerate_id(true);

if(!isset($_SESSION['otp'])){
    echo json_encode(['success' => false, 'message' => 'OTP has expired']);
    exit;
}
$_SESSION['otp_attempts'] = ($_SESSION['otp_attempts'] ?? 0) + 1;

if ($_SESSION['otp_attempts'] > 5) {
    echo json_encode(['success' => false, 'message' => 'Too many attempts']);
    exit;
}

if(time() > $_SESSION['otp_expiry']){
    unset($_SESSION['otp']);
    unset($_SESSION['otp_email']);
    unset($_SESSION['otp_expiry']);
    echo json_encode(['success' => false, 'message' => 'OTP has expired']);
    exit;
}

if($enteredOtp == $_SESSION['otp']){
    // OTP verified - set authenticated session
    session_regenerate_id(true);
    $_SESSION['authenticated'] = true;
    $_SESSION['otp_verified'] = true;
    $_SESSION['user_id'] = $_SESSION['otp_user_id'] ?? null;
    $_SESSION['user_email'] = $_SESSION['otp_email'] ?? null;

    if (!empty($_SESSION['remember_me']) && !empty($_SESSION['user_id'])) {
        $rememberToken = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare('UPDATE user SET remember_token = :remember_token WHERE user_id = :user_id');
        $stmt->execute([
            ':remember_token' => hash('sha256', $rememberToken),
            ':user_id' => (int) $_SESSION['user_id'],
        ]);
        setcookie('spendtrack_remember', $rememberToken, [
            'expires' => time() + 60 * 60 * 24 * 30,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    // Clean up OTP data
    unset($_SESSION['otp']);
    unset($_SESSION['otp_user_id']);
    unset($_SESSION['otp_email']);
    unset($_SESSION['otp_expiry']);
    unset($_SESSION['otp_attempts']);
    unset($_SESSION['remember_me']);

    echo json_encode(['success' => true, 'message' => 'OTP verified successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid OTP']);
}
}
?>
