<?php

require_once __DIR__ . '/../includes/functions.php';
require_login();
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('inventory/index.php');
}

verify_csrf();

$id = post_int('id', 1);
$stmt = mysqli_prepare($conn, 'DELETE FROM inventory WHERE tile_id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);

flash('success', 'Inventory item deleted.');
redirect('inventory/index.php');

