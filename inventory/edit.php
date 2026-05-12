<?php

require_once __DIR__ . '/../includes/functions.php';
require_login();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    redirect('inventory/index.php');
}

$stmt = mysqli_prepare($conn, 'SELECT * FROM inventory WHERE tile_id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$item = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$item) {
    flash('error', 'Inventory item was not found.');
    redirect('inventory/index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $tileName = post_string('tile_name', 150);
    $category = post_string('category', 100);
    $color = post_string('color', 100);
    $size = post_string('size', 50);
    $finish = post_string('finish', 100);
    $unitPrice = post_float('unit_price', 0);
    $stockQuantity = post_int('stock_quantity', 0);
    $reorderLevel = post_int('reorder_level', 0);
    $tilesPerBox = post_int('tiles_per_box', 1);
    $supplier = post_string('supplier', 150);

    if ($tileName === '') {
        $errors[] = 'Tile name is required.';
    }
    if ($size === '') {
        $errors[] = 'Size is required.';
    }
    if ($unitPrice <= 0) {
        $errors[] = 'Unit price must be greater than zero.';
    }

    if (!$errors) {
        $update = mysqli_prepare($conn, 'UPDATE inventory SET tile_name = ?, category = ?, color = ?, size = ?, finish = ?, unit_price = ?, stock_quantity = ?, reorder_level = ?, tiles_per_box = ?, supplier = ? WHERE tile_id = ?');
        mysqli_stmt_bind_param($update, 'sssssdiiisi', $tileName, $category, $color, $size, $finish, $unitPrice, $stockQuantity, $reorderLevel, $tilesPerBox, $supplier, $id);
        mysqli_stmt_execute($update);

        flash('success', 'Inventory item updated successfully.');
        redirect('inventory/index.php');
    }

    $item = array_merge($item, [
        'tile_name' => $tileName,
        'category' => $category,
        'color' => $color,
        'size' => $size,
        'finish' => $finish,
        'unit_price' => $unitPrice,
        'stock_quantity' => $stockQuantity,
        'reorder_level' => $reorderLevel,
        'tiles_per_box' => $tilesPerBox,
        'supplier' => $supplier,
    ]);
}

$pageTitle = 'Edit Inventory | Smart Tiles';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="page-heading">
    <div>
        <p class="eyebrow">Stock control</p>
        <h1>Edit Tile</h1>
    </div>
    <a class="btn btn-outline-secondary" href="<?= e(url('inventory/index.php')) ?>">Back</a>
</div>

<section class="panel form-panel">
    <?php if ($errors): ?>
        <div class="alert alert-danger"><?= e(implode(' ', $errors)) ?></div>
    <?php endif; ?>
    <form method="post" class="row g-3" novalidate>
        <?= csrf_field() ?>
        <div class="col-md-6">
            <label class="form-label" for="tile_name">Tile name</label>
            <input class="form-control" id="tile_name" name="tile_name" value="<?= e($item['tile_name']) ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="category">Category</label>
            <input class="form-control" id="category" name="category" value="<?= e($item['category']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="color">Colour</label>
            <input class="form-control" id="color" name="color" value="<?= e($item['color']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="size">Size</label>
            <input class="form-control" id="size" name="size" value="<?= e($item['size']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="finish">Finish</label>
            <input class="form-control" id="finish" name="finish" value="<?= e($item['finish']) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="unit_price">Unit price</label>
            <input type="number" step="0.01" min="0" class="form-control" id="unit_price" name="unit_price" value="<?= e($item['unit_price']) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="stock_quantity">Stock quantity</label>
            <input type="number" min="0" class="form-control" id="stock_quantity" name="stock_quantity" value="<?= e($item['stock_quantity']) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="reorder_level">Reorder level</label>
            <input type="number" min="0" class="form-control" id="reorder_level" name="reorder_level" value="<?= e($item['reorder_level']) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="tiles_per_box">Tiles per box</label>
            <input type="number" min="1" class="form-control" id="tiles_per_box" name="tiles_per_box" value="<?= e($item['tiles_per_box']) ?>" required>
        </div>
        <div class="col-md-12">
            <label class="form-label" for="supplier">Supplier</label>
            <input class="form-control" id="supplier" name="supplier" value="<?= e($item['supplier']) ?>">
        </div>
        <div class="col-12">
            <button class="btn btn-primary" type="submit">Update Tile</button>
        </div>
    </form>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
