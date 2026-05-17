<?php
require_once __DIR__ . '/_layout.php';
$user = require_page_auth($pdo);
ob_start();
?>
<div class="grid cols-3">
    <section class="card">
        <div class="section-head"><div><h2>New Expense</h2><p>Scan a bill, review details, then save the expense with reminders.</p></div></div>
        <div class="field full" style="margin-bottom:14px">
            <label>Scan Bill / Receipt Image</label>
            <input type="file" id="billImage" accept="image/*">
            <p class="metric-note">OCR runs in your browser. Details will pop up first so you can edit before saving.</p>
        </div>
        <form id="expenseForm" class="form-grid">
            <input type="hidden" name="type" value="expense">
            <div class="field full"><label>Description</label><input name="description" required></div>
            <div class="field"><label>Amount</label><input type="number" name="amount" min="0.01" step="0.01" required></div>
            <div class="field"><label>Currency</label><input name="currency" maxlength="3" value="<?= htmlspecialchars($user['currency'] ?? 'USD') ?>"></div>
            <div class="field"><label>Category</label><input name="category" placeholder="Food, Travel, Bills" required></div>
            <div class="field"><label>Date</label><input type="date" name="transaction_date" value="<?= date('Y-m-d') ?>" required></div>
            <div class="field"><label>Due Date</label><input type="date" name="due_date"></div>
            <div class="field"><label>Payment Status</label><select name="payment_status"><option value="unpaid">Unpaid</option><option value="scheduled">Scheduled</option><option value="paid">Paid</option></select></div>
            <div class="field"><label>Email Reminder</label><select name="reminder_enabled"><option value="">No</option><option value="1">Yes</option></select></div>
            <div class="field"><label>Remind Days Before</label><input type="number" min="0" max="30" name="reminder_days_before" value="3"></div>
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
<div class="modal-backdrop" id="scanReviewModal">
    <div class="review-modal">
        <div class="section-head">
            <div><h2>Review Scanned Bill</h2><p>Check the extracted details. Edit anything wrong before saving.</p></div>
            <button class="icon-button" type="button" id="closeScanReview" aria-label="Close"><i data-lucide="x"></i></button>
        </div>
        <form id="scanReviewForm" class="form-grid">
            <div class="field full"><label>Description</label><input name="description" required></div>
            <div class="field"><label>Amount</label><input type="number" name="amount" min="0.01" step="0.01" required></div>
            <div class="field"><label>Currency</label><input name="currency" maxlength="3" value="<?= htmlspecialchars($user['currency'] ?? 'USD') ?>"></div>
            <div class="field"><label>Category</label><input name="category" value="Bills" required></div>
            <div class="field"><label>Transaction Date</label><input type="date" name="transaction_date" value="<?= date('Y-m-d') ?>" required></div>
            <div class="field"><label>Due Date</label><input type="date" name="due_date"></div>
            <div class="field full"><label>Scanned Text</label><div class="scan-preview" id="scanTextPreview"></div></div>
            <div class="field full">
                <button class="btn primary" type="submit"><i data-lucide="check"></i> Looks Correct, Save Expense</button>
                <button class="btn" type="button" id="copyScanToForm">Copy To Main Form</button>
            </div>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
<script>
function parseBillText(text) {
    const clean = text.replace(/\s+/g, ' ').trim();
    const lines = text.split(/\r?\n/).map(line => line.trim()).filter(Boolean);
    const amountMatches = [...clean.matchAll(/(?:total|amount due|balance due|grand total|₱|php|usd|\$)?\s*([₱$]?\s?\d{1,3}(?:,\d{3})*(?:\.\d{2})|\d+\.\d{2})/gi)];
    const amounts = amountMatches
        .map(match => Number(String(match[1]).replace(/[₱$,\s]/g, '')))
        .filter(value => Number.isFinite(value) && value > 0);
    const dateMatch = clean.match(/(\d{4}[-/]\d{1,2}[-/]\d{1,2}|\d{1,2}[-/]\d{1,2}[-/]\d{2,4})/);
    const dueMatch = clean.match(/(?:due date|payment due|pay before)\D{0,20}(\d{4}[-/]\d{1,2}[-/]\d{1,2}|\d{1,2}[-/]\d{1,2}[-/]\d{2,4})/i);
    const currency = /₱|php/i.test(clean) ? 'PHP' : /\$|usd/i.test(clean) ? 'USD' : '<?= htmlspecialchars($user['currency'] ?? 'USD') ?>';

    return {
        description: lines[0] || 'Scanned bill',
        amount: amounts.length ? Math.max(...amounts).toFixed(2) : '',
        currency,
        category: /electric|water|internet|utility|bill/i.test(clean) ? 'Bills' : 'Expense',
        transaction_date: normalizeBillDate(dateMatch?.[1]) || new Date().toISOString().slice(0, 10),
        due_date: normalizeBillDate(dueMatch?.[1]) || ''
    };
}

