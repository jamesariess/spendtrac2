<?php
require_once __DIR__ . '/_layout.php';

$user = require_page_auth($pdo);

ob_start();
?>
<div class="grid cols-3">
    <section class="card">
        <div class="section-head"><div><h2>Add Transaction</h2><p>Track income or expenses securely.</p></div></div>
        <form id="transactionForm" class="form-grid">
            <input type="hidden" name="transaction_id" id="transactionId">
            <div class="field"><label>Type</label><select name="type" required><option value="expense">Expense</option><option value="income">Income</option></select></div>
            <div class="field"><label>Currency</label><input name="currency" maxlength="3" value="<?= htmlspecialchars($user['currency'] ?? 'USD') ?>" required></div>
            <div class="field full"><label>Description</label><input name="description" placeholder="Groceries, salary, rent..." required></div>
            <div class="field"><label>Amount</label><input type="number" step="0.01" min="0.01" name="amount" required></div>
            <div class="field"><label>Category</label><input name="category" placeholder="Food, Rent, Salary" required></div>
            <div class="field"><label>Date</label><input type="date" name="transaction_date" value="<?= date('Y-m-d') ?>" required></div>
            <div class="field"><label>Payment Method</label><input name="payment_method" placeholder="Cash, card, bank"></div>
            <div class="field"><label>Recurring</label><select name="is_recurring"><option value="">No</option><option value="1">Yes</option></select></div>
            <div class="field"><label>Interval</label><select name="recurring_interval"><option value="monthly">Monthly</option><option value="weekly">Weekly</option><option value="yearly">Yearly</option></select></div>
            <div class="field full"><label>Notes</label><textarea name="notes" rows="3"></textarea></div>
            <button class="btn primary full" type="submit"><i data-lucide="save"></i> Save Transaction</button>
        </form>
    </section>

    <section class="card span-2">
        <div class="section-head">
            <div><h2>Transactions</h2><p>Search, filter, edit, delete, and export your records.</p></div>
            <div>
                <input id="transactionSearch" class="field-input" placeholder="Search..." style="min-height:40px;padding:0 12px;border:1px solid var(--line);border-radius:8px;background:var(--surface);color:var(--text)">
                <button class="btn" id="exportCsv" type="button"><i data-lucide="download"></i> CSV</button>
            </div>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Description</th><th>Category</th><th>Date</th><th>Currency</th><th>Amount</th><th>Actions</th></tr></thead>
                <tbody id="transactionsTable"></tbody>
            </table>
        </div>
    </section>
</div>

<script>
let currentRows = [];

function applyTransactionFilter() {
    const term = document.getElementById('transactionSearch').value.toLowerCase();
    currentRows = appState.transactions.filter(item =>
        item.description.toLowerCase().includes(term) ||
        item.category.toLowerCase().includes(term) ||
        item.type.toLowerCase().includes(term)
    );
    renderTransactionsTable('transactionsTable', currentRows);
}

document.addEventListener('DOMContentLoaded', async () => {
    await loadAppData();
    currentRows = appState.transactions;
    renderTransactionsTable('transactionsTable');

    document.getElementById('transactionSearch').addEventListener('input', applyTransactionFilter);
    document.getElementById('transactionForm').addEventListener('submit', async event => {
        event.preventDefault();
        if (await saveTransactionFromForm(event.target)) {
            event.target.reset();
            event.target.transaction_date.value = new Date().toISOString().slice(0, 10);
            applyTransactionFilter();
        }
    });

    document.addEventListener('click', async event => {
        const deleteId = event.target.closest('[data-delete-transaction]')?.dataset.deleteTransaction;
        const editId = event.target.closest('[data-edit-transaction]')?.dataset.editTransaction;
        if (deleteId && confirm('Delete this transaction?')) {
            const button = event.target.closest('[data-delete-transaction]');
            await withButtonLoading(button, 'Deleting...', async () => {
                const data = await apiFetch('../api/finance.php?action=delete_transaction', {
                    method: 'POST',
                    body: JSON.stringify({ csrfToken: appState.csrfToken, transaction_id: deleteId })
                });
                appToast(data.message || 'Deleted', data.success ? 'success' : 'error');
                await loadAppData('Refreshing transactions...');
                applyTransactionFilter();
            });
        }
        if (editId) {
            const item = appState.transactions.find(row => String(row.transaction_id) === String(editId));
            if (!item) return;
            const form = document.getElementById('transactionForm');
            Object.keys(item).forEach(key => { if (form.elements[key]) form.elements[key].value = item[key] ?? ''; });
            form.transaction_id.value = item.transaction_id;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    document.getElementById('exportCsv').addEventListener('click', () => {
        const rows = [['Description','Type','Category','Date','Currency','Amount'], ...currentRows.map(t => [t.description,t.type,t.category,t.transaction_date,t.currency,t.amount])];
        const csv = rows.map(row => row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(',')).join('\n');
        const blob = new Blob([csv], { type: 'text/csv' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'spendtrack-transactions.csv';
        link.click();
    });
});
</script>
<?php
render_layout('Transactions', 'transactions', ob_get_clean(), $user, ['subtitle' => 'Full transaction ledger']);
