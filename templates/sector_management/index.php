<?php ob_start(); ?>
<h1>Sector Management</h1>
<table class="data-table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Status</th>
            <th>Supervisors</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($sectors as $sector): ?>
            <tr>
                <td><?= htmlspecialchars($sector['name']) ?></td>
                <td><?= htmlspecialchars($sector['status']) ?></td>
                <td><?= htmlspecialchars($sector['supervisors']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php $content = ob_get_clean(); $title = 'Sector Management'; include __DIR__ . '/../layout/main_layout.php';
