<?php
ob_start();
?>
<div class="page-header">
    <div>
        <h1>Lost &amp; Found</h1>
        <p>Track lifecycle states, reminders, and releases.</p>
    </div>
    <div class="actions">
        <a class="btn ghost" href="/lost-and-found/recycle-bin">Recycle bin</a>
        <button type="button" class="btn" data-open="release-modal" id="release-selected" disabled>Release selected</button>
        <button type="button" class="btn" data-open="state-modal" id="bulk-state" disabled>Change state</button>
    </div>
</div>
<div class="layout-grid">
    <section class="card">
        <h2>Filters</h2>
        <form class="filter-grid" method="get">
            <input type="search" name="q" placeholder="Search name, owner, description" value="<?= htmlspecialchars($filters['query'] ?? '') ?>">
            <select name="state">
                <option value="">Any state</option>
                <?php foreach (['new' => 'New', 'under_review' => 'Under review', 'stored' => 'Stored', 'pending_release' => 'Pending release', 'released' => 'Released', 'archived' => 'Archived'] as $value => $label): ?>
                    <option value="<?= $value ?>" <?= ($filters['state'] ?? '') === $value ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <input name="category" placeholder="Category" value="<?= htmlspecialchars($filters['category'] ?? '') ?>">
            <input name="tag" placeholder="Tag" value="<?= htmlspecialchars($filters['tag'] ?? '') ?>">
            <label>From <input type="date" name="from" value="<?= htmlspecialchars($filters['from'] ?? '') ?>"></label>
            <label>To <input type="date" name="to" value="<?= htmlspecialchars($filters['to'] ?? '') ?>"></label>
            <label class="toggle">
                <input type="checkbox" name="high_value" value="1" <?= !empty($filters['high_value']) ? 'checked' : '' ?>> High value only
            </label>
            <button class="btn" type="submit">Apply</button>
        </form>
        <details>
            <summary>Saved views</summary>
            <ul class="saved-list">
                <?php foreach ($savedFilters as $view): ?>
                    <li data-filters='<?= htmlspecialchars($view['filters_json']) ?>'><?= htmlspecialchars($view['name']) ?></li>
                <?php endforeach; ?>
            </ul>
            <form method="post" action="/lost-and-found/save-filter" class="inline">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                <input type="hidden" name="q" value="<?= htmlspecialchars($filters['query'] ?? '') ?>">
                <input type="hidden" name="state" value="<?= htmlspecialchars($filters['state'] ?? '') ?>">
                <input type="hidden" name="category" value="<?= htmlspecialchars($filters['category'] ?? '') ?>">
                <input type="hidden" name="tag" value="<?= htmlspecialchars($filters['tag'] ?? '') ?>">
                <input type="hidden" name="from" value="<?= htmlspecialchars($filters['from'] ?? '') ?>">
                <input type="hidden" name="to" value="<?= htmlspecialchars($filters['to'] ?? '') ?>">
                <input type="hidden" name="high_value" value="<?= htmlspecialchars($filters['high_value'] ?? '') ?>">
                <input type="text" name="filter_name" placeholder="Name this filter" required>
                <button class="btn small">Save current</button>
            </form>
        </details>
    </section>
    <section class="card">
        <h2>Stats</h2>
        <div class="stats-grid">
            <?php foreach ($stats as $stat): ?>
                <div>
                    <span class="muted"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $stat['lifecycle_state']))) ?></span>
                    <strong><?= (int)$stat['total'] ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
        <h3>Upcoming reminders</h3>
        <ul>
            <?php foreach ($reminders as $reminder): ?>
                <li><?= htmlspecialchars($reminder['item_name']) ?> <span class="muted">due <?= htmlspecialchars($reminder['reminder_date']) ?></span></li>
            <?php endforeach; ?>
            <?php if (!$reminders): ?>
                <li class="muted">Nothing due</li>
            <?php endif; ?>
        </ul>
    </section>
</div>
<section class="card">
    <h2>Register new item</h2>
    <form method="post">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
        <div class="form-grid">
            <input name="item_name" placeholder="Item name" required>
            <input name="category" placeholder="Category">
            <input name="location_area" placeholder="Area">
            <input name="location_building" placeholder="Building">
            <input name="location_floor" placeholder="Floor">
            <input name="location_exact" placeholder="Exact spot">
            <input name="owner_name" placeholder="Possible owner">
            <input name="owner_contact" placeholder="Owner contact">
            <input name="tags" placeholder="Tags (comma separated)">
            <label>Found at <input type="datetime-local" name="found_at" value="<?= date('Y-m-d\TH:i') ?>"></label>
            <label>Reminder <input type="date" name="reminder_date"></label>
            <label>Retention <input type="date" name="retention_date"></label>
            <select name="lifecycle_state">
                <option value="new">New</option>
                <option value="under_review">Under review</option>
                <option value="stored">Stored</option>
            </select>
            <label class="toggle"><input type="checkbox" name="high_value"> High value</label>
            <label class="toggle"><input type="checkbox" name="sensitive_document"> Sensitive document</label>
        </div>
        <textarea name="description" placeholder="Description"></textarea>
        <textarea name="notes" placeholder="Internal notes"></textarea>
        <button class="btn" type="submit">Save item</button>
    </form>
