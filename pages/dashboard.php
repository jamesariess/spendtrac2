<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | SpendTrackFinance</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body>
   <?php include 'sidebar.html'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Header -->
            <header class="top-header">
                <div class="header-left">
                    <button class="header-btn" id="menuToggle" onclick="toggleSidebar()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="12" x2="21" y2="12"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>
                    <h1 class="page-title">Dashboard</h1>
                    <div class="search-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input type="text" placeholder="Search transactions..." id="searchInput" onkeyup="filterTransactions()">
                    </div>
                </div>
                <div class="header-right">
                    <button class="header-btn" onclick="showNotifications()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                        <span class="notification-badge"></span>
                    </button>
                    <button class="header-btn" onclick="window.location.href='settings.html'">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                        </svg>
                    </button>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon balance">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                                    <line x1="12" y1="2" x2="12" y2="22"/>
                                </svg>
                            </div>
                            <span class="stat-trend up" id="balanceTrend">+0%</span>
                        </div>
                        <div class="stat-value" id="totalBalance">$0.00</div>
                        <div class="stat-label">Total Balance</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon income">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="19" x2="12" y2="5"/>
                                    <polyline points="5 12 12 5 19 12"/>
                                </svg>
                            </div>
                            <span class="stat-trend up" id="incomeTrend">+0%</span>
                        </div>
                        <div class="stat-value" id="totalIncome">$0.00</div>
                        <div class="stat-label">Total Income</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon expense">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"/>
                                    <polyline points="19 12 12 19 5 12"/>
                                </svg>
                            </div>
                            <span class="stat-trend down" id="expenseTrend">-0%</span>
                        </div>
                        <div class="stat-value" id="totalExpenses">$0.00</div>
                        <div class="stat-label">Total Expenses</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon savings">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                                </svg>
                            </div>
                            <span class="stat-trend up" id="savingsTrend">+0%</span>
                        </div>
                        <div class="stat-value" id="totalSavings">$0.00</div>
                        <div class="stat-label">Savings</div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="charts-grid">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title">Monthly Spending</h3>
                            <select class="form-select" style="width: auto; padding: 6px 12px;" id="chartPeriod" onchange="updateCharts()">
                                <option value="6">Last 6 Months</option>
                                <option value="12">Last 12 Months</option>
                            </select>
                        </div>
                        <div class="chart-container">
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title">Expense Categories</h3>
                        </div>
                        <div class="chart-container-donut">
                            <canvas id="doughnutChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Transactions -->
                <div class="transactions-card">
                    <div class="transactions-header">
                        <h3 class="transactions-title">Recent Transactions</h3>
                        <button class="btn btn-secondary" onclick="openModal()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 6px;">
                                <line x1="12" y1="5" x2="12" y2="19"/>
                                <line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                            Add Transaction
                        </button>
                    </div>
                    <table class="transactions-table">
                        <thead>
                            <tr>
                                <th>Transaction</th>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="transactionsBody">
                        </tbody>
                    </table>
                    <div class="empty-state" id="emptyState" style="display: none;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                        <p>No transactions yet. Click "Add Transaction" to get started!</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Quick Add Button -->
    <button class="quick-add-btn" onclick="openModal()" title="Add Transaction">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
    </button>

    <!-- Add Transaction Modal -->
    <div class="modal-overlay" id="transactionModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Add Transaction</h3>
                <button class="modal-close" onclick="closeModal()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <form id="transactionForm">
                    <input type="hidden" id="transactionId">
                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <select class="form-select" id="transactionType" required>
                            <option value="expense">Expense</option>
                            <option value="income">Income</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-input" id="transactionDesc" placeholder="e.g., Grocery shopping" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Amount</label>
                            <input type="number" class="form-input" id="transactionAmount" placeholder="0.00" step="0.01" min="0" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select class="form-select" id="transactionCategory" required>
                                <option value="food">Food & Dining</option>
                                <option value="transport">Transport</option>
                                <option value="shopping">Shopping</option>
                                <option value="bills">Bills & Utilities</option>
                                <option value="entertainment">Entertainment</option>
                                <option value="income">Income</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-input" id="transactionDate" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button class="btn btn-primary" onclick="saveTransaction()">Save Transaction</button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast" id="toast"></div>

    <script>
        // Check if user is logged in
        if (localStorage.getItem('isLoggedIn') !== 'true') {
            window.location.href = 'login.html';
        }

        // Initialize data
        let transactions = JSON.parse(localStorage.getItem('transactions')) || [];
        let barChart, doughnutChart;

        // Set default date to today
        document.getElementById('transactionDate').valueAsDate = new Date();

        // Category icons mapping
        const categoryIcons = {
            food: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>',
            transport: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
            shopping: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
            bills: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
            entertainment: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="15" rx="2" ry="2"/><polyline points="17 2 12 7 7 2"/></svg>',
            income: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>'
        };

        const categoryLabels = {
            food: 'Food & Dining',
            transport: 'Transport',
            shopping: 'Shopping',
            bills: 'Bills & Utilities',
            entertainment: 'Entertainment',
            income: 'Income'
        };

        // Mobile menu toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

        // Show notifications
        function showNotifications() {
            showToast('You have no new notifications', 'success');
        }

        // Update stats
        function updateStats() {
            const income = transactions
                .filter(t => t.type === 'income')
                .reduce((sum, t) => sum + parseFloat(t.amount), 0);
            
            const expenses = transactions
                .filter(t => t.type === 'expense')
                .reduce((sum, t) => sum + parseFloat(t.amount), 0);
            
            const balance = income - expenses;
            const savings = balance > 0 ? balance : 0;

            document.getElementById('totalBalance').textContent = formatCurrency(balance);
            document.getElementById('totalIncome').textContent = formatCurrency(income);
            document.getElementById('totalExpenses').textContent = formatCurrency(expenses);
            document.getElementById('totalSavings').textContent = formatCurrency(savings);

            // Update trends (simple calculation)
            document.getElementById('balanceTrend').textContent = balance >= 0 ? '+' + formatCurrency(balance) : formatCurrency(balance);
            document.getElementById('incomeTrend').textContent = '+' + formatCurrency(income);
            document.getElementById('expenseTrend').textContent = '-' + formatCurrency(expenses);
            document.getElementById('savingsTrend').textContent = '+' + formatCurrency(savings);
        }

        // Format currency
        function formatCurrency(amount) {
            return '$' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }

        // Format date
        function formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        }

        // Render transactions
        function renderTransactions(filter = '') {
            const tbody = document.getElementById('transactionsBody');
            const emptyState = document.getElementById('emptyState');
            
            let filteredTransactions = transactions;
            
            if (filter) {
                filteredTransactions = transactions.filter(t => 
                    t.description.toLowerCase().includes(filter.toLowerCase()) ||
                    t.category.toLowerCase().includes(filter.toLowerCase())
                );
            }

            // Sort by date (newest first)
            filteredTransactions.sort((a, b) => new Date(b.date) - new Date(a.date));

            if (filteredTransactions.length === 0) {
                tbody.innerHTML = '';
                emptyState.style.display = 'block';
                return;
            }

            emptyState.style.display = 'none';
            tbody.innerHTML = filteredTransactions.map(t => `
                <tr>
                    <td>
                        <div class="transaction-info">
                            <div class="transaction-icon ${t.category}">
                                ${categoryIcons[t.category] || categoryIcons.food}
                            </div>
                            <div class="transaction-details">
                                <h4>${t.description}</h4>
                                <span>${t.category === 'income' ? 'Income' : 'Expense'}</span>
                            </div>
                        </div>
                    </td>
                    <td>${formatDate(t.date)}</td>
                    <td>${categoryLabels[t.category] || t.category}</td>
                    <td class="transaction-amount ${t.type}">${t.type === 'income' ? '+' : '-'}${formatCurrency(t.amount)}</td>
                    <td>
                        <div class="transaction-actions">
                            <button class="action-btn" title="Edit" onclick="editTransaction('${t.id}')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </button>
                            <button class="action-btn delete" title="Delete" onclick="deleteTransaction('${t.id}')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        // Filter transactions
        function filterTransactions() {
            const searchValue = document.getElementById('searchInput').value;
            renderTransactions(searchValue);
        }

        // Open modal
        function openModal(isEdit = false) {
            document.getElementById('transactionModal').classList.add('show');
            if (!isEdit) {
                document.getElementById('modalTitle').textContent = 'Add Transaction';
                document.getElementById('transactionForm').reset();
                document.getElementById('transactionDate').valueAsDate = new Date();
                document.getElementById('transactionId').value = '';
            }
        }

        // Close modal
        function closeModal() {
            document.getElementById('transactionModal').classList.remove('show');
            document.getElementById('transactionForm').reset();
            document.getElementById('transactionDate').valueAsDate = new Date();
            document.getElementById('transactionId').value = '';
        }

        // Save transaction
        function saveTransaction() {
            const id = document.getElementById('transactionId').value;
            const type = document.getElementById('transactionType').value;
            const description = document.getElementById('transactionDesc').value;
            const amount = document.getElementById('transactionAmount').value;
            const category = document.getElementById('transactionCategory').value;
            const date = document.getElementById('transactionDate').value;

            if (!description || !amount) {
                showToast('Please fill in all required fields', 'error');
                return;
            }

            if (id) {
                // Update existing
                const index = transactions.findIndex(t => t.id === id);
                if (index !== -1) {
                    transactions[index] = { id, type, description, amount: parseFloat(amount), category, date };
                    showToast('Transaction updated successfully!', 'success');
                }
            } else {
                // Add new
                const newTransaction = {
                    id: Date.now().toString(),
                    type,
                    description,
                    amount: parseFloat(amount),
                    category,
                    date
                };
                transactions.push(newTransaction);
                showToast('Transaction added successfully!', 'success');
            }

            // Save to localStorage
            localStorage.setItem('transactions', JSON.stringify(transactions));

            // Update UI
            updateStats();
            renderTransactions();
            updateCharts();
            closeModal();
        }

        // Edit transaction
        function editTransaction(id) {
            const transaction = transactions.find(t => t.id === id);
            if (!transaction) return;

            document.getElementById('modalTitle').textContent = 'Edit Transaction';
            document.getElementById('transactionId').value = transaction.id;
            document.getElementById('transactionType').value = transaction.type;
            document.getElementById('transactionDesc').value = transaction.description;
            document.getElementById('transactionAmount').value = transaction.amount;
            document.getElementById('transactionCategory').value = transaction.category;
            document.getElementById('transactionDate').value = transaction.date;

            openModal(true);
        }

        // Delete transaction
        function deleteTransaction(id) {
            if (!confirm('Are you sure you want to delete this transaction?')) return;

            transactions = transactions.filter(t => t.id !== id);
            localStorage.setItem('transactions', JSON.stringify(transactions));
            
            updateStats();
            renderTransactions();
            updateCharts();
            showToast('Transaction deleted successfully!', 'success');
        }

        // Show toast
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast ' + type + ' show';
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // Update charts
        function updateCharts() {
            const period = parseInt(document.getElementById('chartPeriod').value);
            
            // Monthly data
            const months = [];
            const incomeData = [];
            const expenseData = [];
            
            for (let i = period - 1; i >= 0; i--) {
                const date = new Date();
                date.setMonth(date.getMonth() - i);
                const monthName = date.toLocaleDateString('en-US', { month: 'short' });
                const monthYear = date.toISOString().slice(0, 7);
                
                months.push(monthName);
                
                const monthIncome = transactions
                    .filter(t => t.type === 'income' && t.date.startsWith(monthYear))
                    .reduce((sum, t) => sum + t.amount, 0);
                
                const monthExpense = transactions
                    .filter(t => t.type === 'expense' && t.date.startsWith(monthYear))
                    .reduce((sum, t) => sum + t.amount, 0);
                
                incomeData.push(monthIncome);
                expenseData.push(monthExpense);
            }

            // Destroy existing charts
            if (barChart) barChart.destroy();
            if (doughnutChart) doughnutChart.destroy();

            // Bar Chart - Monthly Spending
            const barCtx = document.getElementById('barChart').getContext('2d');
            barChart = new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Expenses',
                        data: expenseData,
                        backgroundColor: '#4F46E5',
                        borderRadius: 6,
                        borderSkipped: false,
                    }, {
                        label: 'Income',
                        data: incomeData,
                        backgroundColor: '#22c55e',
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                                padding: 20,
                                font: { family: 'Inter', size: 12 }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: 'Inter' } }
                        },
                        y: {
                            grid: { borderDash: [4, 4] },
                            ticks: { 
                                font: { family: 'Inter' },
                                callback: value => '$' + value.toLocaleString()
                            }
                        }
                    }
                }
            });

            // Category data for doughnut
            const categoryData = {};
            transactions
                .filter(t => t.type === 'expense')
                .forEach(t => {
                    categoryData[t.category] = (categoryData[t.category] || 0) + t.amount;
                });

            const categories = Object.keys(categoryData);
            const values = Object.values(categoryData);
            const colors = ['#f59e0b', '#3b82f6', '#ec4899', '#ef4444', '#8b5cf6', '#6366f1'];

            // Doughnut Chart - Expense Categories
            const doughnutCtx = document.getElementById('doughnutChart').getContext('2d');
            doughnutChart = new Chart(doughnutCtx, {
                type: 'doughnut',
                data: {
                    labels: categories.map(c => categoryLabels[c] || c),
                    datasets: [{
                        data: values.length > 0 ? values : [1],
                        backgroundColor: colors.slice(0, categories.length > 0 ? categories.length : 1),
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                                padding: 16,
                                font: { family: 'Inter', size: 11 }
                            }
                        }
                    }
                }
            });
        }

        // Logout function
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                localStorage.removeItem('isLoggedIn');
                localStorage.removeItem('userEmail');
                window.location.href = 'login.html';
            }
        }

        // Close modal on overlay click
        document.getElementById('transactionModal').addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-overlay')) {
                closeModal();
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Escape to close modal
            if (e.key === 'Escape') {
                closeModal();
            }
            // Ctrl+N to open new transaction
            if (e.ctrlKey && e.key === 'n') {
                e.preventDefault();
                openModal();
            }
        });

        // Initialize
        updateStats();
        renderTransactions();
        updateCharts();
    </script>
</body>
</html>

