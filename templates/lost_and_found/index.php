<?php
ob_start();
?>
<h1>Lost &amp; Found Items</h1>
<form method="post" class="card">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
    <div class="form-grid">
        <input name="item_name" placeholder="Item name" required>
        <input name="category" placeholder="Category">
        <input name="location" placeholder="Location">
        <input type="date" name="found_at" value="<?= date('Y-m-d') ?>">
    </div>
    <textarea name="description" placeholder="Description"></textarea>
    <button type="submit">Add item</button>
</form>
<table class="data-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Status</th>
            <th>Location</th>
            <th>Found</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?= (int)$item['id'] ?></td>
                <td><?= htmlspecialchars($item['item_name']) ?></td>
                <td><?= htmlspecialchars($item['category']) ?></td>
                <td><?= htmlspecialchars($item['status']) ?></td>
                <td><?= htmlspecialchars($item['location']) ?></td>
                <td><?= htmlspecialchars($item['found_at']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php
$content = ob_get_clean();
$title = 'Lost & Found';
include __DIR__ . '/../layout/main_layout.php';
