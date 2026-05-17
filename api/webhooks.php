<?php

declare(strict_types=1);

require_once '../backend/conn.php';

$provider = $_GET['provider'] ?? '';
$payload = file_get_contents('php://input');
$eventId = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? $_SERVER['HTTP_PAYPAL_TRANSMISSION_ID'] ?? hash('sha256', $payload);

if (!in_array($provider, ['stripe', 'paypal'], true)) {
    http_response_code(404);
    exit;
}

$data = json_decode($payload, true);
$eventType = $data['type'] ?? $data['event_type'] ?? 'unknown';

$stmt = $pdo->prepare(
    'INSERT IGNORE INTO payment_events (provider, event_id, event_type, payload, processed_at)
     VALUES (:provider, :event_id, :event_type, :payload, NOW())'
);
$stmt->execute([
    ':provider' => $provider,
    ':event_id' => $eventId,
    ':event_type' => $eventType,
    ':payload' => $payload ?: null,
]);

http_response_code(200);
echo 'ok';

