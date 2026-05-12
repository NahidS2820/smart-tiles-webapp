<?php

require_once __DIR__ . '/../includes/functions.php';
require_login();

$totalSales = fetch_single_value($conn, 'SELECT COALESCE(SUM(total_amount), 0) FROM sales');
$salesCount = fetch_single_value($conn, 'SELECT COUNT(*) FROM sales');
$stockValue = fetch_single_value($conn, 'SELECT COALESCE(SUM(stock_quantity * unit_price), 0) FROM inventory');
$estimateCount = fetch_single_value($conn, 'SELECT COUNT(*) FROM estimations');

$topTiles = mysqli_query(
    $conn,
    'SELECT i.tile_name, i.size, COALESCE(SUM(si.quantity), 0) AS total_sold, COALESCE(SUM(si.line_total), 0) AS revenue
     FROM sale_items si
     INNER JOIN inventory i ON i.tile_id = si.tile_id
     GROUP BY i.tile_id, i.tile_name, i.size
     ORDER BY total_sold DESC
     LIMIT 5'
);

$lowStock = mysqli_query(
    $conn,
    'SELECT tile_name, category, size, stock_quantity, reorder_level
     FROM inventory
     WHERE stock_quantity <= reorder_level
     ORDER BY stock_quantity ASC'
);

$pageTitle = 'Reports | Smart Tiles';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="page-heading">
    <div>
        <p class="eyebrow">Business insights</p>
        <h1>Reports</h1>
    </div>
</div>

<div class="metric-grid">
    <section class="metric-card">
        <span>Total Sales</span>
        <strong><?= e(format_money($totalSales)) ?></strong>
    </section>
    <section class="metric-card">
        <span>Invoices</span>
        <strong><?= e((int) $salesCount) ?></strong>
    </section>
    <section class="metric-card">
        <span>Inventory Value</span>
        <strong><?= e(format_money($stockValue)) ?></strong>
    </section>
    <section class="metric-card">
        <span>Estimations</span>
        <strong><?= e((int) $estimateCount) ?></strong>
    </section>
</div>

<div class="content-grid">
    <section class="panel">
        <div class="panel-heading">
            <h2>Top Selling Tiles</h2>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                <tr>
                    <th>Tile</th>
                    <th>Size</th>
                    <th class="text-end">Sold</th>
                    <th class="text-end">Revenue</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($topTiles && mysqli_num_rows($topTiles) > 0): ?>
                    <?php while ($tile = mysqli_fetch_assoc($topTiles)): ?>
                        <tr>
                            <td><?= e($tile['tile_name']) ?></td>
                            <td><?= e($tile['size']) ?></td>
                            <td class="text-end"><?= e($tile['total_sold']) ?></td>
                            <td class="text-end"><?= e(format_money($tile['revenue'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-muted">No sales data yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <div class="panel-heading">
            <h2>Low Stock Report</h2>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                <tr>
                    <th>Tile</th>
                    <th>Category</th>
                    <th>Size</th>
                    <th class="text-end">Stock</th>
                    <th class="text-end">Reorder</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($lowStock && mysqli_num_rows($lowStock) > 0): ?>
                    <?php while ($item = mysqli_fetch_assoc($lowStock)): ?>
                        <tr>
                            <td><?= e($item['tile_name']) ?></td>
                            <td><?= e($item['category']) ?></td>
                            <td><?= e($item['size']) ?></td>
                            <td class="text-end"><?= e($item['stock_quantity']) ?></td>
                            <td class="text-end"><?= e($item['reorder_level']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-muted">No low stock items.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

