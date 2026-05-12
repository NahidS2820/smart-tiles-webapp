<?php

require_once __DIR__ . '/../includes/functions.php';
require_login();

$search = trim((string) ($_GET['search'] ?? ''));

if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt = mysqli_prepare($conn, 'SELECT * FROM inventory WHERE tile_name LIKE ? OR category LIKE ? OR color LIKE ? OR size LIKE ? ORDER BY tile_name ASC');
    mysqli_stmt_bind_param($stmt, 'ssss', $like, $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $items = mysqli_stmt_get_result($stmt);
} else {
    $items = mysqli_query($conn, 'SELECT * FROM inventory ORDER BY tile_name ASC');
}

$pageTitle = 'Inventory | Smart Tiles';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="page-heading">
    <div>
        <p class="eyebrow">Stock control</p>
        <h1>Inventory</h1>
    </div>
    <a class="btn btn-primary" href="<?= e(url('inventory/create.php')) ?>">Add Tile</a>
</div>

<section class="panel">
    <form method="get" class="row g-2 mb-3">
        <div class="col-md-10">
            <input type="search" class="form-control" name="search" value="<?= e($search) ?>" placeholder="Search by name, category, colour or size">
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-outline-primary" type="submit">Search</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
            <tr>
                <th>Tile</th>
                <th>Category</th>
                <th>Colour</th>
                <th>Size</th>
                <th class="text-end">Price</th>
                <th class="text-end">Stock</th>
                <th class="text-end">Reorder</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($items && mysqli_num_rows($items) > 0): ?>
                <?php while ($item = mysqli_fetch_assoc($items)): ?>
                    <tr class="<?= (int) $item['stock_quantity'] <= (int) $item['reorder_level'] ? 'table-warning' : '' ?>">
                        <td>
                            <strong><?= e($item['tile_name']) ?></strong>
                            <div class="text-muted small"><?= e($item['finish']) ?> finish · <?= e($item['supplier']) ?></div>
                        </td>
                        <td><?= e($item['category']) ?></td>
                        <td><?= e($item['color']) ?></td>
                        <td><?= e($item['size']) ?></td>
                        <td class="text-end"><?= e(format_money($item['unit_price'])) ?></td>
                        <td class="text-end"><?= e($item['stock_quantity']) ?></td>
                        <td class="text-end"><?= e($item['reorder_level']) ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="<?= e(url('inventory/edit.php?id=' . $item['tile_id'])) ?>">Edit</a>
                            <?php if (current_user()['role'] === 'admin'): ?>
                                <form method="post" action="<?= e(url('inventory/delete.php')) ?>" class="d-inline" onsubmit="return confirm('Delete this tile from inventory?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= e($item['tile_id']) ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8" class="text-muted">No inventory records found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

