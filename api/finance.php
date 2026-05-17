<?php

declare(strict_types=1);

require_once '../backend/conn.php';
require_once '../backend/security.php';

$userId = require_auth();
$action = $_GET['action'] ?? '';

function valid_currency(string $currency): string
{
    $currency = strtoupper(trim($currency));
    return preg_match('/^[A-Z]{3}$/', $currency) ? $currency : 'USD';
}

function valid_date(?string $date, ?string $fallback = null): string
{
    $date = $date ?: $fallback ?: date('Y-m-d');
    $parsed = DateTime::createFromFormat('Y-m-d', $date);

    if (!$parsed || $parsed->format('Y-m-d') !== $date) {
        json_response(['success' => false, 'message' => 'Invalid date'], 422);
    }

    return $date;
}

function get_overview(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare(
        'SELECT transaction_id, type, description, amount, currency, category, payment_method, notes,
                receipt_path, is_recurring, recurring_interval, transaction_date
         FROM transactions
         WHERE user_id = :user_id
         ORDER BY transaction_date DESC, transaction_id DESC
         LIMIT 500'
    );
    $stmt->execute([':user_id' => $userId]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $monthStart = date('Y-m-01');
    $summary = [
        'income' => 0,
        'expenses' => 0,
        'monthlyIncome' => 0,
        'monthlyExpenses' => 0,
        'balance' => 0,
        'savings' => 0,
    ];

    $categories = [];
    $monthly = [];

    foreach ($transactions as $transaction) {
        $amount = (float) $transaction['amount'];
        $isIncome = $transaction['type'] === 'income';
        $date = $transaction['transaction_date'];
        $month = substr($date, 0, 7);

        if (!isset($monthly[$month])) {
            $monthly[$month] = ['month' => $month, 'income' => 0, 'expenses' => 0];
        }

        if ($isIncome) {
            $summary['income'] += $amount;
            $monthly[$month]['income'] += $amount;
            if ($date >= $monthStart) {
                $summary['monthlyIncome'] += $amount;
            }
        } else {
            $summary['expenses'] += $amount;
            $monthly[$month]['expenses'] += $amount;
            $categories[$transaction['category']] = ($categories[$transaction['category']] ?? 0) + $amount;
            if ($date >= $monthStart) {
                $summary['monthlyExpenses'] += $amount;
            }
        }
    }

    $summary['balance'] = $summary['income'] - $summary['expenses'];
    $summary['savings'] = max(0, $summary['monthlyIncome'] - $summary['monthlyExpenses']);

    ksort($monthly);
    $monthly = array_slice(array_values($monthly), -12);

    $budgetsStmt = $pdo->prepare('SELECT * FROM budgets WHERE user_id = :user_id ORDER BY category');
    $budgetsStmt->execute([':user_id' => $userId]);

    $goalsStmt = $pdo->prepare('SELECT * FROM goals WHERE user_id = :user_id ORDER BY status, target_date IS NULL, target_date');
    $goalsStmt->execute([':user_id' => $userId]);

    $notificationsStmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = :user_id OR user_id IS NULL ORDER BY created_at DESC LIMIT 30');
    $notificationsStmt->execute([':user_id' => $userId]);

    return [
        'summary' => $summary,
        'transactions' => $transactions,
        'budgets' => $budgetsStmt->fetchAll(PDO::FETCH_ASSOC),
        'goals' => $goalsStmt->fetchAll(PDO::FETCH_ASSOC),
        'notifications' => $notificationsStmt->fetchAll(PDO::FETCH_ASSOC),
        'categoryTotals' => $categories,
        'monthlyTotals' => $monthly,
    ];
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if ($action === 'overview' || $action === '') {
            json_response(['success' => true] + get_overview($pdo, $userId));
        }

        if ($action === 'admin') {
            $user = current_user($pdo);
            if (($user['role'] ?? 'user') !== 'admin') {
                json_response(['success' => false, 'message' => 'Admin access required'], 403);
            }

            $users = $pdo->query('SELECT user_id, email, role, currency, locale, created_at FROM user ORDER BY created_at DESC LIMIT 200')->fetchAll(PDO::FETCH_ASSOC);
            $totals = $pdo->query(
                'SELECT COUNT(*) AS users,
                        (SELECT COUNT(*) FROM transactions) AS transactions,
                        (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE type = "expense") AS expenses,
                        (SELECT COUNT(*) FROM subscriptions WHERE status IN ("active", "trialing")) AS active_subscriptions
                 FROM user'
            )->fetch(PDO::FETCH_ASSOC);

            json_response(['success' => true, 'users' => $users, 'totals' => $totals]);
        }

        json_response(['success' => false, 'message' => 'Unknown action'], 404);
    }

    $input = require_post_json();
    validate_csrf_token($input['csrfToken'] ?? null);

    if ($action === 'save_transaction') {
        $id = (int) ($input['transaction_id'] ?? $input['id'] ?? 0);
        $type = $input['type'] ?? '';
        $description = clean_string($input['description'] ?? '', 120);
        $amount = filter_var($input['amount'] ?? null, FILTER_VALIDATE_FLOAT);
        $category = clean_string($input['category'] ?? '', 40);
        $date = valid_date($input['transaction_date'] ?? $input['date'] ?? null);
        $currency = valid_currency($input['currency'] ?? 'USD');
        $paymentMethod = clean_string($input['payment_method'] ?? '', 40);
        $notes = clean_string($input['notes'] ?? '', 1000);
        $isRecurring = !empty($input['is_recurring']) ? 1 : 0;
        $recurringInterval = $isRecurring ? ($input['recurring_interval'] ?? 'monthly') : null;

        if (!in_array($type, ['income', 'expense'], true)) {
            json_response(['success' => false, 'message' => 'Invalid transaction type'], 422);
        }
        if ($description === '' || $category === '' || $amount === false || $amount <= 0) {
            json_response(['success' => false, 'message' => 'Please complete all required fields'], 422);
        }
        if ($recurringInterval && !in_array($recurringInterval, ['weekly', 'monthly', 'yearly'], true)) {
            json_response(['success' => false, 'message' => 'Invalid recurring interval'], 422);
        }

        $params = [
            ':user_id' => $userId,
            ':type' => $type,
            ':description' => $description,
            ':amount' => $amount,
            ':currency' => $currency,
            ':category' => $category,
            ':payment_method' => $paymentMethod ?: null,
            ':notes' => $notes ?: null,
            ':is_recurring' => $isRecurring,
            ':recurring_interval' => $recurringInterval,
            ':transaction_date' => $date,
        ];

        if ($id > 0) {
            $params[':transaction_id'] = $id;
            $stmt = $pdo->prepare(
                'UPDATE transactions
                 SET type = :type, description = :description, amount = :amount, currency = :currency,
                     category = :category, payment_method = :payment_method, notes = :notes,
                     is_recurring = :is_recurring, recurring_interval = :recurring_interval,
                     transaction_date = :transaction_date
                 WHERE transaction_id = :transaction_id AND user_id = :user_id'
            );
            $stmt->execute($params);
            json_response(['success' => true, 'message' => 'Transaction updated']);
        }

        $stmt = $pdo->prepare(
            'INSERT INTO transactions
                (user_id, type, description, amount, currency, category, payment_method, notes, is_recurring, recurring_interval, transaction_date)
             VALUES
                (:user_id, :type, :description, :amount, :currency, :category, :payment_method, :notes, :is_recurring, :recurring_interval, :transaction_date)'
        );
        $stmt->execute($params);
        json_response(['success' => true, 'message' => 'Transaction saved', 'transaction_id' => (int) $pdo->lastInsertId()]);
    }

    if ($action === 'delete_transaction') {
        $id = (int) ($input['transaction_id'] ?? $input['id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM transactions WHERE transaction_id = :transaction_id AND user_id = :user_id');
        $stmt->execute([':transaction_id' => $id, ':user_id' => $userId]);
        json_response(['success' => true, 'message' => 'Transaction deleted']);
    }

    if ($action === 'save_budget') {
        $stmt = $pdo->prepare(
            'INSERT INTO budgets (user_id, category, amount, currency, period, starts_on)
             VALUES (:user_id, :category, :amount, :currency, :period, :starts_on)'
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':category' => clean_string($input['category'] ?? '', 40),
            ':amount' => max(0, (float) ($input['amount'] ?? 0)),
            ':currency' => valid_currency($input['currency'] ?? 'USD'),
            ':period' => in_array(($input['period'] ?? ''), ['monthly', 'quarterly', 'yearly'], true) ? $input['period'] : 'monthly',
            ':starts_on' => valid_date($input['starts_on'] ?? null, date('Y-m-01')),
        ]);
        json_response(['success' => true, 'message' => 'Budget saved']);
    }

    if ($action === 'save_goal') {
        $stmt = $pdo->prepare(
            'INSERT INTO goals (user_id, name, target_amount, saved_amount, currency, target_date)
             VALUES (:user_id, :name, :target_amount, :saved_amount, :currency, :target_date)'
        );
        $targetDate = !empty($input['target_date']) ? valid_date($input['target_date']) : null;
        $stmt->execute([
            ':user_id' => $userId,
            ':name' => clean_string($input['name'] ?? '', 120),
            ':target_amount' => max(0, (float) ($input['target_amount'] ?? 0)),
            ':saved_amount' => max(0, (float) ($input['saved_amount'] ?? 0)),
            ':currency' => valid_currency($input['currency'] ?? 'USD'),
            ':target_date' => $targetDate,
        ]);
        json_response(['success' => true, 'message' => 'Goal saved']);
    }

    if ($action === 'save_profile') {
        $stmt = $pdo->prepare(
            'UPDATE user SET display_name = :display_name, currency = :currency, locale = :locale, timezone = :timezone WHERE user_id = :user_id'
        );
        $stmt->execute([
            ':display_name' => clean_string($input['display_name'] ?? '', 120),
            ':currency' => valid_currency($input['currency'] ?? 'USD'),
            ':locale' => clean_string($input['locale'] ?? 'en-US', 12),
            ':timezone' => clean_string($input['timezone'] ?? 'UTC', 64),
            ':user_id' => $userId,
        ]);
        json_response(['success' => true, 'message' => 'Profile updated']);
    }

    if ($action === 'mark_notifications_read') {
        $stmt = $pdo->prepare('UPDATE notifications SET read_at = NOW() WHERE user_id = :user_id AND read_at IS NULL');
        $stmt->execute([':user_id' => $userId]);
        json_response(['success' => true, 'message' => 'Notifications marked as read']);
    }

    json_response(['success' => false, 'message' => 'Unknown action'], 404);
} catch (PDOException $e) {
    error_log('Finance API Error: ' . $e->getMessage());
    json_response(['success' => false, 'message' => 'Database error. Run database.sql or the migration file, then try again.'], 500);
}

