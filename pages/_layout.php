<?php

declare(strict_types=1);

require_once __DIR__ . '/../backend/conn.php';
require_once __DIR__ . '/../backend/security.php';

function require_page_auth(PDO $pdo): array
{
    bootstrap_remembered_user($pdo);

    if (empty($_SESSION['authenticated']) || empty($_SESSION['user_id'])) {
        header('Location: ../auth/login.html');
        exit;
    }

    $user = current_user($pdo);
    if (!$user) {
        header('Location: ../auth/login.html');
        exit;
    }

    return $user;
}

function user_initials(string $email): string
{
    $name = explode('@', $email)[0] ?: 'User';
    $parts = preg_split('/[^a-z0-9]+/i', $name) ?: [];
    $initials = '';

    foreach ($parts as $part) {
        if ($part !== '') {
            $initials .= strtoupper($part[0]);
        }
        if (strlen($initials) >= 2) {
            break;
        }
    }

    return $initials ?: strtoupper(substr($email, 0, 2));
}

function nav_items(): array
{
    return [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => 'dashboard.php', 'icon' => 'layout-dashboard'],
        ['key' => 'expenses', 'label' => 'Expenses', 'href' => 'expenses.php', 'icon' => 'receipt'],
        ['key' => 'income', 'label' => 'Income', 'href' => 'income.php', 'icon' => 'wallet'],
        ['key' => 'analytics', 'label' => 'Analytics', 'href' => 'analytics.php', 'icon' => 'chart-no-axes-combined'],
        ['key' => 'reports', 'label' => 'Reports', 'href' => 'reports.php', 'icon' => 'file-bar-chart'],
        ['key' => 'budget', 'label' => 'Budget Planner', 'href' => 'budget.php', 'icon' => 'piggy-bank'],
        ['key' => 'transactions', 'label' => 'Transactions', 'href' => 'transactions.php', 'icon' => 'list-filter'],
        ['key' => 'notifications', 'label' => 'Notifications', 'href' => 'notifications.php', 'icon' => 'bell'],
        ['key' => 'settings', 'label' => 'Settings', 'href' => 'settings.php', 'icon' => 'settings'],
        ['key' => 'profile', 'label' => 'User Profile', 'href' => 'profile.php', 'icon' => 'circle-user-round'],
    ];
}

function render_layout(string $title, string $active, string $content, array $user, array $options = []): void
{
    $role = $user['role'] ?? 'user';
    $email = htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8');
    $displayName = htmlspecialchars($user['display_name'] ?? explode('@', $user['email'])[0], ENT_QUOTES, 'UTF-8');
    $initials = htmlspecialchars(user_initials($user['email']), ENT_QUOTES, 'UTF-8');
    $subtitle = htmlspecialchars($options['subtitle'] ?? 'Global financial command center', ENT_QUOTES, 'UTF-8');
    ?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?> | SpendTrack</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/app.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="app-shell">
        <aside class="app-sidebar" id="appSidebar" aria-label="Primary navigation">
            <div class="sidebar-brand">
                <a href="dashboard.php" class="brand-lockup" aria-label="SpendTrack dashboard">
                    <span class="brand-mark"><i data-lucide="badge-dollar-sign"></i></span>
                    <span class="brand-text">SpendTrack</span>
                </a>
                <button class="icon-button desktop-only" type="button" data-sidebar-collapse aria-label="Collapse sidebar">
                    <i data-lucide="panel-left-close"></i>
                </button>
            </div>

            <nav class="sidebar-menu">
                <?php foreach (nav_items() as $item): ?>
                    <a class="sidebar-link <?= $active === $item['key'] ? 'active' : '' ?>" href="<?= $item['href'] ?>" data-nav="<?= $item['key'] ?>">
                        <i data-lucide="<?= $item['icon'] ?>"></i>
                        <span><?= $item['label'] ?></span>
                    </a>
                <?php endforeach; ?>

                <?php if ($role === 'admin'): ?>
                    <a class="sidebar-link <?= $active === 'admin' ? 'active' : '' ?>" href="admin.php" data-nav="admin">
                        <i data-lucide="shield-check"></i>
                        <span>Admin Panel</span>
                    </a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-user">
                <a href="profile.php" class="user-chip">
                    <span class="avatar"><?= $initials ?></span>
                    <span class="user-meta">
                        <strong><?= $displayName ?></strong>
                        <small><?= $email ?></small>
                    </span>
                </a>
                <button class="logout-button" type="button" data-logout>
                    <i data-lucide="log-out"></i>
                    <span>Logout</span>
                </button>
            </div>
        </aside>

        <div class="sidebar-backdrop" data-sidebar-backdrop></div>

        <main class="app-main">
            <header class="app-topbar">
                <div class="topbar-title">
                    <button class="icon-button mobile-only" type="button" data-sidebar-open aria-label="Open sidebar">
                        <i data-lucide="menu"></i>
                    </button>
                    <div>
                        <p><?= $subtitle ?></p>
                        <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
                    </div>
                </div>
                <div class="topbar-actions">
                    <button class="icon-button" type="button" data-theme-toggle aria-label="Toggle dark mode">
                        <i data-lucide="moon"></i>
                    </button>
                    <a class="icon-button" href="notifications.php" aria-label="Notifications">
                        <i data-lucide="bell"></i>
                    </a>
                    <a class="topbar-profile" href="profile.php">
                        <span class="avatar small"><?= $initials ?></span>
                    </a>
                </div>
            </header>

            <section class="page-content">
                <?= $content ?>
            </section>
        </main>
    </div>

    <div class="toast" id="appToast" role="status" aria-live="polite"></div>
    <script src="../assets/js/app-auth.js"></script>
    <script src="../assets/js/app.js"></script>
</body>
</html>
    <?php
}
