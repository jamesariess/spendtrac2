<?php
header('Content-Type: application/json');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $userAnswer = $input['answer'] ?? '';

    // Check if CAPTCHA exists
    if (!isset($_SESSION['captcha_answer'])) {
        echo json_encode(['success' => false, 'message' => 'CAPTCHA expired. Please refresh.']);
        exit;
    }

    // Check if CAPTCHA expired (5 minutes)
    if (time() - $_SESSION['captcha_created'] > 300) {
        unset($_SESSION['captcha_answer']);
        unset($_SESSION['captcha_created']);
        echo json_encode(['success' => false, 'message' => 'CAPTCHA expired. Please refresh.']);
        exit;
    }

    // Verify answer
    if ((string)$userAnswer === $_SESSION['captcha_answer']) {
        // Mark as verified and clean up
        $_SESSION['captcha_verified'] = true;
        unset($_SESSION['captcha_answer']);
        unset($_SESSION['captcha_created']);

        echo json_encode(['success' => true, 'message' => 'CAPTCHA verified']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Incorrect answer. Please try again.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
