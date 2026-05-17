<?php

declare(strict_types=1);

require_once '../backend/conn.php';
require_once '../backend/security.php';

$userId = require_auth();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->prepare(
            'SELECT transaction_id AS id, type, description, amount, category, transaction_date AS date
             FROM transactions
             WHERE user_id = :user_id
             ORDER BY transaction_date DESC, transaction_id DESC'
        );
        $stmt->execute([':user_id' => $userId]);

        json_response(['success' => true, 'transactions' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    $input = require_post_json();
    validate_csrf_token($input['csrfToken'] ?? null);
    $action = $input['action'] ?? '';

    if ($action === 'save') {
        $id = isset($input['id']) && $input['id'] !== '' ? (int) $input['id'] : null;
        $type = $input['type'] ?? '';
        $description = clean_string($input['description'] ?? '', 120);
        $amount = filter_var($input['amount'] ?? null, FILTER_VALIDATE_FLOAT);
        $category = clean_string($input['category'] ?? '', 40);
        $date = $input['date'] ?? '';

        $allowedTypes = ['income', 'expense'];
        $allowedCategories = ['food', 'transport', 'shopping', 'bills', 'entertainment', 'income'];

        if (!in_array($type, $allowedTypes, true)) {
            json_response(['success' => false, 'message' => 'Invalid transaction type'], 422);
        }

        if ($description === '') {
            json_response(['success' => false, 'message' => 'Description is required'], 422);
        }

        if ($amount === false || $amount <= 0) {
            json_response(['success' => false, 'message' => 'Amount must be greater than zero'], 422);
        }

        if (!in_array($category, $allowedCategories, true)) {
            json_response(['success' => false, 'message' => 'Invalid category'], 422);
        }

        $dateTime = DateTime::createFromFormat('Y-m-d', $date);
        if (!$dateTime || $dateTime->format('Y-m-d') !== $date) {
            json_response(['success' => false, 'message' => 'Invalid date'], 422);
        }

        if ($id) {
            $stmt = $pdo->prepare(
                'UPDATE transactions
                 SET type = :type, description = :description, amount = :amount, category = :category, transaction_date = :transaction_date
                 WHERE transaction_id = :transaction_id AND user_id = :user_id'
            );
            $stmt->execute([
                ':type' => $type,
                ':description' => $description,
                ':amount' => $amount,
                ':category' => $category,
                ':transaction_date' => $date,
                ':transaction_id' => $id,
                ':user_id' => $userId,
            ]);

            json_response(['success' => true, 'message' => 'Transaction updated successfully']);
        }

        $stmt = $pdo->prepare(
            'INSERT INTO transactions (user_id, type, description, amount, category, transaction_date)
             VALUES (:user_id, :type, :description, :amount, :category, :transaction_date)'
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':type' => $type,
            ':description' => $description,
            ':amount' => $amount,
            ':category' => $category,
            ':transaction_date' => $date,
        ]);

        json_response(['success' => true, 'message' => 'Transaction added successfully', 'id' => (int) $pdo->lastInsertId()]);
    }

    if ($action === 'delete') {
        $id = (int) ($input['id'] ?? 0);
        if ($id <= 0) {
            json_response(['success' => false, 'message' => 'Invalid transaction'], 422);
        }

        $stmt = $pdo->prepare('DELETE FROM transactions WHERE transaction_id = :transaction_id AND user_id = :user_id');
        $stmt->execute([':transaction_id' => $id, ':user_id' => $userId]);

        json_response(['success' => true, 'message' => 'Transaction deleted successfully']);
    }

    json_response(['success' => false, 'message' => 'Invalid action'], 400);
} catch (PDOException $e) {
    error_log('Transactions API Error: ' . $e->getMessage());
    json_response(['success' => false, 'message' => 'Database error. Please try again later.'], 500);
}

