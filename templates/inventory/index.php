<?php
ob_start();
?>
<div class="page-header">
    <div>
        <h1>Inventory</h1>
        <p>Manage quantities, movements, and stocktakes.</p>
    </div>
    <div class="badges">
        <span class="badge danger">Low stock: <?= count($alerts['low']) ?></span>
        <span class="badge warning">Overstock: <?= count($alerts['over']) ?></span>
    </div>
</div>
<section class="card">
    <h2>Filters</h2>
    <form method="get" class="filter-grid">
        <input name="q" placeholder="Search name or SKU" value="<?= htmlspecialchars($filters['query'] ?? '') ?>">
        <input name="category" placeholder="Category" value="<?= htmlspecialchars($filters['category'] ?? '') ?>">
        <input name="location" placeholder="Location" value="<?= htmlspecialchars($filters['location'] ?? '') ?>">
        <select name="status">
            <option value="">Any status</option>
            <?php foreach (['active','in_use','in_repair','scrapped','archived'] as $status): ?>
                <option value="<?= $status ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>><?= ucwords(str_replace('_',' ', $status)) ?></option>
            <?php endforeach; ?>
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
        <form method="post" action="/inventory/save-filter" class="inline">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
            <?php foreach (['q','status','category','location'] as $key): ?>
                <input type="hidden" name="<?= $key ?>" value="<?= htmlspecialchars($filters[$key] ?? '') ?>">
            <?php endforeach; ?>
            <input name="filter_name" placeholder="Name" required>
            <button class="btn small">Save current</button>
        </form>
    </details>
</section>
<section class="card">
    <h2>Register item</h2>
    <form method="post" action="/inventory">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
        <div class="form-grid">
            <input name="name" placeholder="Name" required>
            <input name="sku" placeholder="SKU">
            <input name="category" placeholder="Category">
            <input name="location" placeholder="Location">
            <input type="number" name="quantity_on_hand" placeholder="Qty" min="0">
            <input type="number" name="min_stock" placeholder="Min" min="0">
            <input type="number" name="max_stock" placeholder="Max" min="0">
            <select name="condition">
                <option value="new">New</option>
                <option value="used">Used</option>
                <option value="damaged">Damaged</option>
            </select>
            <select name="status">
                <option value="active">Active</option>
                <option value="in_use">In use</option>
                <option value="in_repair">In repair</option>
                <option value="scrapped">Scrapped</option>
            </select>
        </div>
        <textarea name="notes" placeholder="Notes"></textarea>
        <button class="btn" type="submit">Save item</button>
    </form>
</section>
<section class="card">
    <h2>Movement</h2>
    <form method="post" action="/inventory/movement">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
        <div class="form-grid">
            <select name="item_id" required>
                <?php foreach ($items as $item): ?>
                    <option value="<?= (int)$item['id'] ?>"><?= htmlspecialchars($item['name']) ?> (<?= (int)$item['quantity_on_hand'] ?>)</option>
                <?php endforeach; ?>
            </select>
            <label>Date <input type="datetime-local" name="movement_time" value="<?= date('Y-m-d\TH:i') ?>"></label>
            <input name="from_location" placeholder="From">
            <input name="to_location" placeholder="To">
            <input type="number" name="quantity_moved" placeholder="Quantity (+/-)" required>
            <input name="reason" placeholder="Reason (issue, transfer, repair)">
        </div>
        <label>Issued signature <canvas class="signature" data-target="issued_signature"></canvas><input type="hidden" name="issued_signature"></label>
        <label>Received signature <canvas class="signature" data-target="received_signature"></canvas><input type="hidden" name="received_signature"></label>
        <textarea name="notes" placeholder="Movement notes"></textarea>
        <button class="btn" type="submit">Log movement</button>
    </form>
</section>
<section class="card">
    <div class="table-actions">
        <form method="post" action="/inventory/export" class="inline">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
            <?php foreach (['q','status','category','location'] as $key): ?>
                <input type="hidden" name="<?= $key ?>" value="<?= htmlspecialchars($filters[$key] ?? '') ?>">
            <?php endforeach; ?>
            <button class="btn ghost" type="submit" name="format" value="pdf">Export PDF</button>
            <button class="btn ghost" type="submit" name="format" value="excel">Export Excel</button>
        </form>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Name</th>
                <th>Qty</th>
                <th>Location</th>
                <th>Status</th>
                <th>Condition</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['sku']) ?></td>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= (int)$item['quantity_on_hand'] ?></td>
                    <td><?= htmlspecialchars($item['location']) ?></td>
                    <td><span class="badge"><?= htmlspecialchars($item['status']) ?></span></td>
                    <td><?= htmlspecialchars($item['condition']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<section class="card">
    <h2>Stock alerts</h2>
    <div class="alert-grid">
        <div>
            <h3>Low stock</h3>
            <ul>
                <?php foreach ($alerts['low'] as $alert): ?>
                    <li><?= htmlspecialchars($alert['name']) ?> (<?= (int)$alert['quantity_on_hand'] ?>/<?= (int)$alert['min_stock'] ?>)</li>
                <?php endforeach; ?>
                <?php if (!$alerts['low']): ?><li class="muted">No alerts</li><?php endif; ?>
            </ul>
        </div>
        <div>
            <h3>Overstock</h3>
            <ul>
                <?php foreach ($alerts['over'] as $alert): ?>
                    <li><?= htmlspecialchars($alert['name']) ?> (<?= (int)$alert['quantity_on_hand'] ?>/<?= (int)$alert['max_stock'] ?>)</li>
                <?php endforeach; ?>
                <?php if (!$alerts['over']): ?><li class="muted">No alerts</li><?php endif; ?>
            </ul>
        </div>
    </div>
</section>
<section class="card">
    <h2>Stocktake</h2>
    <form method="post" action="/inventory/start-stocktake" class="inline">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
        <input name="name" placeholder="Session name" required>
        <input name="location" placeholder="Location" required>
        <input type="date" name="session_date" value="<?= date('Y-m-d') ?>">
        <button class="btn" type="submit">Start session</button>
    </form>
    <ul class="session-list">
        <?php foreach ($sessions as $session): ?>
            <li><a href="/inventory?session=<?= (int)$session['id'] ?>"><?= htmlspecialchars($session['name']) ?> (<?= htmlspecialchars($session['session_date']) ?>)</a></li>
        <?php endforeach; ?>
        <?php if (!$sessions): ?><li class="muted">No sessions</li><?php endif; ?>
    </ul>
    <?php if ($sessionItems): ?>
        <form method="post" action="/inventory/update-stocktake">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Expected</th>
                        <th>Counted</th>
                        <th>Variance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sessionItems as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= (int)$item['expected_quantity'] ?></td>
                            <td><input type="number" name="counts[<?= (int)$item['id'] ?>]" value="<?= (int)$item['counted_quantity'] ?>"></td>
                            <td><?= (int)$item['variance'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button class="btn" type="submit">Update counts</button>
        </form>
    <?php endif; ?>
</section>
<?php
$content = ob_get_clean();
$title = 'Inventory';
include __DIR__ . '/../layout/main_layout.php';
