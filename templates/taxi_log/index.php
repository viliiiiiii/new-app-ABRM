<?php
ob_start();
?>
<div class="page-header">
    <div>
        <h1>Taxi Log</h1>
        <p>Record rides with guest/room linkage.</p>
    </div>
    <div>
        <p class="muted">Month <?= htmlspecialchars($filters['month']) ?> • <?= (int)($summary['rides'] ?? 0) ?> rides • $<?= number_format((float)($summary['revenue'] ?? 0), 2) ?></p>
    </div>
</div>
<section class="card">
    <h2>Filters</h2>
    <form method="get" class="filter-grid">
        <label>From <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>"></label>
        <label>To <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>"></label>
        <input name="driver" placeholder="Driver" value="<?= htmlspecialchars($filters['driver'] ?? '') ?>">
        <input name="guest" placeholder="Guest name" value="<?= htmlspecialchars($filters['guest'] ?? '') ?>">
        <input name="room" placeholder="Room #" value="<?= htmlspecialchars($filters['room'] ?? '') ?>">
        <label>Month <input type="month" name="month" value="<?= htmlspecialchars($filters['month']) ?>"></label>
        <button class="btn" type="submit">Apply</button>
    </form>
    <details>
        <summary>Saved views</summary>
        <ul class="saved-list">
            <?php foreach ($savedFilters as $view): ?>
                <li data-filters='<?= htmlspecialchars($view['filters_json']) ?>'><?= htmlspecialchars($view['name']) ?></li>
            <?php endforeach; ?>
        </ul>
        <form method="post" action="/taxi-log/save-filter" class="inline">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
            <?php foreach (['date_from','date_to','driver','guest','room','month'] as $key): ?>
                <input type="hidden" name="<?= $key ?>" value="<?= htmlspecialchars($filters[$key] ?? '') ?>">
            <?php endforeach; ?>
            <input name="filter_name" placeholder="Name" required>
            <button class="btn small" type="submit">Save current</button>
        </form>
    </details>
</section>
<section class="card">
    <h2>Log ride</h2>
    <form method="post" action="/taxi-log">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
        <div class="form-grid">
            <label>Date/time <input type="datetime-local" name="ride_time" value="<?= date('Y-m-d\TH:i') ?>"></label>
            <input name="start_location" placeholder="Start" required>
            <input name="destination" placeholder="Destination" required>
            <input name="guest_name" placeholder="Guest name">
            <input name="room_number" placeholder="Room">
            <input name="driver_name" placeholder="Driver">
            <input type="number" step="0.01" name="price" placeholder="Price">
        </div>
        <textarea name="notes" placeholder="Notes"></textarea>
        <button class="btn" type="submit">Add ride</button>
    </form>
</section>
<section class="card">
    <div class="table-actions">
        <form method="post" action="/taxi-log/export" class="inline">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
            <?php foreach (['date_from','date_to','driver','guest','room','month'] as $key): ?>
                <input type="hidden" name="<?= $key ?>" value="<?= htmlspecialchars($filters[$key] ?? '') ?>">
            <?php endforeach; ?>
            <button class="btn ghost" type="submit" name="format" value="pdf">Export PDF</button>
            <button class="btn ghost" type="submit" name="format" value="excel">Export Excel</button>
        </form>
    </div>
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
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($entries as $entry): ?>
                <tr>
                    <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($entry['ride_time']))) ?></td>
                    <td><?= htmlspecialchars($entry['start_location']) ?></td>
                    <td><?= htmlspecialchars($entry['destination']) ?></td>
                    <td><?= htmlspecialchars($entry['guest_name']) ?></td>
                    <td><?= htmlspecialchars($entry['room_number']) ?></td>
                    <td><?= htmlspecialchars($entry['driver_name']) ?></td>
                    <td>$<?= number_format((float)$entry['price'], 2) ?></td>
                    <td>
                        <form method="post" action="/taxi-log/delete" class="inline">
                            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                            <input type="hidden" name="entry_id" value="<?= (int)$entry['id'] ?>">
                            <button class="btn ghost" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<section class="card">
    <h2>Frequent guests (<?= htmlspecialchars($filters['date_from'] ?? date('Y-m-01')) ?> to <?= htmlspecialchars($filters['date_to'] ?? date('Y-m-t')) ?>)</h2>
    <ul>
        <?php foreach ($frequent as $guest): ?>
            <li><?= htmlspecialchars($guest['guest_name'] ?: 'Unknown') ?> (Room <?= htmlspecialchars($guest['room_number'] ?: 'N/A') ?>) – <?= (int)$guest['total'] ?> rides</li>
        <?php endforeach; ?>
        <?php if (!$frequent): ?>
            <li class="muted">No data</li>
        <?php endif; ?>
    </ul>
</section>
<?php
$content = ob_get_clean();
$title = 'Taxi Log';
include __DIR__ . '/../layout/main_layout.php';
