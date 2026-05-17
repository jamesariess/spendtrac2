<?php
require_once __DIR__ . '/_layout.php';
$user = require_page_auth($pdo);
ob_start();
?>
<div class="grid cols-4">
    <article class="card metric-card"><div class="metric-top"><span>Net Worth Flow</span><span class="metric-icon"><i data-lucide="activity"></i></span></div><div class="metric-value" id="analyticsBalance">--</div><div class="metric-note">Current tracked balance</div></article>
    <article class="card metric-card"><div class="metric-top"><span>Savings Rate</span><span class="metric-icon"><i data-lucide="percent"></i></span></div><div class="metric-value" id="savingsRate">--</div><div class="metric-note">Monthly income retained</div></article>
    <article class="card metric-card"><div class="metric-top"><span>Transactions</span><span class="metric-icon"><i data-lucide="list"></i></span></div><div class="metric-value" id="txCount">--</div><div class="metric-note">Tracked ledger entries</div></article>
    <article class="card metric-card"><div class="metric-top"><span>Top Category</span><span class="metric-icon"><i data-lucide="target"></i></span></div><div class="metric-value" id="topCategory" style="font-size:1.35rem">--</div><div class="metric-note">Highest expense area</div></article>
</div>
<div class="grid cols-2" style="margin-top:18px">
    <section class="card"><div class="section-head"><div><h2>Yearly Cash Flow</h2><p>Global financial trend.</p></div></div><div class="chart-box"><canvas id="yearChart"></canvas></div></section>
    <section class="card"><div class="section-head"><div><h2>Category Split</h2><p>Expense concentration by category.</p></div></div><div class="chart-box"><canvas id="splitChart"></canvas></div></section>
</div>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    await loadAppData();
    renderMetric('analyticsBalance', appState.summary.balance);
    const rate = appState.summary.monthlyIncome > 0 ? Math.round((appState.summary.savings / appState.summary.monthlyIncome) * 100) : 0;
    document.getElementById('savingsRate').textContent = `${rate}%`;
    document.getElementById('txCount').textContent = appState.transactions.length;
    const top = Object.entries(appState.categoryTotals).sort((a,b) => b[1] - a[1])[0];
    document.getElementById('topCategory').textContent = top ? top[0] : 'None';
    new Chart(document.getElementById('yearChart'), { type: 'line', data: { labels: appState.monthlyTotals.map(m => m.month), datasets: [{ label: 'Income', data: appState.monthlyTotals.map(m => m.income), borderColor: '#16a34a', tension: .35 }, { label: 'Expenses', data: appState.monthlyTotals.map(m => m.expenses), borderColor: '#ef4444', tension: .35 }] }, options: { responsive: true, maintainAspectRatio: false } });
    new Chart(document.getElementById('splitChart'), { type: 'polarArea', data: { labels: Object.keys(appState.categoryTotals), datasets: [{ data: Object.values(appState.categoryTotals), backgroundColor: ['#2457ff','#18a999','#f59e0b','#ef4444','#8b5cf6'] }] }, options: { responsive: true, maintainAspectRatio: false } });
});
</script>
<?php render_layout('Analytics', 'analytics', ob_get_clean(), $user, ['subtitle' => 'Deeper insight into money behavior']); ?>

