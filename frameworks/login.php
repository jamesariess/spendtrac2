<?php
// Set headers FIRST - before any output
header('Content-Type: application/json');
error_reporting(0);           // Temporarily hide warnings (for production)
ini_set('display_errors', 0);

session_start();
include '../backend/conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../auth/login.html');
    exit;
}

// Get input
$input = json_decode(file_get_contents('php://input'), true);
$email    = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

// Basic validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT user_id, email, password FROM user WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        
        // Generate OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store in session
        $_SESSION['otp']          = $otp;
        $_SESSION['otp_email']    = $email;
        $_SESSION['otp_user_id']  = $user['user_id'];
        $_SESSION['otp_expiry']   = time() + 300;
        $_SESSION['otp_attempts'] = 0;
        $_SESSION['login_mode']   = true;

        // Send OTP Email
        require_once '../vendor/autoload.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'slatetransportsystem@gmail.com';
        $mail->Password   = 'mfkkigrgxtoascov';
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('slatetransportsystem@gmail.com', 'SpendTrack');
        $mail->addAddress($email);
        $mail->Subject = 'SpendTrack - Login Verification Code';
        $mail->Body    = "Hello,\n\nYour login OTP code is: $otp\n\nThis code will expire in 5 minutes.\n\nIf you did not request this, please ignore this email.";

        $mail->send();

        echo json_encode([
            'success' => true,
            'message' => 'OTP sent to your email. Please verify.'
        ]);

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password'
        ]);
    }

} catch (PDOException $e) {
    error_log('Login DB Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error. Please try again later.'
    ]);
} catch (Exception $e) {
    error_log('Login Mail Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send OTP. Please try again.'
    ]);
}
?>