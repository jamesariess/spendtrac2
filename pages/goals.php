<?php
require_once __DIR__ . '/_layout.php';
$user = require_page_auth($pdo);
ob_start();
?>
<div class="grid cols-3">
    <section class="card">
        <div class="section-head"><div><h2>Savings Goal</h2><p>Plan large purchases, emergency funds, and milestones.</p></div></div>
        <form id="goalForm" class="form-grid">
            <div class="field full"><label>Name</label><input name="name" required></div>
            <div class="field"><label>Target Amount</label><input type="number" name="target_amount" min="0.01" step="0.01" required></div>
            <div class="field"><label>Saved Amount</label><input type="number" name="saved_amount" min="0" step="0.01" value="0"></div>
            <div class="field"><label>Currency</label><input name="currency" maxlength="3" value="<?= htmlspecialchars($user['currency'] ?? 'USD') ?>"></div>
            <div class="field"><label>Target Date</label><input type="date" name="target_date"></div>
            <button class="btn primary full" type="submit"><i data-lucide="target"></i> Save Goal</button>
        </form>
    </section>
    <section class="card span-2"><div class="section-head"><div><h2>Goals</h2><p>Track progress toward financial milestones.</p></div></div><div id="goalList" class="grid cols-2"></div></section>
</div>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    await loadAppData();
    const render = () => document.getElementById('goalList').innerHTML = appState.goals.length ? appState.goals.map(g => {
        const pct = g.target_amount > 0 ? Math.min(100, Math.round((g.saved_amount / g.target_amount) * 100)) : 0;
        return `<article class="card"><strong>${escapeHtml(g.name)}</strong><div class="metric-value">${pct}%</div><p class="metric-note">${money(g.saved_amount,g.currency)} of ${money(g.target_amount,g.currency)}</p><div style="height:8px;background:var(--surface-2);border-radius:999px"><span style="display:block;width:${pct}%;height:8px;background:var(--primary);border-radius:999px"></span></div></article>`;
    }).join('') : '<div class="empty-state">No savings goals yet.</div>';
    render();
    document.getElementById('goalForm').addEventListener('submit', async e => {
        e.preventDefault();
        const payload = Object.fromEntries(new FormData(e.target).entries());
        payload.csrfToken = appState.csrfToken;
        const data = await apiFetch('../api/finance.php?action=save_goal', { method: 'POST', body: JSON.stringify(payload) });
        appToast(data.message, data.success ? 'success' : 'error');
        await loadAppData(); render();
    });
});
</script>
<?php render_layout('Goals', 'analytics', ob_get_clean(), $user, ['subtitle' => 'Build toward long-term targets']); ?>