function normalizeBillDate(value) {
    if (!value) return '';
    const normalized = value.replace(/\//g, '-');
    const parts = normalized.split('-').map(Number);
    if (parts.length !== 3) return '';
    if (String(parts[0]).length === 4) {
        return `${parts[0]}-${String(parts[1]).padStart(2, '0')}-${String(parts[2]).padStart(2, '0')}`;
    }
    const year = parts[2] < 100 ? 2000 + parts[2] : parts[2];
    return `${year}-${String(parts[0]).padStart(2, '0')}-${String(parts[1]).padStart(2, '0')}`;
}

function fillForm(form, values) {
    Object.entries(values).forEach(([key, value]) => {
        if (form.elements[key]) form.elements[key].value = value;
    });
}

document.addEventListener('DOMContentLoaded', async () => {
    await loadAppData();
    const expenses = appState.transactions.filter(t => t.type === 'expense');
    renderTransactionsTable('expenseRows', expenses);
    document.getElementById('expenseForm').addEventListener('submit', async e => {
        e.preventDefault();
        if (await saveTransactionFromForm(e.target)) location.reload();
    });
    document.getElementById('billImage').addEventListener('change', async event => {
        const file = event.target.files[0];
        if (!file) return;
        if (!window.Tesseract) {
            appToast('OCR library is not available. Check your internet connection.', 'error');
            return;
        }
        showPageLoader('Scanning bill image...');
        let result;
        try {
            result = await Tesseract.recognize(file, 'eng');
        } finally {
            hidePageLoader();
        }
        const text = result.data.text || '';
        const parsed = parseBillText(text);
        document.getElementById('scanTextPreview').textContent = text || 'No readable text found.';
        fillForm(document.getElementById('scanReviewForm'), parsed);
        document.getElementById('scanReviewModal').classList.add('show');
        if (window.lucide) window.lucide.createIcons();
    });
    document.getElementById('closeScanReview').addEventListener('click', () => document.getElementById('scanReviewModal').classList.remove('show'));
    document.getElementById('copyScanToForm').addEventListener('click', () => {
        const values = Object.fromEntries(new FormData(document.getElementById('scanReviewForm')).entries());
        fillForm(document.getElementById('expenseForm'), values);
        document.getElementById('scanReviewModal').classList.remove('show');
    });
    document.getElementById('scanReviewForm').addEventListener('submit', async event => {
        event.preventDefault();
        const mainForm = document.getElementById('expenseForm');
        fillForm(mainForm, Object.fromEntries(new FormData(event.target).entries()));
        mainForm.elements.reminder_enabled.value = mainForm.elements.due_date.value ? '1' : '';
        if (await saveTransactionFromForm(mainForm)) location.reload();
    });
    new Chart(document.getElementById('expenseChart'), { type: 'bar', data: { labels: Object.keys(appState.categoryTotals), datasets: [{ label: 'Expenses', data: Object.values(appState.categoryTotals), backgroundColor: '#2457ff', borderRadius: 6 }] }, options: { responsive: true, maintainAspectRatio: false } });
});
</script>
<?php render_layout('Expenses', 'expenses', ob_get_clean(), $user, ['subtitle' => 'Control outgoing cash flow']); ?>
