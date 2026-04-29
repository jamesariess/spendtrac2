<?php
header('Content-Type: application/json');
session_start();
include '../backend/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if OTP session exists
    if (!isset($_SESSION['otp_email'])) {
        echo json_encode(['success' => false, 'message' => 'OTP session expired. Please login again.']);
        exit;
    }

    $email = $_SESSION['otp_email'];
    $otp = rand(100000, 999999);

    // Update session with new OTP
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_expiry'] = time() + 300; // 5 minutes
    $_SESSION['otp_attempts'] = 0;

    require '../vendor/autoload.php';
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your_email@gmail.com';
        $mail->Password = 'your_app_password';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('your_email@gmail.com', 'SpendTrack');
        $mail->addAddress($email);

        $mail->Subject = 'Your OTP Code';
        $mail->Body = "Your OTP is: $otp";

        $mail->send();

        echo json_encode([
            'success' => true,
            'message' => 'OTP resent to email'
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to resend OTP'
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
