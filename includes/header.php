<?php

require_once __DIR__ . '/functions.php';
start_secure_session();

$pageTitle = $pageTitle ?? 'Smart Tiles Application';
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(url('css/styles.css')) ?>" rel="stylesheet">
</head>
<body>
<?php if ($user): ?>
    <nav class="navbar navbar-expand-lg navbar-dark app-navbar">
        <div class="container-fluid">
            <a class="navbar-brand fw-semibold" href="<?= e(url('dashboard.php')) ?>">Smart Tiles</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('dashboard.php')) ?>">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('inventory/index.php')) ?>">Inventory</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('sales/index.php')) ?>">Sales</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('estimation/index.php')) ?>">Estimation</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('reports/index.php')) ?>">Reports</a></li>
                </ul>
                <div class="d-flex align-items-center gap-3 text-white small">
                    <span><?= e($user['username']) ?> · <?= e(ucfirst($user['role'])) ?></span>
                    <a class="btn btn-sm btn-outline-light" href="<?= e(url('logout.php')) ?>">Logout</a>
                </div>
            </div>
        </div>
    </nav>
<?php endif; ?>
<main class="<?= $user ? 'container-fluid app-shell' : 'login-shell' ?>">
    <?php if ($message = flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= e($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($message = flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= e($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

