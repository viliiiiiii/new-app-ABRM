<?php
ob_start();
$queryBuilder = function (array $overrides = []) use ($filters) {
    $base = array_filter([
        'user_id' => $filters['user_id'] ?? null,
        'module' => $filters['module'] ?? null,
        'action' => $filters['action'] ?? null,
        'severity' => $filters['severity'] ?? null,
        'from' => $filters['from'] ?? null,
        'to' => $filters['to'] ?? null,
        'query' => $filters['query'] ?? null,
    ], fn($value) => $value !== null && $value !== '');
    $merged = array_merge($base, $overrides);
    return http_build_query($merged);
};
?>
<div class="page-header">
    <div>
        <h1>Activity &amp; Amendment log</h1>
        <p>Review high-risk actions, see side-by-side diffs, and export for audits.</p>
    </div>
    <div class="badges">
        <span class="badge">Total <?= (int)($summary['total'] ?? 0) ?></span>
        <span class="badge warning">Warnings <?= (int)($summary['warning'] ?? 0) ?></span>
        <span class="badge danger">Critical <?= (int)($summary['critical'] ?? 0) ?></span>
    </div>
</div>
<section class="card">
    <h2>Filters</h2>
    <form method="get" class="filter-grid">
        <select name="user_id">
            <option value="">Any user</option>
            <?php foreach ($options['users'] as $u): ?>
                <option value="<?= (int)$u['id'] ?>" <?= (int)($filters['user_id'] ?? 0) === (int)$u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="module">
            <option value="">Any module</option>
            <?php foreach ($options['modules'] as $module): ?>
                <option value="<?= htmlspecialchars($module) ?>" <?= ($filters['module'] ?? '') === $module ? 'selected' : '' ?>><?= htmlspecialchars(ucwords(str_replace('_', ' ', $module))) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="action">
            <option value="">Any action</option>
            <?php foreach ($options['actions'] as $action): ?>
                <option value="<?= htmlspecialchars($action) ?>" <?= ($filters['action'] ?? '') === $action ? 'selected' : '' ?>><?= htmlspecialchars($action) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="severity">
            <option value="">Any severity</option>
            <?php foreach (['info' => 'Info', 'warning' => 'Warning', 'critical' => 'Critical'] as $value => $label): ?>
                <option value="<?= $value ?>" <?= ($filters['severity'] ?? '') === $value ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
        <label>From <input type="date" name="from" value="<?= htmlspecialchars($filters['from'] ?? '') ?>"></label>
        <label>To <input type="date" name="to" value="<?= htmlspecialchars($filters['to'] ?? '') ?>"></label>
        <input type="search" name="query" placeholder="Search description or payload" value="<?= htmlspecialchars($filters['query'] ?? '') ?>">
        <button class="btn" type="submit">Apply</button>
        <a class="btn ghost" href="/activity">Reset</a>
    </form>
</section>
<section class="card">
    <div class="table-actions">
        <strong>Recent entries</strong>
        <form method="post" action="/activity/export" class="inline">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
            <?php foreach (['user_id','module','action','severity','from','to','query'] as $key): ?>
                <input type="hidden" name="<?= $key ?>" value="<?= htmlspecialchars($filters[$key] ?? '') ?>">
            <?php endforeach; ?>
            <button class="btn ghost" type="submit" name="format" value="pdf">Export PDF</button>
            <button class="btn ghost" type="submit" name="format" value="excel">Export Excel</button>
        </form>
    </div>
    <table class="data-table activity-table">
        <thead>
            <tr>
                <th>Severity</th>
                <th>Time</th>
                <th>User</th>
                <th>Module</th>
                <th>Action</th>
                <th>Description</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($entries as $entry): ?>
                <tr class="<?= !empty($entry['alert_flag']) ? 'activity-alert-row' : '' ?>">
                    <td>
                        <span class="severity-pill severity-<?= htmlspecialchars($entry['severity'] ?? 'info') ?>">
                            <?= strtoupper(htmlspecialchars($entry['severity'] ?? 'info')) ?>
                        </span>
                        <?php if (!empty($entry['alert_flag'])): ?>
                            <span class="badge danger">Alert</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($entry['created_at']) ?></td>
                    <td><?= htmlspecialchars($entry['user_name'] ?? 'System') ?></td>
                    <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', $entry['module']))) ?></td>
                    <td><?= htmlspecialchars($entry['action_type']) ?></td>
                    <td><?= htmlspecialchars($entry['description']) ?></td>
                    <td>
                        <a class="btn ghost" href="/activity?<?= $queryBuilder(['view' => (int)$entry['id']]) ?>">Inspect</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$entries): ?>
                <tr>
                    <td colspan="7" class="muted">No activity recorded for the selected filters.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>
<?php if ($detail): ?>
<section class="card activity-detail">
    <h2>Entry #<?= (int)$detail['id'] ?> details</h2>
    <p class="muted">Logged on <?= htmlspecialchars($detail['created_at']) ?> from <?= htmlspecialchars($detail['ip'] ?? 'unknown IP') ?></p>
    <div class="detail-grid">
        <div><span class="muted">User</span><strong><?= htmlspecialchars($detail['user_name'] ?? 'System') ?></strong></div>
        <div><span class="muted">Module</span><strong><?= htmlspecialchars($detail['module']) ?></strong></div>
        <div><span class="muted">Action</span><strong><?= htmlspecialchars($detail['action_type']) ?></strong></div>
        <div><span class="muted">Severity</span><span class="severity-pill severity-<?= htmlspecialchars($detail['severity'] ?? 'info') ?>"><?= strtoupper(htmlspecialchars($detail['severity'] ?? 'info')) ?></span></div>
    </div>
    <p><?= htmlspecialchars($detail['description']) ?></p>
    <?php if (!empty($detail['alert_flag'])): ?>
        <p class="alert-callout">This entry triggered an alert for privileged users.</p>
    <?php endif; ?>
    <h3>Diff view</h3>
    <?php if ($diff): ?>
        <div class="diff-grid">
            <div class="diff-head">Field</div>
            <div class="diff-head">Previous</div>
            <div class="diff-head">New</div>
            <?php foreach ($diff as $change): ?>
                <div><?= htmlspecialchars($change['field']) ?></div>
                <div><code><?= htmlspecialchars(is_scalar($change['before']) ? (string)$change['before'] : json_encode($change['before'])) ?></code></div>
                <div><code><?= htmlspecialchars(is_scalar($change['after']) ? (string)$change['after'] : json_encode($change['after'])) ?></code></div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="muted">No field-level differences recorded.</p>
    <?php endif; ?>
    <details>
        <summary>Raw payloads</summary>
        <div class="payload-columns">
            <div>
                <h4>Before</h4>
                <pre><?= htmlspecialchars(json_encode($detail['before_state'], JSON_PRETTY_PRINT)) ?></pre>
            </div>
            <div>
                <h4>After</h4>
                <pre><?= htmlspecialchars(json_encode($detail['after_state'], JSON_PRETTY_PRINT)) ?></pre>
            </div>
        </div>
    </details>
</section>
<?php endif; ?>
<?php
$content = ob_get_clean();
$title = 'Activity log';
include __DIR__ . '/../layout/main_layout.php';
