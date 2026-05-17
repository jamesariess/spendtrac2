<?php

declare(strict_types=1);

require_once '../backend/conn.php';
require_once '../backend/security.php';

$userId = require_auth();
$input = require_post_json();
validate_csrf_token($input['csrfToken'] ?? null);

$provider = $input['provider'] ?? '';
$plan = clean_string($input['plan'] ?? 'premium', 80);

if (!in_array($provider, ['stripe', 'paypal'], true)) {
    json_response(['success' => false, 'message' => 'Unsupported payment provider'], 422);
}

if ($provider === 'stripe') {
    $secret = getenv('STRIPE_SECRET_KEY') ?: '';
    $priceId = getenv('STRIPE_PREMIUM_PRICE_ID') ?: '';

    if ($secret === '' || $priceId === '' || !class_exists('\\Stripe\\StripeClient')) {
        json_response([
            'success' => false,
            'message' => 'Stripe is not configured. Install stripe/stripe-php and set STRIPE_SECRET_KEY plus STRIPE_PREMIUM_PRICE_ID.'
        ], 503);
    }
}

if ($provider === 'paypal') {
    $clientId = getenv('PAYPAL_CLIENT_ID') ?: '';
    $secret = getenv('PAYPAL_CLIENT_SECRET') ?: '';

    if ($clientId === '' || $secret === '') {
        json_response([
            'success' => false,
            'message' => 'PayPal is not configured. Set PAYPAL_CLIENT_ID and PAYPAL_CLIENT_SECRET.'
        ], 503);
    }
}

$stmt = $pdo->prepare(
    'INSERT INTO subscriptions (user_id, provider, plan_name, status)
     VALUES (:user_id, :provider, :plan_name, :status)'
);
$stmt->execute([
    ':user_id' => $userId,
    ':provider' => $provider,
    ':plan_name' => $plan,
    ':status' => 'pending_checkout',
]);

json_response([
    'success' => true,
    'message' => 'Billing provider is configured. Connect the checkout redirect URL in production.',
    'subscription_id' => (int) $pdo->lastInsertId(),
]);

