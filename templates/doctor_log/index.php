<?php ob_start(); ?>
<h1>Doctor Log</h1>
<table class="data-table">
    <thead>
        <tr>
            <th>Room</th>
            <th>Time Called</th>
            <th>Doctor</th>
            <th>Reason</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($entries as $entry): ?>
            <tr>
                <td><?= htmlspecialchars($entry['room_number']) ?></td>
                <td><?= htmlspecialchars($entry['time_called']) ?></td>
                <td><?= htmlspecialchars($entry['doctor_name']) ?></td>
                <td><?= htmlspecialchars($entry['reason']) ?></td>
                <td><?= htmlspecialchars($entry['status']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php $content = ob_get_clean(); $title = 'Doctor Log'; include __DIR__ . '/../layout/main_layout.php';
