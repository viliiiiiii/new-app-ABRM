<?php
ob_start();
?>
<div class="page-header">
    <h1>Notes &amp; Collaboration</h1>
    <div class="filters-inline">
        <form method="get" class="filter-grid">
            <select name="type">
                <option value="">All types</option>
                <?php foreach (['Personal','Team','Task','Reminder'] as $type): ?>
                    <option value="<?= $type ?>" <?= ($filters['type'] ?? '') === $type ? 'selected' : '' ?>><?= $type ?></option>
                <?php endforeach; ?>
            </select>
            <input name="tag" placeholder="Tag" value="<?= htmlspecialchars($filters['tag'] ?? '') ?>">
            <label class="toggle"><input type="checkbox" name="pinned" value="1" <?= !empty($filters['pinned']) ? 'checked' : '' ?>>Pinned</label>
            <label class="toggle"><input type="checkbox" name="favourite" value="1" <?= !empty($filters['favourite']) ? 'checked' : '' ?>>Starred</label>
            <label class="toggle"><input type="checkbox" name="incomplete" value="1" <?= !empty($filters['incomplete']) ? 'checked' : '' ?>>With open checklist</label>
            <button class="btn" type="submit">Apply</button>
        </form>
        <details>
            <summary>Saved views</summary>
            <ul class="saved-list">
                <?php foreach ($savedFilters as $view): ?>
                    <li data-filters='<?= htmlspecialchars($view['filters_json']) ?>'><?= htmlspecialchars($view['name']) ?></li>
                <?php endforeach; ?>
            </ul>
            <form method="post" action="/notes/save-filter" class="inline">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                <?php foreach (['type','tag','pinned','favourite','incomplete'] as $key): ?>
                    <input type="hidden" name="<?= $key ?>" value="<?= htmlspecialchars($filters[$key] ?? '') ?>">
                <?php endforeach; ?>
                <input name="filter_name" placeholder="Name" required>
                <button class="btn small">Save current</button>
            </form>
        </details>
    </div>
</div>
<section class="card">
    <h2>Create note</h2>
    <form method="post" action="/notes">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
        <div class="form-grid">
            <input name="title" placeholder="Title">
            <select name="note_type">
                <option value="Personal">Personal</option>
                <option value="Team">Team</option>
                <option value="Task">Task</option>
                <option value="Reminder">Reminder</option>
            </select>
            <label>Reminder <input type="datetime-local" name="reminder_at"></label>
            <input name="tags" placeholder="Tags (comma separated)">
            <label class="toggle"><input type="checkbox" name="pinned"> Pin</label>
            <label class="toggle"><input type="checkbox" name="is_favourite"> Favourite</label>
            <select id="template-select">
                <option value="">Apply template</option>
                <?php foreach ($templates as $template): ?>
                    <option value="<?= (int)$template['id'] ?>" data-body="<?= htmlspecialchars($template['body'] ?? '') ?>" data-checklist='<?= htmlspecialchars($template['checklist_json'] ?? '[]', ENT_QUOTES) ?>'><?= htmlspecialchars($template['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <textarea name="body" id="note-body" placeholder="Write your note..."></textarea>
        <textarea name="checklist" placeholder="Checklist items (one per line)"></textarea>
        <input name="share_users" placeholder="Share with user IDs (comma separated)">
        <button class="btn" type="submit">Save note</button>
    </form>
</section>
<div class="notes-grid">
    <?php foreach ($notes as $note): ?>
        <article class="note-card">
            <header>
                <h3><?= htmlspecialchars($note['title']) ?></h3>
                <form method="post" action="/notes/pin" class="inline">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                    <input type="hidden" name="note_id" value="<?= (int)$note['id'] ?>">
                    <input type="hidden" name="pinned" value="<?= $note['pinned'] ? 0 : 1 ?>">
                    <button class="icon-btn" type="submit" title="Toggle pin"><?= $note['pinned'] ? '📌' : '📍' ?></button>
                </form>
                <form method="post" action="/notes/favourite" class="inline">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                    <input type="hidden" name="note_id" value="<?= (int)$note['id'] ?>">
                    <input type="hidden" name="fav" value="<?= $note['is_favourite'] ? 0 : 1 ?>">
                    <button class="icon-btn" type="submit" title="Toggle favourite">⭐</button>
                </form>
            </header>
            <p><?= nl2br(htmlspecialchars($note['body'])) ?></p>
            <div class="note-meta">
                <span class="badge"><?= htmlspecialchars($note['note_type']) ?></span>
                <?php if ($note['tags']): ?><span class="badge ghost"><?= htmlspecialchars($note['tags']) ?></span><?php endif; ?>
                <?php if ($note['reminder_at']): ?><span class="badge warning">Reminder <?= htmlspecialchars($note['reminder_at']) ?></span><?php endif; ?>
            </div>
            <?php if (!empty($checklists[$note['id']])): ?>
                <ul class="checklist">
                    <?php foreach ($checklists[$note['id']] as $item): ?>
                        <li>
                            <form method="post" action="/notes/checklist" class="inline">
                                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                                <input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>">
                                <input type="hidden" name="is_done" value="<?= $item['is_done'] ? 0 : 1 ?>">
                                <button class="icon-btn" type="submit"><?= $item['is_done'] ? '☑' : '☐' ?></button>
                            </form>
                            <?= htmlspecialchars($item['description']) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <details>
                <summary>Share / comments</summary>
                <form method="post" action="/notes/share" class="inline">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                    <input type="hidden" name="note_id" value="<?= (int)$note['id'] ?>">
                    <input name="share_users" placeholder="User IDs">
                    <input name="share_sectors" placeholder="Sector IDs">
                    <button class="btn small" type="submit">Share</button>
                </form>
                <form method="post" action="/notes/comment">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                    <input type="hidden" name="note_id" value="<?= (int)$note['id'] ?>">
                    <textarea name="comment" placeholder="Add comment"></textarea>
                    <button class="btn small" type="submit">Comment</button>
                </form>
                <ul class="comment-list">
                    <?php foreach (($comments[$note['id']] ?? []) as $comment): ?>
                        <li><strong>#<?= (int)$comment['author_id'] ?></strong> <?= htmlspecialchars($comment['body']) ?> <span class="muted"><?= htmlspecialchars($comment['created_at']) ?></span></li>
                    <?php endforeach; ?>
                </ul>
                <div class="readers">
                    <strong>Read by:</strong>
                    <?php foreach ($readers[$note['id']] as $reader): ?>
                        <span><?= htmlspecialchars($reader['name']) ?> (<?= htmlspecialchars($reader['read_at']) ?>)</span>
                    <?php endforeach; ?>
                </div>
            </details>
        </article>
    <?php endforeach; ?>
</div>
<?php
$content = ob_get_clean();
$title = 'Notes';
include __DIR__ . '/../layout/main_layout.php';
