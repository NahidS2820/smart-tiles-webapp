<?php

require_once __DIR__ . '/../includes/functions.php';
require_login();

$tiles = mysqli_query($conn, 'SELECT tile_id, tile_name, size, unit_price, tiles_per_box FROM inventory ORDER BY tile_name ASC');
$result = null;
$errors = [];

$form = [
    'customer_name' => '',
    'room_length' => '4',
    'room_width' => '3',
    'tile_length_cm' => '60',
    'tile_width_cm' => '60',
    'wastage_percent' => '10',
    'tile_id' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $form['customer_name'] = post_string('customer_name', 150);
    $form['room_length'] = (string) post_float('room_length', 0);
    $form['room_width'] = (string) post_float('room_width', 0);
    $form['tile_length_cm'] = (string) post_float('tile_length_cm', 0);
    $form['tile_width_cm'] = (string) post_float('tile_width_cm', 0);
    $form['wastage_percent'] = (string) post_float('wastage_percent', 0);
    $tileId = post_int('tile_id', 0);
    $form['tile_id'] = (string) $tileId;

    $roomLength = (float) $form['room_length'];
    $roomWidth = (float) $form['room_width'];
    $tileLength = (float) $form['tile_length_cm'];
    $tileWidth = (float) $form['tile_width_cm'];
    $wastage = (float) $form['wastage_percent'];

    if ($form['customer_name'] === '') {
        $errors[] = 'Customer name is required.';
    }
    if ($roomLength <= 0 || $roomWidth <= 0) {
        $errors[] = 'Room dimensions must be greater than zero.';
    }
    if ($tileLength <= 0 || $tileWidth <= 0) {
        $errors[] = 'Tile dimensions must be greater than zero.';
    }

    $selectedTile = null;
    if ($tileId > 0) {
        $tileStmt = mysqli_prepare($conn, 'SELECT tile_id, tile_name, unit_price, tiles_per_box FROM inventory WHERE tile_id = ? LIMIT 1');
        mysqli_stmt_bind_param($tileStmt, 'i', $tileId);
        mysqli_stmt_execute($tileStmt);
        $selectedTile = mysqli_fetch_assoc(mysqli_stmt_get_result($tileStmt));

        if (!$selectedTile) {
            $errors[] = 'Please select a valid tile.';
        }
    }

    if (!$errors) {
        $roomArea = $roomLength * $roomWidth;
        $tileArea = ($tileLength / 100) * ($tileWidth / 100);
        $neededTiles = (int) ceil(($roomArea / $tileArea) * (1 + ($wastage / 100)));
        $tilesPerBox = $selectedTile ? max(1, (int) $selectedTile['tiles_per_box']) : 1;
        $boxesNeeded = (int) ceil($neededTiles / $tilesPerBox);
        $unitPrice = $selectedTile ? (float) $selectedTile['unit_price'] : 0.0;
        $estimatedCost = $neededTiles * $unitPrice;
        $createdBy = current_user()['user_id'];
        $tileIdForInsert = $selectedTile ? $tileId : null;

        $stmt = mysqli_prepare(
            $conn,
            'INSERT INTO estimations (customer_name, tile_id, room_length, room_width, tile_length_cm, tile_width_cm, wastage_percent, needed_tiles, boxes_needed, estimated_cost, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        mysqli_stmt_bind_param($stmt, 'sidddddiidi', $form['customer_name'], $tileIdForInsert, $roomLength, $roomWidth, $tileLength, $tileWidth, $wastage, $neededTiles, $boxesNeeded, $estimatedCost, $createdBy);
        mysqli_stmt_execute($stmt);

        $result = [
            'room_area' => $roomArea,
            'tile_area' => $tileArea,
            'needed_tiles' => $neededTiles,
            'boxes_needed' => $boxesNeeded,
            'estimated_cost' => $estimatedCost,
            'tile_name' => $selectedTile['tile_name'] ?? 'Not selected',
        ];
    }
}

$recent = mysqli_query(
    $conn,
    'SELECT e.*, i.tile_name
     FROM estimations e
     LEFT JOIN inventory i ON i.tile_id = e.tile_id
     ORDER BY e.created_at DESC
     LIMIT 5'
);

$pageTitle = 'Tile Estimation | Smart Tiles';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="page-heading">
    <div>
        <p class="eyebrow">Calculator</p>
        <h1>Tile Estimation</h1>
    </div>
</div>

<div class="content-grid">
    <section class="panel form-panel">
        <?php if ($errors): ?>
            <div class="alert alert-danger"><?= e(implode(' ', $errors)) ?></div>
        <?php endif; ?>
        <form method="post" class="row g-3" novalidate>
            <?= csrf_field() ?>
            <div class="col-md-12">
                <label class="form-label" for="customer_name">Customer name</label>
                <input class="form-control" id="customer_name" name="customer_name" value="<?= e($form['customer_name']) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="room_length">Room length (m)</label>
                <input type="number" step="0.01" min="0" class="form-control" id="room_length" name="room_length" value="<?= e($form['room_length']) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="room_width">Room width (m)</label>
                <input type="number" step="0.01" min="0" class="form-control" id="room_width" name="room_width" value="<?= e($form['room_width']) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="tile_length_cm">Tile length (cm)</label>
                <input type="number" step="0.01" min="0" class="form-control" id="tile_length_cm" name="tile_length_cm" value="<?= e($form['tile_length_cm']) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="tile_width_cm">Tile width (cm)</label>
                <input type="number" step="0.01" min="0" class="form-control" id="tile_width_cm" name="tile_width_cm" value="<?= e($form['tile_width_cm']) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="wastage_percent">Wastage (%)</label>
                <input type="number" step="0.01" min="0" class="form-control" id="wastage_percent" name="wastage_percent" value="<?= e($form['wastage_percent']) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="tile_id">Tile for cost estimate</label>
                <select class="form-select" id="tile_id" name="tile_id">
                    <option value="">No tile selected</option>
                    <?php if ($tiles): ?>
                        <?php mysqli_data_seek($tiles, 0); ?>
                        <?php while ($tile = mysqli_fetch_assoc($tiles)): ?>
                            <option value="<?= e($tile['tile_id']) ?>" <?= (int) $form['tile_id'] === (int) $tile['tile_id'] ? 'selected' : '' ?>>
                                <?= e($tile['tile_name']) ?> · <?= e($tile['size']) ?> · <?= e(format_money($tile['unit_price'])) ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-12">
                <button class="btn btn-primary" type="submit">Calculate</button>
            </div>
        </form>

        <?php if ($result): ?>
            <div class="estimate-result">
                <h2>Estimation Result</h2>
                <div class="metric-grid compact">
                    <section class="metric-card"><span>Room Area</span><strong><?= e(number_format($result['room_area'], 2)) ?> m²</strong></section>
                    <section class="metric-card"><span>Tiles Needed</span><strong><?= e($result['needed_tiles']) ?></strong></section>
                    <section class="metric-card"><span>Boxes Needed</span><strong><?= e($result['boxes_needed']) ?></strong></section>
                    <section class="metric-card"><span>Estimated Cost</span><strong><?= e(format_money($result['estimated_cost'])) ?></strong></section>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <section class="panel">
        <div class="panel-heading">
            <h2>Recent Estimates</h2>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                <tr>
                    <th>Customer</th>
                    <th>Tile</th>
                    <th class="text-end">Tiles</th>
                    <th class="text-end">Cost</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($recent && mysqli_num_rows($recent) > 0): ?>
                    <?php while ($estimate = mysqli_fetch_assoc($recent)): ?>
                        <tr>
                            <td><?= e($estimate['customer_name']) ?></td>
                            <td><?= e($estimate['tile_name'] ?? 'N/A') ?></td>
                            <td class="text-end"><?= e($estimate['needed_tiles']) ?></td>
                            <td class="text-end"><?= e(format_money($estimate['estimated_cost'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-muted">No estimations recorded yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
