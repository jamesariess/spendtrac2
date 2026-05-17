async function getSession() {
    const response = await fetch('../api/session.php', { credentials: 'same-origin' });
    return response.json();
}

async function requirePageSession() {
    try {
        const session = await getSession();
        if (!session.authenticated) {
            window.location.href = '../auth/login.html';
        }
        return session;
    } catch (error) {
        window.location.href = '../auth/login.html';
        return null;
    }
}

async function logoutSession() {
    if (!confirm('Are you sure you want to logout?')) return;

    await fetch('../api/logout.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({})
    });

    window.location.href = '../auth/login.html';
}

