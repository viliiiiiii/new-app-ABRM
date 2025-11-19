<?php ob_start(); ?>
<h1>User Management</h1>
<table class="data-table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Sector</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['role_name']) ?></td>
                <td><?= htmlspecialchars($row['sector_name']) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php $content = ob_get_clean(); $title = 'User Management'; include __DIR__ . '/../layout/main_layout.php';
