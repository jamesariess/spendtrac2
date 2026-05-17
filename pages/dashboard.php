<?php
require_once __DIR__ . '/_layout.php';

$user = require_page_auth($pdo);

ob_start();
?>
<div class="grid cols-4" id="dashboardMetrics">
    <article class="card metric-card">
        <div class="metric-top"><span>Total Balance</span><span class="metric-icon"><i data-lucide="landmark"></i></span></div>
        <div class="metric-value" id="metricBalance">--</div>
        <div class="metric-note">All-time income minus expenses</div>
    </article>
    <article class="card metric-card">
        <div class="metric-top"><span>Monthly Income</span><span class="metric-icon"><i data-lucide="trending-up"></i></span></div>
        <div class="metric-value" id="metricIncome">--</div>
        <div class="metric-note">Income received this month</div>
    </article>
    <article class="card metric-card">
        <div class="metric-top"><span>Monthly Expenses</span><span class="metric-icon"><i data-lucide="trending-down"></i></span></div>
        <div class="metric-value" id="metricExpenses">--</div>
        <div class="metric-note">Spending tracked this month</div>
    </article>
    <article class="card metric-card">
        <div class="metric-top"><span>Savings Overview</span><span class="metric-icon"><i data-lucide="badge-percent"></i></span></div>
        <div class="metric-value" id="metricSavings">--</div>
        <div class="metric-note">Current monthly surplus</div>
    </article>
</div>

<div class="grid cols-3" style="margin-top:18px">
    <section class="card span-2">
        <div class="section-head">
            <div>
                <h2>Cash Flow</h2>
                <p>Income and expense trend for the latest months.</p>
            </div>
            <a class="btn" href="analytics.php"><i data-lucide="chart-line"></i> Analytics</a>
        </div>
        <div class="chart-box"><canvas id="cashflowChart"></canvas></div>
    </section>

    <section class="card">
        <div class="section-head">
            <div>
                <h2>Expense Categories</h2>
                <p>Where money is going.</p>
            </div>
        </div>
        <div class="chart-box"><canvas id="categoryChart"></canvas></div>
    </section>
</div>

<div class="grid cols-3" style="margin-top:18px">
    <section class="card span-2">
        <div class="section-head">
            <div>
                <h2>Recent Transactions</h2>
                <p>Search, review, and manage your latest activity.</p>
            </div>
            <a class="btn primary" href="transactions.php"><i data-lucide="plus"></i> Add Transaction</a>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr><th>Description</th><th>Category</th><th>Date</th><th>Currency</th><th>Amount</th><th>Actions</th></tr>
                </thead>
                <tbody id="recentTransactions"></tbody>
            </table>
        </div>
    </section>

    <section class="card">
        <div class="section-head">
            <div>
                <h2>Budget Health</h2>
                <p>Active limits and savings goals.</p>
            </div>
        </div>
        <div id="budgetHealth" class="grid"></div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const data = await loadAppData();
    if (!data?.success) {
        appToast(data?.message || 'Could not load dashboard', 'error');
        return;
    }

    renderMetric('metricBalance', appState.summary.balance);
    renderMetric('metricIncome', appState.summary.monthlyIncome);
    renderMetric('metricExpenses', appState.summary.monthlyExpenses);
    renderMetric('metricSavings', appState.summary.savings);
    renderTransactionsTable('recentTransactions', appState.transactions.slice(0, 8));

    const budgetBox = document.getElementById('budgetHealth');
    budgetBox.innerHTML = appState.budgets.length
        ? appState.budgets.slice(0, 4).map(b => `<div><strong>${escapeHtml(b.category)}</strong><p class="metric-note">${money(b.amount, b.currency)} ${escapeHtml(b.period)}</p></div>`).join('')
        : '<div class="empty-state">No budgets yet. Create one in Budget Planner.</div>';

    const months = appState.monthlyTotals.map(item => item.month);
    new Chart(document.getElementById('cashflowChart'), {
        type: 'bar',
        data: {
            labels: months,
            datasets: [
                { label: 'Income', data: appState.monthlyTotals.map(item => item.income), backgroundColor: '#16a34a', borderRadius: 6 },
                { label: 'Expenses', data: appState.monthlyTotals.map(item => item.expenses), backgroundColor: '#2457ff', borderRadius: 6 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    const categoryLabels = Object.keys(appState.categoryTotals);
    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: categoryLabels.length ? categoryLabels : ['No expenses'],
            datasets: [{ data: categoryLabels.length ? Object.values(appState.categoryTotals) : [1], backgroundColor: ['#2457ff', '#18a999', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'] }]
        },
        options: { responsive: true, maintainAspectRatio: false, cutout: '68%' }
    });

    if (window.lucide) window.lucide.createIcons();
});
</script>
<?php
render_layout('Dashboard', 'dashboard', ob_get_clean(), $user, ['subtitle' => 'Welcome back to your finance cockpit']);

