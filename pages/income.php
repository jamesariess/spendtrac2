<?php
require_once __DIR__ . '/_layout.php';
$user = require_page_auth($pdo);
ob_start();
?>
<div class="grid cols-3">
    <section class="card">
        <div class="section-head"><div><h2>Add Income</h2><p>Track salaries, sales, freelance, and passive income.</p></div></div>
        <form id="incomeForm" class="form-grid">
            <input type="hidden" name="type" value="income">
            <div class="field full"><label>Source</label><input name="description" placeholder="Salary, client, business" required></div>
            <div class="field"><label>Amount</label><input type="number" name="amount" min="0.01" step="0.01" required></div>
            <div class="field"><label>Currency</label><input name="currency" maxlength="3" value="<?= htmlspecialchars($user['currency'] ?? 'USD') ?>"></div>
            <div class="field"><label>Category</label><input name="category" value="Income" required></div>
            <div class="field"><label>Date</label><input type="date" name="transaction_date" value="<?= date('Y-m-d') ?>" required></div>
            <button class="btn success full" type="submit"><i data-lucide="wallet"></i> Add Income</button>
        </form>
    </section>
    <section class="card span-2">
        <div class="section-head"><div><h2>Income Analytics</h2><p>Monthly inflow and recent sources.</p></div></div>
        <div class="chart-box"><canvas id="incomeChart"></canvas></div>
        <div class="table-wrap" style="margin-top:16px"><table class="data-table"><thead><tr><th>Description</th><th>Category</th><th>Date</th><th>Currency</th><th>Amount</th><th>Actions</th></tr></thead><tbody id="incomeRows"></tbody></table></div>
    </section>
</div>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    await loadAppData();
    const rows = appState.transactions.filter(t => t.type === 'income');
    renderTransactionsTable('incomeRows', rows);
    document.getElementById('incomeForm').addEventListener('submit', async e => {
        e.preventDefault();
        if (await saveTransactionFromForm(e.target)) location.reload();
    });
    new Chart(document.getElementById('incomeChart'), { type: 'line', data: { labels: appState.monthlyTotals.map(m => m.month), datasets: [{ label: 'Income', data: appState.monthlyTotals.map(m => m.income), borderColor: '#16a34a', backgroundColor: 'rgba(22,163,74,.12)', fill: true, tension: .35 }] }, options: { responsive: true, maintainAspectRatio: false } });
});
</script>
<?php render_layout('Income', 'income', ob_get_clean(), $user, ['subtitle' => 'Monitor global income sources']); ?>

