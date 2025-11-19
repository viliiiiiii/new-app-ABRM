<?php ob_start(); ?>
<h1>Activity Log</h1>
<table class="data-table">
    <thead>
        <tr>
            <th>User</th>
            <th>Action</th>
            <th>Module</th>
            <th>Description</th>
            <th>Time</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($entries as $entry): ?>
            <tr>
                <td><?= htmlspecialchars($entry['user_name']) ?></td>
                <td><?= htmlspecialchars($entry['action_type']) ?></td>
                <td><?= htmlspecialchars($entry['module']) ?></td>
                <td><?= htmlspecialchars($entry['description']) ?></td>
                <td><?= htmlspecialchars($entry['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php $content = ob_get_clean(); $title = 'Activity'; include __DIR__ . '/../layout/main_layout.php';
