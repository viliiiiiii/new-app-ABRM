<?php
use Core\CSRF;
use Core\Notifications;
use Core\Database;

$notificationsService = new Notifications();
$initialNotifications = isset($user) ? $notificationsService->latest($user['id']) : [];
$initialUnread = isset($user) ? $notificationsService->unreadCount($user['id']) : 0;
$systemBanner = null;
try {
    $systemBanner = Database::connection()->query('SELECT * FROM system_messages LIMIT 1')->fetch();
} catch (Throwable $e) {
    $systemBanner = null;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= htmlspecialchars($user['theme_preference'] ?? 'light') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'ABRM Management') ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <script defer src="/assets/js/app.js"></script>
</head>
<body data-theme="<?= htmlspecialchars($user['theme_preference'] ?? 'light') ?>">
<div class="app-shell">
    <aside class="sidebar">
        <div class="logo">ABRM</div>
        <nav>
            <a href="/">Landing</a>
            <a href="/lost-and-found">Lost &amp; Found</a>
            <a href="/taxi-log">Taxi Log</a>
            <a href="/inventory">Inventory</a>
            <a href="/doctor-log">Doctor Log</a>
            <a href="/notes">Notes</a>
            <a href="/profile">Profile</a>
            <a href="/activity">Activity</a>
            <a href="/users">Users</a>
            <a href="/sectors">Sectors</a>
            <a href="/cms">CMS</a>
        </nav>
    </aside>
    <main>
        <header class="top-bar">
            <button id="theme-toggle" data-csrf="<?= htmlspecialchars(CSRF::token()) ?>" class="icon-btn" title="Toggle theme">🌓</button>
            <button class="icon-btn" id="search-trigger" title="Search (Ctrl+K)">🔍</button>
            <div class="search-inline">
                <input type="search" id="global-search" placeholder="Global search">
            </div>
            <div class="top-bar-right">
                <div class="notifications" id="notification-bell">
                    <span class="badge"><?= (int)$initialUnread ?></span>
                    <ul id="notification-list">
                        <?php foreach ($initialNotifications as $notification): ?>
                            <li><?= htmlspecialchars($notification['message']) ?></li>
                        <?php endforeach; ?>
                        <?php if (!$initialNotifications): ?><li class="muted">No notifications</li><?php endif; ?>
                    </ul>
                </div>
                <div class="user-menu">
                    <span><?= htmlspecialchars($user['name'] ?? '') ?></span>
                    <a href="/logout.php">Logout</a>
                </div>
            </div>
        </header>
        <?php if ($systemBanner && $systemBanner['is_enabled']): ?>
            <div class="system-banner system-<?= htmlspecialchars($systemBanner['message_type']) ?>"><?= htmlspecialchars($systemBanner['message_text']) ?></div>
        <?php endif; ?>
        <section class="content">
            <?= $content ?? '' ?>
        </section>
    </main>
</div>
<div id="search-overlay">
    <div class="overlay-content">
        <input type="search" id="search-input" placeholder="Search everywhere">
        <ul id="search-results"></ul>
    </div>
</div>
</body>
</html>
