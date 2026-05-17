<?php

declare(strict_types=1);

require_once __DIR__ . '/../backend/conn.php';
require_once __DIR__ . '/../vendor/autoload.php';

function smtp_configured(): bool
{
    return (getenv('SPENDTRACK_SMTP_USER') ?: '') !== '' && (getenv('SPENDTRACK_SMTP_PASS') ?: '') !== '';
}

function send_reminder_email(string $to, array $expense): bool
{
    if (!smtp_configured()) {
        error_log('SpendTrack reminder not emailed because SMTP is not configured. Expense #' . $expense['transaction_id']);
        return false;
    }

    $smtpUser = getenv('SPENDTRACK_SMTP_USER');
    $smtpPass = getenv('SPENDTRACK_SMTP_PASS');
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $smtpUser;
    $mail->Password = $smtpPass;
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->setFrom($smtpUser, 'SpendTrack');
    $mail->addAddress($to);
    $mail->isHTML(true);
    $mail->Subject = 'Payment reminder: ' . $expense['description'];

    $amount = number_format((float) $expense['amount'], 2);
    $mail->Body = '
        <h2>SpendTrack payment reminder</h2>
        <p>This expense is due soon:</p>
        <ul>
            <li><strong>Description:</strong> ' . htmlspecialchars($expense['description'], ENT_QUOTES, 'UTF-8') . '</li>
            <li><strong>Amount:</strong> ' . htmlspecialchars($expense['currency'], ENT_QUOTES, 'UTF-8') . ' ' . $amount . '</li>
            <li><strong>Due date:</strong> ' . htmlspecialchars($expense['due_date'], ENT_QUOTES, 'UTF-8') . '</li>
            <li><strong>Category:</strong> ' . htmlspecialchars($expense['category'], ENT_QUOTES, 'UTF-8') . '</li>
        </ul>
        <p>Open SpendTrack to mark it as paid after payment.</p>';
    $mail->AltBody = "Payment reminder: {$expense['description']} {$expense['currency']} {$amount}, due {$expense['due_date']}.";

    $mail->send();
    return true;
}

$stmt = $pdo->prepare(
    'SELECT t.*, u.email
     FROM transactions t
     JOIN user u ON u.user_id = t.user_id
     WHERE t.type = "expense"
       AND t.reminder_enabled = 1
       AND t.payment_status <> "paid"
       AND t.due_date IS NOT NULL
       AND DATE_SUB(t.due_date, INTERVAL t.reminder_days_before DAY) <= CURDATE()
       AND t.due_date >= CURDATE()
       AND (t.reminder_last_sent_at IS NULL OR DATE(t.reminder_last_sent_at) < CURDATE())
     ORDER BY t.due_date ASC
     LIMIT 100'
);
$stmt->execute();
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sent = 0;
$logged = 0;

foreach ($expenses as $expense) {
    $emailed = false;
    try {
        $emailed = send_reminder_email($expense['email'], $expense);
    } catch (Throwable $e) {
        error_log('SpendTrack reminder email failed: ' . $e->getMessage());
    }

    $title = 'Expense payment reminder';
    $body = sprintf(
        '%s (%s %s) is due on %s.',
        $expense['description'],
        $expense['currency'],
        number_format((float) $expense['amount'], 2),
        $expense['due_date']
    );

    $pdo->prepare(
        'INSERT INTO notifications (user_id, title, body, type) VALUES (:user_id, :title, :body, :type)'
    )->execute([
        ':user_id' => $expense['user_id'],
        ':title' => $title,
        ':body' => $body,
        ':type' => 'expense_reminder',
    ]);

    $pdo->prepare(
        'UPDATE transactions SET reminder_last_sent_at = NOW() WHERE transaction_id = :transaction_id'
    )->execute([':transaction_id' => $expense['transaction_id']]);

    $sent += $emailed ? 1 : 0;
    $logged++;
}

echo json_encode([
    'success' => true,
    'matched' => count($expenses),
    'emails_sent' => $sent,
    'notifications_created' => $logged,
]);

