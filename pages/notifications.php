<?php
require_once __DIR__ . '/_layout.php';
$user = require_page_auth($pdo);
ob_start();
?>
<section class="card">
    <div class="section-head"><div><h2>Notifications</h2><p>Budget alerts, payment events, monthly summaries, and security updates.</p></div><button class="btn" id="markRead">Mark all read</button></div>
    <div id="notificationList" class="grid"></div>
</section>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    await loadAppData();
    const list = document.getElementById('notificationList');
    list.innerHTML = appState.notifications.length ? appState.notifications.map(n => `<article class="card"><strong>${escapeHtml(n.title)}</strong><p>${escapeHtml(n.body)}</p><span class="tag">${n.read_at ? 'Read' : 'Unread'}</span></article>`).join('') : '<div class="empty-state">No notifications yet.</div>';
    document.getElementById('markRead').addEventListener('click', async () => {
        await withButtonLoading(document.getElementById('markRead'), 'Updating...', async () => {
            const data = await apiFetch('../api/finance.php?action=mark_notifications_read', { method: 'POST', body: JSON.stringify({ csrfToken: appState.csrfToken }) });
            appToast(data.message, data.success ? 'success' : 'error');
            await loadAppData('Refreshing notifications...');
        });
    });
});
</script>
<?php render_layout('Notifications', 'notifications', ob_get_clean(), $user, ['subtitle' => 'Stay ahead of money events']); ?>
