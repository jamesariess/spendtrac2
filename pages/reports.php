<?php
require_once __DIR__ . '/_layout.php';
$user = require_page_auth($pdo);
ob_start();
?>
<section class="card">
    <div class="section-head">
        <div><h2>Reports</h2><p>Downloadable monthly and yearly finance summaries.</p></div>
        <button class="btn primary" id="downloadReport"><i data-lucide="download"></i> Download CSV</button>
    </div>
    <div class="grid cols-3" id="reportCards"></div>
    <div class="table-wrap" style="margin-top:18px"><table class="data-table"><thead><tr><th>Month</th><th>Income</th><th>Expenses</th><th>Net</th></tr></thead><tbody id="reportRows"></tbody></table></div>
</section>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    await loadAppData();
    document.getElementById('reportCards').innerHTML = `
        <article class="card"><strong>Total Income</strong><div class="metric-value">${money(appState.summary.income)}</div></article>
        <article class="card"><strong>Total Expenses</strong><div class="metric-value">${money(appState.summary.expenses)}</div></article>
        <article class="card"><strong>Net Balance</strong><div class="metric-value">${money(appState.summary.balance)}</div></article>`;
    document.getElementById('reportRows').innerHTML = appState.monthlyTotals.map(row => `<tr><td>${row.month}</td><td>${money(row.income)}</td><td>${money(row.expenses)}</td><td>${money(row.income - row.expenses)}</td></tr>`).join('');
    document.getElementById('downloadReport').addEventListener('click', () => {
        const rows = [['Month','Income','Expenses','Net'], ...appState.monthlyTotals.map(r => [r.month,r.income,r.expenses,r.income-r.expenses])];
        const csv = rows.map(r => r.join(',')).join('\n');
        const link = document.createElement('a');
        link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv' }));
        link.download = 'spendtrack-report.csv';
        link.click();
    });
});
</script>
<?php render_layout('Reports', 'reports', ob_get_clean(), $user, ['subtitle' => 'Exportable financial reporting']); ?>

