<?php

require_once __DIR__ . '/includes/functions.php';
require_login();

$totalTiles = fetch_single_value($conn, 'SELECT COUNT(*) FROM inventory');
$stockUnits = fetch_single_value($conn, 'SELECT COALESCE(SUM(stock_quantity), 0) FROM inventory');
$inventoryValue = fetch_single_value($conn, 'SELECT COALESCE(SUM(stock_quantity * unit_price), 0) FROM inventory');
$lowStock = fetch_single_value($conn, 'SELECT COUNT(*) FROM inventory WHERE stock_quantity <= reorder_level');
$monthSales = fetch_single_value($conn, "SELECT COALESCE(SUM(total_amount), 0) FROM sales WHERE sale_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')");

$recentSales = mysqli_query($conn, 'SELECT sale_id, customer_name, sale_date, total_amount FROM sales ORDER BY sale_date DESC, sale_id DESC LIMIT 5');
$lowStockItems = mysqli_query($conn, 'SELECT tile_name, size, stock_quantity, reorder_level FROM inventory WHERE stock_quantity <= reorder_level ORDER BY stock_quantity ASC LIMIT 5');

$pageTitle = 'Dashboard | Smart Tiles';
require_once __DIR__ . '/includes/header.php';
?>
<div class="page-heading">
    <div>
        <p class="eyebrow">Overview</p>
        <h1>Dashboard</h1>
    </div>
    <a class="btn btn-primary" href="<?= e(url('inventory/create.php')) ?>">Add Inventory</a>
</div>

<div class="metric-grid">
    <section class="metric-card">
        <span>Total Tile Types</span>
        <strong><?= e((int) $totalTiles) ?></strong>
    </section>
    <section class="metric-card">
        <span>Stock Units</span>
        <strong><?= e((int) $stockUnits) ?></strong>
    </section>
    <section class="metric-card">
        <span>Inventory Value</span>
        <strong><?= e(format_money($inventoryValue)) ?></strong>
    </section>
    <section class="metric-card warning">
        <span>Low Stock Items</span>
        <strong><?= e((int) $lowStock) ?></strong>
    </section>
    <section class="metric-card">
        <span>This Month Sales</span>
        <strong><?= e(format_money($monthSales)) ?></strong>
    </section>
</div>

<div class="content-grid">
    <section class="panel">
        <div class="panel-heading">
            <h2>Recent Sales</h2>
            <a href="<?= e(url('sales/index.php')) ?>">View all</a>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th class="text-end">Amount</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($recentSales && mysqli_num_rows($recentSales) > 0): ?>
                    <?php while ($sale = mysqli_fetch_assoc($recentSales)): ?>
                        <tr>
                            <td>#<?= e($sale['sale_id']) ?></td>
                            <td><?= e($sale['customer_name']) ?></td>
                            <td><?= e($sale['sale_date']) ?></td>
                            <td class="text-end"><?= e(format_money($sale['total_amount'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-muted">No sales recorded yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <div class="panel-heading">
            <h2>Low Stock Alerts</h2>
            <a href="<?= e(url('inventory/index.php')) ?>">Manage</a>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                <tr>
                    <th>Tile</th>
                    <th>Size</th>
                    <th class="text-end">Stock</th>
                    <th class="text-end">Reorder</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($lowStockItems && mysqli_num_rows($lowStockItems) > 0): ?>
                    <?php while ($item = mysqli_fetch_assoc($lowStockItems)): ?>
                        <tr>
                            <td><?= e($item['tile_name']) ?></td>
                            <td><?= e($item['size']) ?></td>
                            <td class="text-end"><?= e($item['stock_quantity']) ?></td>
                            <td class="text-end"><?= e($item['reorder_level']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-muted">No low stock items.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

