<?php
use Core\CSRF;
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
<body>
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
            <a href="/users">Users</a>
            <a href="/sectors">Sectors</a>
            <a href="/activity">Activity</a>
            <a href="/cms">CMS</a>
        </nav>
    </aside>
    <main>
        <header class="top-bar">
            <div class="search">
                <input type="search" id="global-search" placeholder="Global search" />
            </div>
            <div class="user-menu">
                <span><?= htmlspecialchars($user['name'] ?? '') ?></span>
                <a href="/logout.php">Logout</a>
            </div>
        </header>
        <section class="content">
            <?= $content ?? '' ?>
        </section>
    </main>
</div>
</body>
</html>
