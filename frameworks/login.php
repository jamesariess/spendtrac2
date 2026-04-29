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

    $stmt = $pdo->prepare('SELECT * FROM user WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {

      $otp = rand(100000, 999999);

      $_SESSION['otp'] = $otp;
      $_SESSION['otp_email'] = $email;
      $_SESSION['otp_expiry'] = time() + 300;
       $_SESSION['user_id'] = $user['user_id'];

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
            'message' => 'OTP sent to email'
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'OTP sending failed'
        ]);
    }
       
        echo json_encode(['success' => true, 'message' => 'Login successful']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    }
} else {
    // GET: Simple redirect
    header('Location: ../auth/login.html');
    exit;
}
?>
