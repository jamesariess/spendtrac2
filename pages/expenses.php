<?php
require_once __DIR__ . '/_layout.php';
$user = require_page_auth($pdo);
ob_start();
?>
<div class="grid cols-3">
    <section class="card">
        <div class="section-head"><div><h2>New Expense</h2><p>Categorize spending, mark recurring items, and keep notes.</p></div></div>
        <form id="expenseForm" class="form-grid">
            <input type="hidden" name="type" value="expense">
            <div class="field full"><label>Description</label><input name="description" required></div>
            <div class="field"><label>Amount</label><input type="number" name="amount" min="0.01" step="0.01" required></div>
            <div class="field"><label>Currency</label><input name="currency" maxlength="3" value="<?= htmlspecialchars($user['currency'] ?? 'USD') ?>"></div>
            <div class="field"><label>Category</label><input name="category" placeholder="Food, Travel, Bills" required></div>
            <div class="field"><label>Date</label><input type="date" name="transaction_date" value="<?= date('Y-m-d') ?>" required></div>
            <div class="field"><label>Recurring</label><select name="is_recurring"><option value="">No</option><option value="1">Yes</option></select></div>
            <div class="field"><label>Interval</label><select name="recurring_interval"><option value="monthly">Monthly</option><option value="weekly">Weekly</option><option value="yearly">Yearly</option></select></div>
            <div class="field full"><label>Notes</label><textarea name="notes" rows="3"></textarea></div>
            <button class="btn primary full" type="submit"><i data-lucide="receipt"></i> Add Expense</button>
        </form>
    </section>
    <section class="card span-2">
        <div class="section-head"><div><h2>Expense Records</h2><p>Filtered view of outgoing money.</p></div></div>
        <div class="chart-box"><canvas id="expenseChart"></canvas></div>
        <div class="table-wrap" style="margin-top:16px"><table class="data-table"><thead><tr><th>Description</th><th>Category</th><th>Date</th><th>Currency</th><th>Amount</th><th>Actions</th></tr></thead><tbody id="expenseRows"></tbody></table></div>
    </section>
</div>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    await loadAppData();
    const expenses = appState.transactions.filter(t => t.type === 'expense');
    renderTransactionsTable('expenseRows', expenses);
    document.getElementById('expenseForm').addEventListener('submit', async e => {
        e.preventDefault();
        if (await saveTransactionFromForm(e.target)) location.reload();
    });
    new Chart(document.getElementById('expenseChart'), { type: 'bar', data: { labels: Object.keys(appState.categoryTotals), datasets: [{ label: 'Expenses', data: Object.values(appState.categoryTotals), backgroundColor: '#2457ff', borderRadius: 6 }] }, options: { responsive: true, maintainAspectRatio: false } });
});
</script>
<?php render_layout('Expenses', 'expenses', ob_get_clean(), $user, ['subtitle' => 'Control outgoing cash flow']); ?>

