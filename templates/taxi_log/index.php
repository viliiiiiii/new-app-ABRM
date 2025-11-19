<?php ob_start(); ?>
<h1>Taxi Log</h1>
<table class="data-table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Start</th>
            <th>Destination</th>
            <th>Guest</th>
            <th>Room</th>
            <th>Driver</th>
            <th>Price</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($entries as $entry): ?>
            <tr>
                <td><?= htmlspecialchars($entry['ride_time']) ?></td>
                <td><?= htmlspecialchars($entry['start_location']) ?></td>
                <td><?= htmlspecialchars($entry['destination']) ?></td>
                <td><?= htmlspecialchars($entry['guest_name']) ?></td>
                <td><?= htmlspecialchars($entry['room_number']) ?></td>
                <td><?= htmlspecialchars($entry['driver_name']) ?></td>
                <td><?= htmlspecialchars($entry['price']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php $content = ob_get_clean(); $title = 'Taxi Log'; include __DIR__ . '/../layout/main_layout.php';
