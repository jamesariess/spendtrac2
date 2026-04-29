<?php
header('Content-Type: application/json');
session_start();
include '../backend/conn.php';

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
        $errors[] = 'Password must contain at least one special character';
    }

    return $errors;
}
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

        $email           = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password        = $input['password'] ?? '';
        $confirmPassword = $input['confirmPassword'] ?? '';

        // === VALIDATIONS ===
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
            exit;
        }

        if ($password !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
            exit;
        }
        if (!checkRateLimit()) {
            echo json_encode(['success' => false, 'message' => 'Too many signup attempts. Please try again later.']);
            exit;
        }

        $passwordErrors = validatePasswordStrength($password);
        if (!empty($passwordErrors)) {
            echo json_encode(['success' => false, 'message' => $passwordErrors[0]]);
            exit;
        }

        // Check if email already exists
        $stmt = $pdo->prepare('SELECT user_id FROM user WHERE email = :email');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'This email is already registered']);
            exit;
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert into database
        $stmt = $pdo->prepare('INSERT INTO user (email, password, created_at) VALUES (:email, :password, NOW())');
        $stmt->execute([
            ':email'    => $email,
            ':password' => $hashedPassword
        ]);

        // Success - No OTP, direct success
        echo json_encode([
            'success' => true,
            'message' => 'Account created successfully! Redirecting to login...'
        ]);

    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate') !== false || strpos($e->getMessage(), 'unique') !== false) {
            echo json_encode(['success' => false, 'message' => 'This email is already registered']);
        } else {
            error_log('Signup DB Error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to create account. Please try again.']);
        }
    } catch (Exception $e) {
        error_log('Signup Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>