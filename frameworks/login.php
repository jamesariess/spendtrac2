<?php
header('Content-Type: application/json');
session_start();
include '../backend/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
        exit;
    }

    try {
        $stmt = $pdo->prepare('SELECT * FROM user WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Generate OTP
            $otp = rand(100000, 999999);

            // Store in session
            $_SESSION['otp'] = $otp;
            $_SESSION['otp_email'] = $email;
            $_SESSION['otp_expiry'] = time() + 300; // 5 minutes
            $_SESSION['otp_attempts'] = 0;
            $_SESSION['user_id'] = $user['user_id'];

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

                $mail->Subject = 'Your OTP Code';
                $mail->Body = "Your OTP is: $otp\n\nThis code will expire in 5 minutes.";

                $mail->send();

                echo json_encode([
                    'success' => true,
                    'message' => 'OTP sent to email'
                ]);

            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to send OTP. Please try again.'
                ]);
            }

        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
    }
} else {
    // GET: Simple redirect
    header('Location: ../auth/login.html');
    exit;
}
?>

