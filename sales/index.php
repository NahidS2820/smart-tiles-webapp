<?php

require_once __DIR__ . '/../includes/functions.php';
require_login();

$sales = mysqli_query(
    $conn,
    'SELECT s.sale_id, s.customer_name, s.customer_phone, s.sale_date, s.total_amount, u.username
     FROM sales s
     LEFT JOIN users u ON u.user_id = s.created_by
     ORDER BY s.sale_date DESC, s.sale_id DESC'
);

$pageTitle = 'Sales | Smart Tiles';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="page-heading">
    <div>
        <p class="eyebrow">Transactions</p>
        <h1>Sales</h1>
    </div>
    <a class="btn btn-primary" href="<?= e(url('sales/create.php')) ?>">Record Sale</a>
</div>

<section class="panel">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
            <tr>
                <th>Invoice</th>
                <th>Customer</th>
                <th>Phone</th>
                <th>Date</th>
                <th>Created by</th>
                <th class="text-end">Total</th>
                <th class="text-end">Action</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($sales && mysqli_num_rows($sales) > 0): ?>
                <?php while ($sale = mysqli_fetch_assoc($sales)): ?>
                    <tr>
                        <td>#<?= e($sale['sale_id']) ?></td>
                        <td><?= e($sale['customer_name']) ?></td>
                        <td><?= e($sale['customer_phone']) ?></td>
                        <td><?= e($sale['sale_date']) ?></td>
                        <td><?= e($sale['username'] ?? 'Unknown') ?></td>
                        <td class="text-end"><?= e(format_money($sale['total_amount'])) ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="<?= e(url('sales/invoice.php?id=' . $sale['sale_id'])) ?>">Invoice</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-muted">No sales recorded yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