</section>
<section class="card">
    <div class="table-actions">
        <form method="post" action="/lost-and-found/export" class="inline">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="q" value="<?= htmlspecialchars($filters['query'] ?? '') ?>">
            <input type="hidden" name="state" value="<?= htmlspecialchars($filters['state'] ?? '') ?>">
            <input type="hidden" name="category" value="<?= htmlspecialchars($filters['category'] ?? '') ?>">
            <input type="hidden" name="tag" value="<?= htmlspecialchars($filters['tag'] ?? '') ?>">
            <input type="hidden" name="from" value="<?= htmlspecialchars($filters['from'] ?? '') ?>">
            <input type="hidden" name="to" value="<?= htmlspecialchars($filters['to'] ?? '') ?>">
            <input type="hidden" name="high_value" value="<?= htmlspecialchars($filters['high_value'] ?? '') ?>">
            <button class="btn ghost" type="submit" name="format" value="pdf">Export PDF</button>
            <button class="btn ghost" type="submit" name="format" value="excel">Export Excel</button>
        </form>
    </div>
    <table class="data-table" id="lost-table">
        <thead>
            <tr>
                <th><input type="checkbox" id="select-all"></th>
                <th>Code</th>
                <th>Item</th>
                <th>State</th>
                <th>Location</th>
                <th>Owner</th>
                <th>Found</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr data-id="<?= (int)$item['id'] ?>">
                    <td><input type="checkbox" class="row-check"></td>
                    <td><?= htmlspecialchars($item['item_code']) ?></td>
                    <td>
                        <strong><?= htmlspecialchars($item['item_name']) ?></strong>
                        <?php if ($item['tags']): ?><span class="badge"><?= htmlspecialchars($item['tags']) ?></span><?php endif; ?>
                        <?php if ($item['high_value']): ?><span class="badge danger">High value</span><?php endif; ?>
                        <?php if ($item['sensitive_document']): ?><span class="badge warning">Sensitive</span><?php endif; ?>
                    </td>
                    <td><span class="state state-<?= htmlspecialchars($item['lifecycle_state']) ?>"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $item['lifecycle_state']))) ?></span></td>
                    <td><?= htmlspecialchars(trim(($item['location_area'] ?? '') . ' ' . ($item['location_exact'] ?? ''))) ?></td>
                    <td><?= htmlspecialchars($item['owner_name'] ?? 'Unknown') ?></td>
                    <td><?= htmlspecialchars(date('Y-m-d', strtotime($item['found_at']))) ?></td>
                    <td>
                        <form method="post" action="/lost-and-found/delete" class="inline">
                            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                            <input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>">
                            <button class="btn ghost" type="submit">Archive</button>
                        </form>
                        <a class="btn ghost" href="/lost-and-found?history=<?= (int)$item['id'] ?>">History</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php if ($history): ?>
<section class="card">
    <h2>Version history</h2>
    <div class="history-grid">
        <div>
            <h3>State changes</h3>
            <ul>
                <?php foreach ($history['states'] as $state): ?>
                    <li><strong><?= htmlspecialchars($state['state']) ?></strong> by user #<?= (int)$state['changed_by'] ?> on <?= htmlspecialchars($state['changed_at']) ?><br><span class="muted"><?= htmlspecialchars($state['notes']) ?></span></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div>
            <h3>Text revisions</h3>
            <ul>
                <?php foreach ($history['versions'] as $version): ?>
                    <li><strong><?= htmlspecialchars($version['field']) ?></strong> updated on <?= htmlspecialchars($version['changed_at']) ?><br><em>Old:</em> <?= htmlspecialchars($version['old_value']) ?><br><em>New:</em> <?= htmlspecialchars($version['new_value']) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <form method="post" action="/lost-and-found/update-text">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="item_id" value="<?= (int)$historyItem ?>">
        <textarea name="description" placeholder="Description"></textarea>
        <textarea name="notes" placeholder="Notes"></textarea>
        <button class="btn" type="submit">Update text</button>
    </form>
</section>
<?php endif; ?>

<div class="modal" id="state-modal">
    <div class="modal-content">
        <form method="post" action="/lost-and-found/change-state">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="item_ids" id="state-item-ids">
            <h3>Change lifecycle state</h3>
            <select name="state" required>
                <option value="under_review">Under review</option>
                <option value="stored">Stored</option>
                <option value="pending_release">Pending release</option>
                <option value="released">Released</option>
                <option value="archived">Archived</option>
            </select>
            <textarea name="state_notes" placeholder="Notes"></textarea>
            <div class="modal-actions">
                <button class="btn" type="submit">Apply</button>
                <button class="btn ghost" type="button" data-close>Cancel</button>
            </div>
        </form>
    </div>
</div>
<div class="modal" id="release-modal">
    <div class="modal-content">
        <form method="post" action="/lost-and-found/release">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="item_id" id="release-item-id">
            <h3>Release item</h3>
            <input name="recipient_name" placeholder="Recipient name" required>
            <input name="recipient_id" placeholder="ID / Passport">
            <input name="recipient_contact" placeholder="Contact">
            <input name="staff_name" placeholder="Staff" value="<?= htmlspecialchars($user['name']) ?>">
            <label>Staff signature <canvas class="signature" data-target="staff_signature"></canvas><input type="hidden" name="staff_signature"></label>
            <label>Recipient signature <canvas class="signature" data-target="recipient_signature"></canvas><input type="hidden" name="recipient_signature"></label>
            <div class="modal-actions">
                <button class="btn" type="submit">Confirm release</button>
                <button class="btn ghost" type="button" data-close>Cancel</button>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Lost & Found';
include __DIR__ . '/../layout/main_layout.php';
