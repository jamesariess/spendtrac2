<?php
require_once __DIR__ . '/_layout.php';
$user = require_page_auth($pdo);
ob_start();
?>
<div class="grid cols-3">
    <section class="card">
        <div class="section-head"><div><h2>Create Budget</h2><p>Set spending limits by category and period.</p></div></div>
        <form id="budgetForm" class="form-grid">
            <div class="field full"><label>Category</label><input name="category" required></div>
            <div class="field"><label>Amount</label><input type="number" name="amount" min="0.01" step="0.01" required></div>
            <div class="field"><label>Currency</label><input name="currency" maxlength="3" value="<?= htmlspecialchars($user['currency'] ?? 'USD') ?>"></div>
            <div class="field"><label>Period</label><select name="period"><option value="monthly">Monthly</option><option value="quarterly">Quarterly</option><option value="yearly">Yearly</option></select></div>
            <div class="field"><label>Starts On</label><input type="date" name="starts_on" value="<?= date('Y-m-01') ?>"></div>
            <button class="btn primary full" type="submit"><i data-lucide="piggy-bank"></i> Save Budget</button>
        </form>
    </section>
    <section class="card span-2"><div class="section-head"><div><h2>Budget Planner</h2><p>Active budgets across categories.</p></div></div><div id="budgetList" class="grid cols-2"></div></section>
</div>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    await loadAppData();
    const render = () => document.getElementById('budgetList').innerHTML = appState.budgets.length ? appState.budgets.map(b => `<article class="card"><strong>${escapeHtml(b.category)}</strong><div class="metric-value">${money(b.amount,b.currency)}</div><p class="metric-note">${escapeHtml(b.period)} from ${escapeHtml(b.starts_on)}</p></article>`).join('') : '<div class="empty-state">No budgets yet.</div>';
    render();
    document.getElementById('budgetForm').addEventListener('submit', async e => {
        e.preventDefault();
        const payload = Object.fromEntries(new FormData(e.target).entries());
        payload.csrfToken = appState.csrfToken;
        const data = await apiFetch('../api/finance.php?action=save_budget', { method: 'POST', body: JSON.stringify(payload) });
        appToast(data.message, data.success ? 'success' : 'error');
        await loadAppData(); render();
    });
});
</script>
<?php render_layout('Budget Planner', 'budget', ob_get_clean(), $user, ['subtitle' => 'Plan spend before it happens']); ?>

