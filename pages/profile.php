<?php
require_once __DIR__ . '/_layout.php';
$user = require_page_auth($pdo);
ob_start();
?>
<div class="grid cols-3">
    <section class="card">
        <div style="text-align:center">
            <span class="avatar" style="margin:0 auto;width:82px;height:82px;font-size:1.5rem"><?= htmlspecialchars(user_initials($user['email'])) ?></span>
            <h2><?= htmlspecialchars($user['display_name'] ?? explode('@', $user['email'])[0]) ?></h2>
            <p class="metric-note"><?= htmlspecialchars($user['email']) ?></p>
            <span class="tag"><?= htmlspecialchars($user['role'] ?? 'user') ?></span>
        </div>
    </section>
    <section class="card span-2">
        <div class="section-head"><div><h2>Profile Summary</h2><p>Account, locale, and financial activity.</p></div></div>
        <div class="grid cols-3" id="profileStats"></div>
    </section>
</div>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    await loadAppData();
    document.getElementById('profileStats').innerHTML = `
        <article class="card"><strong>Transactions</strong><div class="metric-value">${appState.transactions.length}</div></article>
        <article class="card"><strong>Budgets</strong><div class="metric-value">${appState.budgets.length}</div></article>
        <article class="card"><strong>Goals</strong><div class="metric-value">${appState.goals.length}</div></article>`;
});
</script>
<?php render_layout('User Profile', 'profile', ob_get_clean(), $user, ['subtitle' => 'Your SpendTrack identity']); ?>

