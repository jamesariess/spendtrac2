<?php
header('Content-Type: application/json');
session_start();
include '../backend/conn.php';

function isLocalDevelopment(): bool
{
    $serverName = $_SERVER['SERVER_NAME'] ?? '';
    $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';

    return in_array($serverName, ['localhost', '127.0.0.1'], true)
        || in_array($remoteAddr, ['127.0.0.1', '::1'], true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if OTP session exists
    if (!isset($_SESSION['otp_email'])) {
        echo json_encode(['success' => false, 'message' => 'OTP session expired. Please login again.']);
        exit;
    }

    $email = $_SESSION['otp_email'];
    $otp = random_int(100000, 999999);

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
        $smtpUser = getenv('SPENDTRACK_SMTP_USER') ?: '';
        $smtpPass = getenv('SPENDTRACK_SMTP_PASS') ?: '';

        if ($smtpUser === '' || $smtpPass === '') {
            throw new Exception('SMTP credentials are not configured');
        }

        $mail->Username = $smtpUser;
        $mail->Password = $smtpPass;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom($smtpUser, 'SpendTrack');
        $mail->addAddress($email);

        $mail->Subject = 'Your OTP Code';
        $mail->Body = "Your OTP is: $otp";

        $mail->send();

        echo json_encode([
            'success' => true,
            'message' => 'OTP resent to email'
        ]);

    } catch (Exception $e) {
        error_log('Resend OTP Mail Error: ' . $e->getMessage());

        if (isLocalDevelopment()) {
            echo json_encode([
                'success' => true,
                'message' => 'OTP regenerated for local testing. Email is not configured.',
                'devOtp' => (string) $otp
            ]);
            exit;
        }

        echo json_encode([
            'success' => false,
            'message' => 'Failed to resend OTP. Please check SMTP settings.'
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
