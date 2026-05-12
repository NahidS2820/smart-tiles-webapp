<?php

require_once __DIR__ . '/../includes/functions.php';
require_login();

$errors = [];
$data = [
    'tile_name' => '',
    'category' => '',
    'color' => '',
    'size' => '',
    'finish' => '',
    'unit_price' => '',
    'stock_quantity' => '',
    'reorder_level' => '10',
    'tiles_per_box' => '4',
    'supplier' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    foreach (array_keys($data) as $key) {
        $data[$key] = post_string($key, 150);
    }

    $unitPrice = post_float('unit_price', 0);
    $stockQuantity = post_int('stock_quantity', 0);
    $reorderLevel = post_int('reorder_level', 0);
    $tilesPerBox = post_int('tiles_per_box', 1);

    if ($data['tile_name'] === '') {
        $errors[] = 'Tile name is required.';
    }
    if ($data['size'] === '') {
        $errors[] = 'Size is required.';
    }
    if ($unitPrice <= 0) {
        $errors[] = 'Unit price must be greater than zero.';
    }

    if (!$errors) {
        $stmt = mysqli_prepare($conn, 'INSERT INTO inventory (tile_name, category, color, size, finish, unit_price, stock_quantity, reorder_level, tiles_per_box, supplier) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param(
            $stmt,
            'sssssdiiis',
            $data['tile_name'],
            $data['category'],
            $data['color'],
            $data['size'],
            $data['finish'],
            $unitPrice,
            $stockQuantity,
            $reorderLevel,
            $tilesPerBox,
            $data['supplier']
        );
        mysqli_stmt_execute($stmt);

        flash('success', 'Inventory item added successfully.');
        redirect('inventory/index.php');
    }
}

$pageTitle = 'Add Inventory | Smart Tiles';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="page-heading">
    <div>
        <p class="eyebrow">Stock control</p>
        <h1>Add Tile</h1>
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
            <input class="form-control" id="tile_name" name="tile_name" value="<?= e($data['tile_name']) ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="category">Category</label>
            <input class="form-control" id="category" name="category" value="<?= e($data['category']) ?>" placeholder="Floor, wall, outdoor">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="color">Colour</label>
            <input class="form-control" id="color" name="color" value="<?= e($data['color']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="size">Size</label>
            <input class="form-control" id="size" name="size" value="<?= e($data['size']) ?>" placeholder="60x60 cm" required>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="finish">Finish</label>
            <input class="form-control" id="finish" name="finish" value="<?= e($data['finish']) ?>" placeholder="Glossy, matte">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="unit_price">Unit price</label>
            <input type="number" step="0.01" min="0" class="form-control" id="unit_price" name="unit_price" value="<?= e($data['unit_price']) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="stock_quantity">Stock quantity</label>
            <input type="number" min="0" class="form-control" id="stock_quantity" name="stock_quantity" value="<?= e($data['stock_quantity']) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="reorder_level">Reorder level</label>
            <input type="number" min="0" class="form-control" id="reorder_level" name="reorder_level" value="<?= e($data['reorder_level']) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="tiles_per_box">Tiles per box</label>
            <input type="number" min="1" class="form-control" id="tiles_per_box" name="tiles_per_box" value="<?= e($data['tiles_per_box']) ?>" required>
        </div>
        <div class="col-md-12">
            <label class="form-label" for="supplier">Supplier</label>
            <input class="form-control" id="supplier" name="supplier" value="<?= e($data['supplier']) ?>">
        </div>
        <div class="col-12">
            <button class="btn btn-primary" type="submit">Save Tile</button>
        </div>
    </form>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
