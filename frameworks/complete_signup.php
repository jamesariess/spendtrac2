<?php
header('Content-Type: application/json');
session_start();
include '../backend/conn.php';

// This endpoint is called after OTP verification during signup
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verify we're in signup mode and have the required data
        if (!isset($_SESSION['signup_mode']) || !$_SESSION['signup_mode']) {
            echo json_encode(['success' => false, 'message' => 'Invalid signup session']);
            exit;
        }

        if (!isset($_SESSION['signup_email']) || !isset($_SESSION['signup_password'])) {
            echo json_encode(['success' => false, 'message' => 'Signup data missing. Please try again.']);
            exit;
        }

        if (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified']) {
            echo json_encode(['success' => false, 'message' => 'OTP not verified']);
            exit;
        }

        $email = $_SESSION['signup_email'];
        $hashedPassword = $_SESSION['signup_password'];

        // Double-check email doesn't already exist (prevents race condition)
        $checkStmt = $pdo->prepare('SELECT user_id FROM user WHERE email = :email');
        $checkStmt->execute(['email' => $email]);
        if ($checkStmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email already registered']);
            exit;
        }

        // Insert user into database
        $stmt = $pdo->prepare('INSERT INTO user (email, password, created_at) VALUES (:email, :password, :created_at)');
        $stmt->execute([
            ':email' => $email,
            ':password' => $hashedPassword,
            ':created_at' => date('Y-m-d')
        ]);

        $userId = $pdo->lastInsertId();

        // Clean up signup session data
        unset($_SESSION['signup_email']);
        unset($_SESSION['signup_password']);
        unset($_SESSION['signup_timestamp']);
        unset($_SESSION['signup_mode']);
        unset($_SESSION['otp']);
        unset($_SESSION['otp_email']);
        unset($_SESSION['otp_expiry']);
        unset($_SESSION['otp_verified']);
        unset($_SESSION['otp_attempts']);

        echo json_encode([
            'success' => true,
            'message' => 'Account created successfully! Redirecting to login...',
            'user_id' => $userId
        ]);

    } catch (PDOException $e) {
        // Handle unique constraint violation (email already exists)
        if (strpos($e->getMessage(), 'UNIQUE') !== false || strpos($e->getMessage(), 'Duplicate') !== false) {
            echo json_encode(['success' => false, 'message' => 'Email already registered']);
        } else {
            error_log('Database error in complete_signup.php: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
        }
    } catch (Exception $e) {
        error_log('Error in complete_signup.php: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
