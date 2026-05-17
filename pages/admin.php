<?php
require_once __DIR__ . '/_layout.php';
$user = require_page_auth($pdo);
if (($user['role'] ?? 'user') !== 'admin') {
    http_response_code(403);
    ob_start();
    echo '<section class="card"><h2>Admin access required</h2><p class="metric-note">Your account does not have permission to view this page.</p></section>';
    render_layout('Admin Panel', 'admin', ob_get_clean(), $user, ['subtitle' => 'Restricted system controls']);
    exit;
}
ob_start();
?>
<div class="grid cols-4" id="adminMetrics"></div>
<section class="card" style="margin-top:18px">
    <div class="section-head"><div><h2>User Management</h2><p>Monitor users, roles, transactions, and subscription status.</p></div></div>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>ID</th><th>Email</th><th>Role</th><th>Currency</th><th>Locale</th><th>Created</th></tr></thead><tbody id="adminUsers"></tbody></table></div>
</section>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    await loadAppData();
    const data = await apiFetch('../api/finance.php?action=admin');
    if (!data.success) { appToast(data.message, 'error'); return; }
    document.getElementById('adminMetrics').innerHTML = `
        <article class="card metric-card"><span class="metric-note">Users</span><div class="metric-value">${data.totals.users}</div></article>
        <article class="card metric-card"><span class="metric-note">Transactions</span><div class="metric-value">${data.totals.transactions}</div></article>
        <article class="card metric-card"><span class="metric-note">Expenses</span><div class="metric-value">${money(data.totals.expenses)}</div></article>
        <article class="card metric-card"><span class="metric-note">Active Subs</span><div class="metric-value">${data.totals.active_subscriptions}</div></article>`;
    document.getElementById('adminUsers').innerHTML = data.users.map(u => `<tr><td>${u.user_id}</td><td>${escapeHtml(u.email)}</td><td><span class="tag">${u.role}</span></td><td>${u.currency}</td><td>${u.locale}</td><td>${u.created_at}</td></tr>`).join('');
});
</script>
<?php render_layout('Admin Panel', 'admin', ob_get_clean(), $user, ['subtitle' => 'System-wide operational view']); ?>

