<?php
require_once __DIR__ . '/_layout.php';
$user = require_page_auth($pdo);
ob_start();
?>
<div class="grid cols-2">
    <section class="card">
        <div class="section-head"><div><h2>Global Preferences</h2><p>Currency, locale, timezone, and display settings.</p></div></div>
        <form id="settingsForm" class="form-grid">
            <div class="field full"><label>Display Name</label><input name="display_name" value="<?= htmlspecialchars($user['display_name'] ?? '') ?>"></div>
            <div class="field"><label>Currency</label><input name="currency" maxlength="3" value="<?= htmlspecialchars($user['currency'] ?? 'USD') ?>"></div>
            <div class="field"><label>Locale</label><input name="locale" value="<?= htmlspecialchars($user['locale'] ?? 'en-US') ?>"></div>
            <div class="field full"><label>Timezone</label><input name="timezone" value="<?= htmlspecialchars($user['timezone'] ?? 'UTC') ?>"></div>
            <button class="btn primary full" type="submit"><i data-lucide="save"></i> Save Settings</button>
        </form>
    </section>
    <section class="card">
        <div class="section-head"><div><h2>Premium Billing</h2><p>Stripe and PayPal checkout are environment-configured for deployment.</p></div></div>
        <div class="grid cols-2">
            <button class="btn primary" data-billing="stripe"><i data-lucide="credit-card"></i> Stripe Premium</button>
            <button class="btn" data-billing="paypal"><i data-lucide="badge-dollar-sign"></i> PayPal Premium</button>
        </div>
        <p class="metric-note" style="margin-top:16px">Required env vars: Stripe keys or PayPal client credentials. Billing events are recorded through <code>api/webhooks.php</code>.</p>
    </section>
</div>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    await loadAppData();
    document.getElementById('settingsForm').addEventListener('submit', async e => {
        e.preventDefault();
        const payload = Object.fromEntries(new FormData(e.target).entries());
        payload.csrfToken = appState.csrfToken;
        const data = await apiFetch('../api/finance.php?action=save_profile', { method: 'POST', body: JSON.stringify(payload) });
        appToast(data.message, data.success ? 'success' : 'error');
    });
    document.querySelectorAll('[data-billing]').forEach(button => button.addEventListener('click', async () => {
        const data = await apiFetch('../api/billing.php', { method: 'POST', body: JSON.stringify({ csrfToken: appState.csrfToken, provider: button.dataset.billing, plan: 'premium' }) });
        appToast(data.message, data.success ? 'success' : 'error');
    }));
});
</script>
<?php render_layout('Settings', 'settings', ob_get_clean(), $user, ['subtitle' => 'Secure account and app preferences']); ?>

