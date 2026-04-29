<?php
header('Content-Type: application/json');
session_start();
include '../backend/conn.php';

// Helper function to validate password strength
function validatePasswordStrength($password) {
    $errors = [];

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }
    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
        $errors[] = 'Password must contain at least one special character (!@#$%^&*)';
    }

    return $errors;
}

// Rate limiting: Check IP-based signup attempts (5 per hour)
function checkRateLimit() {
    $ip = $_SERVER['REMOTE_ADDR'];
    $cache_key = "signup_attempts_" . md5($ip);

    if (!isset($_SESSION[$cache_key])) {
        $_SESSION[$cache_key] = [];
    }

    // Remove old attempts (older than 1 hour)
    $_SESSION[$cache_key] = array_filter($_SESSION[$cache_key], function($timestamp) {
        return time() - $timestamp < 3600;
    });

    if (count($_SESSION[$cache_key]) >= 5) {
        return false; // Rate limit exceeded
    }

    // Log this attempt
    $_SESSION[$cache_key][] = time();
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);

        $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $input['password'] ?? '';
        $confirmPassword = $input['confirmPassword'] ?? '';

        // Validate CAPTCHA was verified
        if (!isset($_SESSION['captcha_verified']) || !$_SESSION['captcha_verified']) {
            echo json_encode(['success' => false, 'message' => 'Please complete the CAPTCHA']);
            exit;
        }

        // Rate limiting
        if (!checkRateLimit()) {
            echo json_encode(['success' => false, 'message' => 'Too many signup attempts. Please try again later.']);
            exit;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
            exit;
        }

        // Check password match
        if ($password !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
            exit;
        }

        // Validate password strength
        $passwordErrors = validatePasswordStrength($password);
        if (!empty($passwordErrors)) {
            echo json_encode(['success' => false, 'message' => $passwordErrors[0]]);
            exit;
        }

        // Check if email already exists
        $stmt = $pdo->prepare('SELECT user_id FROM user WHERE email = :email');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'This email is already registered. Please login or use a different email.']);
            exit;
        }

        // Hash password securely using bcrypt
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Generate 6-digit OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store signup data in session (temporary until OTP verified)
        $_SESSION['signup_email'] = $email;
        $_SESSION['signup_password'] = $hashedPassword;
        $_SESSION['signup_timestamp'] = time();

        // Setup OTP
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_email'] = $email;
        $_SESSION['otp_expiry'] = time() + 300; // 5 minutes
        $_SESSION['otp_attempts'] = 0;
        $_SESSION['signup_mode'] = true; // Flag to indicate signup flow

        // Send OTP via email
        require '../vendor/autoload.php';
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'slatetransportsystem@gmail.com';
            $mail->Password = 'mfkkigrgxtoascov';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('slatetransportsystem@gmail.com', 'SpendTrack');
            $mail->addAddress($email);

            $mail->Subject = 'Verify Your Email - SpendTrack';
            $mail->Body = "Welcome to SpendTrack!\n\n"
                        . "Your OTP verification code is: $otp\n\n"
                        . "This code will expire in 5 minutes.\n\n"
                        . "If you didn't create this account, please ignore this email.";

            $mail->send();

            // Clear CAPTCHA verification flag for security
            unset($_SESSION['captcha_verified']);

            echo json_encode([
                'success' => true,
                'message' => 'Signup successful! Check your email for OTP.'
            ]);

        } catch (Exception $e) {
            // Clear session data on email failure
            unset($_SESSION['signup_email']);
            unset($_SESSION['signup_password']);
            unset($_SESSION['signup_timestamp']);
            unset($_SESSION['otp']);
            unset($_SESSION['otp_email']);
            unset($_SESSION['otp_expiry']);
            unset($_SESSION['signup_mode']);

            echo json_encode([
                'success' => false,
                'message' => 'Failed to send verification email. Please try again.'
            ]);
        }

    } catch (Exception $e) {
        error_log('Error in signup.php: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred. Please try again.'
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
