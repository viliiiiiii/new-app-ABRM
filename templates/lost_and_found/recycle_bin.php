<?php
ob_start();
?>
<h1>Lost &amp; Found Recycle Bin</h1>
<p>Restore or permanently remove soft deleted entries.</p>
<table class="data-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Item</th>
            <th>Deleted at</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $item): ?>
            <?php if (!$item['deleted_at']) continue; ?>
            <tr>
                <td><?= (int)$item['id'] ?></td>
                <td><?= htmlspecialchars($item['item_name']) ?></td>
                <td><?= htmlspecialchars($item['deleted_at']) ?></td>
                <td>
                    <form method="post" action="/lost-and-found/restore" class="inline">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                        <input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>">
                        <button class="btn" type="submit">Restore</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<a class="btn ghost" href="/lost-and-found">Back</a>
<?php
$content = ob_get_clean();
$title = 'Lost & Found Recycle Bin';
include __DIR__ . '/../layout/main_layout.php';
