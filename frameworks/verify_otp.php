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
    $_SESSION['authenticated'] = true;
    $_SESSION['otp_verified'] = true;

    // Clean up OTP data
    unset($_SESSION['otp']);
    unset($_SESSION['otp_expiry']);
    unset($_SESSION['otp_attempts']);

    echo json_encode(['success' => true, 'message' => 'OTP verified successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid OTP']);
}
}
?>
