const appState = {
    currency: 'USD',
    locale: navigator.language || 'en-US',
    transactions: [],
    budgets: [],
    goals: [],
    summary: null,
    csrfToken: ''
};

let pendingRequests = 0;

function showTopProgress() {
    pendingRequests += 1;
    document.getElementById('topProgress')?.classList.add('show');
}

function hideTopProgress() {
    pendingRequests = Math.max(0, pendingRequests - 1);
    if (pendingRequests === 0) {
        document.getElementById('topProgress')?.classList.remove('show');
    }
}

function showPageLoader(message = 'Preparing your financial workspace...') {
    const loader = document.getElementById('pageLoader');
    const text = document.getElementById('pageLoaderText');
    if (text) text.textContent = message;
    loader?.classList.add('show');
    document.body.style.cursor = 'progress';
}

function hidePageLoader() {
    document.getElementById('pageLoader')?.classList.remove('show');
    document.body.style.cursor = '';
}

function setButtonLoading(button, isLoading, label = 'Working...') {
    if (!button) return;
    if (isLoading) {
        button.dataset.originalText = button.innerHTML;
        button.dataset.loadingLabel = label;
        button.classList.add('loading');
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        button.setAttribute('aria-label', label);
    } else {
        button.classList.remove('loading');
        button.disabled = false;
        button.removeAttribute('aria-busy');
        if (button.dataset.originalText) {
            button.innerHTML = button.dataset.originalText;
            delete button.dataset.originalText;
        }
    }
}

async function withButtonLoading(button, label, task) {
    setButtonLoading(button, true, label);
    try {
        return await task();
    } finally {
        setButtonLoading(button, false);
    }
}

function money(value, currency = appState.currency) {
    return new Intl.NumberFormat(appState.locale, {
        style: 'currency',
        currency
    }).format(Number(value || 0));
}

function appToast(message, type = 'success') {
    const toast = document.getElementById('appToast');
    if (!toast) return;
    toast.textContent = message;
    toast.className = `toast ${type} show`;
    setTimeout(() => toast.classList.remove('show'), 2600);
}

async function apiFetch(url, options = {}) {
    const showProgress = options.showProgress !== false;
    const fetchOptions = { ...options };
    delete fetchOptions.showProgress;

    if (showProgress) showTopProgress();

    try {
        const response = await fetch(url, {
            credentials: 'same-origin',
            ...fetchOptions,
            headers: {
                'Content-Type': 'application/json',
                ...(fetchOptions.headers || {})
            }
        });

        const data = await response.json().catch(() => ({ success: false, message: 'Invalid server response' }));
        if (response.status === 401) {
            window.location.href = '../auth/login.html';
        }
        return data;
    } finally {
        if (showProgress) hideTopProgress();
    }
}

async function loadAppData(message = 'Loading your financial data...') {
    showPageLoader(message);
    try {
        const session = await getSession();
        if (!session.authenticated) {
            window.location.href = '../auth/login.html';
            return null;
        }

        appState.csrfToken = session.csrfToken || '';
        appState.currency = session.user?.currency || 'USD';
        appState.locale = session.user?.locale || appState.locale;

        const data = await apiFetch('../api/finance.php?action=overview');
        if (data.success) {
            Object.assign(appState, data);
        }

        return data;
    } finally {
        hidePageLoader();
    }
}

function wireShell() {
    const sidebar = document.getElementById('appSidebar');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');
    const openSidebar = () => {
        sidebar?.classList.add('open');
        backdrop?.classList.add('show');
    };
    const closeSidebar = () => {
        sidebar?.classList.remove('open');
        backdrop?.classList.remove('show');
    };

    document.querySelector('[data-sidebar-open]')?.addEventListener('click', openSidebar);
    document.querySelector('[data-sidebar-backdrop]')?.addEventListener('click', closeSidebar);
    document.querySelector('[data-sidebar-collapse]')?.addEventListener('click', () => {
        document.body.classList.toggle('sidebar-collapsed');
        localStorage.setItem('sidebarCollapsed', document.body.classList.contains('sidebar-collapsed') ? '1' : '0');
    });

    if (localStorage.getItem('sidebarCollapsed') === '1') {
        document.body.classList.add('sidebar-collapsed');
    }

    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.dataset.theme = savedTheme;
    document.querySelector('[data-theme-toggle]')?.addEventListener('click', () => {
        const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
        document.documentElement.dataset.theme = next;
        localStorage.setItem('theme', next);
    });

    document.querySelector('[data-logout]')?.addEventListener('click', logoutSession);

    if (window.lucide) {
        window.lucide.createIcons();
    }
}

function renderMetric(id, value, currency = appState.currency) {
    const el = document.getElementById(id);
    if (el) el.textContent = money(value, currency);
}

function renderTransactionsTable(targetId, transactions = appState.transactions) {
    const tbody = document.getElementById(targetId);
    if (!tbody) return;

    if (!transactions.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="empty-state">No transactions yet.</td></tr>';
        return;
    }

    tbody.innerHTML = transactions.map(item => `
        <tr>
            <td><strong>${escapeHtml(item.description)}</strong></td>
            <td><span class="tag">${escapeHtml(item.category)}</span></td>
            <td>${escapeHtml(item.transaction_date || item.date)}</td>
            <td>${escapeHtml(item.currency || appState.currency)}</td>
            <td class="amount ${item.type}">${item.type === 'income' ? '+' : '-'}${money(item.amount, item.currency || appState.currency)}</td>
            <td>
                <button class="btn" data-edit-transaction="${item.transaction_id || item.id}">Edit</button>
                <button class="btn danger" data-delete-transaction="${item.transaction_id || item.id}">Delete</button>
            </td>
        </tr>
    `).join('');
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

async function saveTransactionFromForm(form) {
    const submitter = form.querySelector('[type="submit"]');
    setButtonLoading(submitter, true, 'Saving transaction...');
    const payload = Object.fromEntries(new FormData(form).entries());
    payload.csrfToken = appState.csrfToken;

    try {
        const data = await apiFetch('../api/finance.php?action=save_transaction', {
            method: 'POST',
            body: JSON.stringify(payload)
        });

        if (!data.success) {
            appToast(data.message || 'Could not save transaction', 'error');
            return false;
        }

        appToast(data.message || 'Transaction saved');
        await loadAppData('Refreshing transactions...');
        return true;
    } finally {
        setButtonLoading(submitter, false);
    }
}

document.addEventListener('DOMContentLoaded', wireShell);
