<?php ob_start(); ?>
<h1>Notes</h1>
<div class="notes-grid">
    <?php foreach ($notes as $note): ?>
        <article class="note-card">
            <h3><?= htmlspecialchars($note['title']) ?></h3>
            <p><?= nl2br(htmlspecialchars($note['body'])) ?></p>
            <span class="badge"><?= htmlspecialchars($note['note_type']) ?></span>
        </article>
    <?php endforeach; ?>
</div>
<?php $content = ob_get_clean(); $title = 'Notes'; include __DIR__ . '/../layout/main_layout.php';
