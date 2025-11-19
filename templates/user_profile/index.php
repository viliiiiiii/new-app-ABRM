<?php ob_start(); ?>
<h1>Profile</h1>
<div class="card">
    <p>Name: <?= htmlspecialchars($user['name']) ?></p>
    <p>Email: <?= htmlspecialchars($user['email']) ?></p>
    <p>Role: <?= htmlspecialchars($user['role_key']) ?></p>
</div>
<h2>Recent logins</h2>
<table class="data-table">
    <thead>
        <tr><th>IP</th><th>User agent</th><th>Time</th></tr>
    </thead>
    <tbody>
        <?php foreach ($history as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['ip_address']) ?></td>
                <td><?= htmlspecialchars($row['user_agent']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php $content = ob_get_clean(); $title = 'Profile'; include __DIR__ . '/../layout/main_layout.php';
