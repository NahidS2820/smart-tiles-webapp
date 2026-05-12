<?php

require_once __DIR__ . '/../includes/functions.php';
require_login();

$tiles = mysqli_query($conn, 'SELECT tile_id, tile_name, size, unit_price, stock_quantity FROM inventory WHERE stock_quantity > 0 ORDER BY tile_name ASC');
$errors = [];
$customerName = '';
$customerPhone = '';
$tileId = 0;
$quantity = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $customerName = post_string('customer_name', 150);
    $customerPhone = post_string('customer_phone', 50);
    $tileId = post_int('tile_id', 1);
    $quantity = post_int('quantity', 1);

    if ($customerName === '') {
        $errors[] = 'Customer name is required.';
    }

    $tileStmt = mysqli_prepare($conn, 'SELECT tile_id, unit_price, stock_quantity FROM inventory WHERE tile_id = ? LIMIT 1');
    mysqli_stmt_bind_param($tileStmt, 'i', $tileId);
    mysqli_stmt_execute($tileStmt);
    $tile = mysqli_fetch_assoc(mysqli_stmt_get_result($tileStmt));

    if (!$tile) {
        $errors[] = 'Please select a valid tile.';
    } elseif ($quantity > (int) $tile['stock_quantity']) {
        $errors[] = 'Quantity is higher than available stock.';
    }

    if (!$errors && $tile) {
        $unitPrice = (float) $tile['unit_price'];
        $lineTotal = $unitPrice * $quantity;
        $user = current_user();

        mysqli_begin_transaction($conn);

        try {
            $saleStmt = mysqli_prepare($conn, 'INSERT INTO sales (customer_name, customer_phone, total_amount, created_by) VALUES (?, ?, ?, ?)');
            mysqli_stmt_bind_param($saleStmt, 'ssdi', $customerName, $customerPhone, $lineTotal, $user['user_id']);
            mysqli_stmt_execute($saleStmt);
            $saleId = mysqli_insert_id($conn);

            $itemStmt = mysqli_prepare($conn, 'INSERT INTO sale_items (sale_id, tile_id, quantity, unit_price, line_total) VALUES (?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($itemStmt, 'iiidd', $saleId, $tileId, $quantity, $unitPrice, $lineTotal);
            mysqli_stmt_execute($itemStmt);

            $stockStmt = mysqli_prepare($conn, 'UPDATE inventory SET stock_quantity = stock_quantity - ? WHERE tile_id = ?');
            mysqli_stmt_bind_param($stockStmt, 'ii', $quantity, $tileId);
            mysqli_stmt_execute($stockStmt);

            mysqli_commit($conn);
            flash('success', 'Sale recorded and inventory stock updated.');
            redirect('sales/invoice.php?id=' . $saleId);
        } catch (Throwable $exception) {
            mysqli_rollback($conn);
            $errors[] = 'Sale could not be recorded. Please try again.';
        }
    }
}

$pageTitle = 'Record Sale | Smart Tiles';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="page-heading">
    <div>
        <p class="eyebrow">Transactions</p>
        <h1>Record Sale</h1>
    </div>
    <a class="btn btn-outline-secondary" href="<?= e(url('sales/index.php')) ?>">Back</a>
</div>

<section class="panel form-panel">
    <?php if ($errors): ?>
        <div class="alert alert-danger"><?= e(implode(' ', $errors)) ?></div>
    <?php endif; ?>
    <form method="post" class="row g-3" novalidate>
        <?= csrf_field() ?>
        <div class="col-md-6">
            <label class="form-label" for="customer_name">Customer name</label>
            <input class="form-control" id="customer_name" name="customer_name" value="<?= e($customerName) ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="customer_phone">Customer phone</label>
            <input class="form-control" id="customer_phone" name="customer_phone" value="<?= e($customerPhone) ?>">
        </div>
        <div class="col-md-8">
            <label class="form-label" for="tile_id">Tile</label>
            <select class="form-select" id="tile_id" name="tile_id" required>
                <option value="">Choose tile</option>
                <?php if ($tiles): ?>
                    <?php mysqli_data_seek($tiles, 0); ?>
                    <?php while ($tile = mysqli_fetch_assoc($tiles)): ?>
                        <option value="<?= e($tile['tile_id']) ?>" <?= $tileId === (int) $tile['tile_id'] ? 'selected' : '' ?>>
                            <?= e($tile['tile_name']) ?> · <?= e($tile['size']) ?> · <?= e(format_money($tile['unit_price'])) ?> · <?= e($tile['stock_quantity']) ?> in stock
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="quantity">Quantity</label>
            <input type="number" min="1" class="form-control" id="quantity" name="quantity" value="<?= e($quantity) ?>" required>
        </div>
        <div class="col-12">
            <button class="btn btn-primary" type="submit">Save Sale</button>
        </div>
    </form>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

