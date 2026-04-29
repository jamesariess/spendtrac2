<?php
header('Content-Type: application/json');
session_start();

// Generate random math CAPTCHA
function generateCaptcha() {
    $num1 = rand(1, 20);
    $num2 = rand(1, 20);
    $operator = rand(0, 1) === 0 ? '+' : '-';

    if ($operator === '+') {
        $answer = $num1 + $num2;
    } else {
        $answer = $num1 - $num2;
        // Ensure non-negative result
        if ($answer < 0) {
            $answer = abs($answer);
            $question = "$num2 - $num1"; // Swap order
        } else {
            $question = "$num1 - $num2";
        }
    }

    if ($operator === '+') {
        $question = "$num1 + $num2";
    }

    // Store answer in session
    $_SESSION['captcha_answer'] = (string)$answer;
    $_SESSION['captcha_created'] = time();

    return json_encode([
        'success' => true,
        'question' => $question,
        'expires_in' => 300 // 5 minutes
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo generateCaptcha();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
