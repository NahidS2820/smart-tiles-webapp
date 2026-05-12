<?php

require_once __DIR__ . '/../includes/functions.php';
require_login();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    redirect('sales/index.php');
}

$saleStmt = mysqli_prepare(
    $conn,
    'SELECT s.*, u.username
     FROM sales s
     LEFT JOIN users u ON u.user_id = s.created_by
     WHERE s.sale_id = ?
     LIMIT 1'
);
mysqli_stmt_bind_param($saleStmt, 'i', $id);
mysqli_stmt_execute($saleStmt);
$sale = mysqli_fetch_assoc(mysqli_stmt_get_result($saleStmt));

if (!$sale) {
    flash('error', 'Invoice was not found.');
    redirect('sales/index.php');
}

$itemStmt = mysqli_prepare(
    $conn,
    'SELECT si.*, i.tile_name, i.size, i.color
     FROM sale_items si
     INNER JOIN inventory i ON i.tile_id = si.tile_id
     WHERE si.sale_id = ?'
);
mysqli_stmt_bind_param($itemStmt, 'i', $id);
mysqli_stmt_execute($itemStmt);
$items = mysqli_stmt_get_result($itemStmt);

$pageTitle = 'Invoice #' . $id . ' | Smart Tiles';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="page-heading">
    <div>
        <p class="eyebrow">Invoice</p>
        <h1>Invoice #<?= e($sale['sale_id']) ?></h1>
    </div>
    <a class="btn btn-outline-secondary" href="<?= e(url('sales/index.php')) ?>">Back</a>
</div>

<section class="panel invoice-panel">
    <div class="invoice-header">
        <div>
            <h2>Smart Tiles Application</h2>
            <p>Tile inventory and sales management</p>
        </div>
        <div class="text-end">
            <strong>Date</strong>
            <p><?= e($sale['sale_date']) ?></p>
        </div>
    </div>

    <div class="row invoice-meta">
        <div class="col-md-6">
            <span>Customer</span>
            <strong><?= e($sale['customer_name']) ?></strong>
            <p><?= e($sale['customer_phone']) ?></p>
        </div>
        <div class="col-md-6 text-md-end">
            <span>Created by</span>
            <strong><?= e($sale['username'] ?? 'Unknown') ?></strong>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
            <tr>
                <th>Tile</th>
                <th class="text-end">Quantity</th>
                <th class="text-end">Unit Price</th>
                <th class="text-end">Line Total</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($item = mysqli_fetch_assoc($items)): ?>
                <tr>
                    <td><?= e($item['tile_name']) ?> · <?= e($item['size']) ?> · <?= e($item['color']) ?></td>
                    <td class="text-end"><?= e($item['quantity']) ?></td>
                    <td class="text-end"><?= e(format_money($item['unit_price'])) ?></td>
                    <td class="text-end"><?= e(format_money($item['line_total'])) ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
            <tfoot>
            <tr>
                <th colspan="3" class="text-end">Total</th>
                <th class="text-end"><?= e(format_money($sale['total_amount'])) ?></th>
            </tr>
            </tfoot>
        </table>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

