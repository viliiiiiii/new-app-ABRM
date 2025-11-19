<?php ob_start(); ?>
<h1>Inventory</h1>
<table class="data-table">
    <thead>
        <tr>
            <th>SKU</th>
            <th>Name</th>
            <th>Location</th>
            <th>Qty</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['sku']) ?></td>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= htmlspecialchars($item['location']) ?></td>
                <td><?= htmlspecialchars($item['quantity_on_hand']) ?></td>
                <td><?= htmlspecialchars($item['status']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php $content = ob_get_clean(); $title = 'Inventory'; include __DIR__ . '/../layout/main_layout.php';
