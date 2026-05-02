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
    $stmt = $pdo->prepare('SELECT * FROM user WHERE email = :email LIMIT 1');
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
        $mail->Subject = 'Your SpendTrack Login Code';

        // Beautiful HTML Email Template
        $htmlBody = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #6366f1, #4f46e5); color: white; padding: 30px 20px; text-align: center; }
                .content { padding: 40px 30px; text-align: center; }
                .otp-code { 
                    font-size: 32px; 
                    font-weight: bold; 
                    letter-spacing: 8px; 
                    color: #4f46e5; 
                    background: #f8fafc; 
                    padding: 20px; 
                    border-radius: 8px; 
                    margin: 20px 0;
                    border: 2px dashed #6366f1;
                }
                .footer { 
                    background: #f8fafc; 
                    padding: 20px; 
                    text-align: center; 
                    color: #64748b; 
                    font-size: 14px;
                }
                .btn { 
                    display: inline-block; 
                    background: #4f46e5; 
                    color: white; 
                    padding: 12px 30px; 
                    text-decoration: none; 
                    border-radius: 6px; 
                    margin-top: 20px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>SpendTrack</h1>
                    <p>Secure Login Verification</p>
                </div>
                <div class="content">
                    <h2>Hello there!</h2>
                    <p>Your login verification code is:</p>
                    <div class="otp-code">' . $otp . '</div>
                    <p>This code will expire in <strong>5 minutes</strong>.</p>
                    <p><small>For security reasons, please do not share this code with anyone.</small></p>
                </div>
                <div class="footer">
                    <p>© ' . date('Y') . ' SpendTrack Finance. All rights reserved.</p>
                    <p>This is an automated message. If you did not request this code, please ignore it.</p>
                </div>
            </div>
        </body>
        </html>';

        $mail->isHTML(true);
        $mail->Body = $htmlBody;
        $mail->AltBody = "Your SpendTrack OTP is: $otp (expires in 5 minutes)";

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