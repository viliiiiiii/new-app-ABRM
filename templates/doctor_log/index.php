<?php
ob_start();
?>
<div class="page-header">
    <h1>Doctor Log</h1>
    <p>Track calls and arrival times.</p>
</div>
<section class="card">
    <h2>Filters</h2>
    <form method="get" class="filter-grid">
        <input name="room" placeholder="Room" value="<?= htmlspecialchars($filters['room'] ?? '') ?>">
        <input name="doctor" placeholder="Doctor" value="<?= htmlspecialchars($filters['doctor'] ?? '') ?>">
        <label>From <input type="date" name="from" value="<?= htmlspecialchars($filters['from'] ?? '') ?>"></label>
        <label>To <input type="date" name="to" value="<?= htmlspecialchars($filters['to'] ?? '') ?>"></label>
        <select name="status">
            <option value="">Any status</option>
            <option value="open" <?= ($filters['status'] ?? '') === 'open' ? 'selected' : '' ?>>Open</option>
            <option value="closed" <?= ($filters['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed</option>
        </select>
        <button class="btn" type="submit">Apply</button>
    </form>
    <details>
        <summary>Saved views</summary>
        <ul class="saved-list">
            <?php foreach ($savedFilters as $view): ?>
                <li data-filters='<?= htmlspecialchars($view['filters_json']) ?>'><?= htmlspecialchars($view['name']) ?></li>
            <?php endforeach; ?>
        </ul>
        <form method="post" action="/doctor-log/save-filter" class="inline">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
            <?php foreach (['room','doctor','from','to','status'] as $key): ?>
                <input type="hidden" name="<?= $key ?>" value="<?= htmlspecialchars($filters[$key] ?? '') ?>">
            <?php endforeach; ?>
            <input name="filter_name" placeholder="Name" required>
            <button class="btn small">Save current</button>
        </form>
    </details>
</section>
<section class="card">
    <h2>New entry</h2>
    <form method="post" action="/doctor-log">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
        <div class="form-grid">
            <input name="room_number" placeholder="Room" required>
            <label>Time called <input type="datetime-local" name="time_called" value="<?= date('Y-m-d\TH:i') ?>"></label>
            <label>Arrival <input type="datetime-local" name="time_arrived"></label>
            <input name="doctor_name" placeholder="Doctor" required>
            <select name="status">
                <option value="open">Open</option>
                <option value="closed">Closed</option>
            </select>
        </div>
        <textarea name="reason" placeholder="Notes"></textarea>
        <button class="btn" type="submit">Save entry</button>
    </form>
</section>
<section class="card">
    <div class="table-actions">
        <form method="post" action="/doctor-log/export" class="inline">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
            <?php foreach (['room','doctor','from','to','status'] as $key): ?>
                <input type="hidden" name="<?= $key ?>" value="<?= htmlspecialchars($filters[$key] ?? '') ?>">
            <?php endforeach; ?>
            <button class="btn ghost" type="submit" name="format" value="pdf">Export PDF</button>
            <button class="btn ghost" type="submit" name="format" value="excel">Export Excel</button>
        </form>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Room</th>
                <th>Time called</th>
                <th>Arrival</th>
                <th>Doctor</th>
                <th>Status</th>
                <th>Notes</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($entries as $entry): ?>
                <tr>
                    <td><?= htmlspecialchars($entry['room_number']) ?></td>
                    <td><?= htmlspecialchars($entry['time_called']) ?></td>
                    <td><?= htmlspecialchars($entry['time_arrived']) ?></td>
                    <td><?= htmlspecialchars($entry['doctor_name']) ?></td>
                    <td><span class="badge <?= $entry['status'] === 'open' ? 'warning' : 'success' ?>"><?= htmlspecialchars($entry['status']) ?></span></td>
                    <td><?= htmlspecialchars($entry['reason']) ?></td>
                    <td>
                        <form method="post" action="/doctor-log/delete" class="inline">
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
<?php
$content = ob_get_clean();
$title = 'Doctor Log';
include __DIR__ . '/../layout/main_layout.php';
