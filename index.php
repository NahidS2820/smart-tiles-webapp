<?php

require_once __DIR__ . '/includes/functions.php';
start_secure_session();

if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];
$identifier = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $identifier = post_string('identifier', 150);
    $password = (string) ($_POST['password'] ?? '');

    if ($identifier === '') {
        $errors[] = 'Username or email is required.';
    }

    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    if (!$errors) {
        $stmt = mysqli_prepare($conn, 'SELECT user_id, username, email, password_hash, role FROM users WHERE username = ? OR email = ? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 'ss', $identifier, $identifier);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'user_id' => (int) $user['user_id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
            ];
            flash('success', 'Welcome back, ' . $user['username'] . '.');
            redirect('dashboard.php');
        }

        $errors[] = 'Invalid login details.';
    }
}

$pageTitle = 'Login | Smart Tiles Application';
require_once __DIR__ . '/includes/header.php';
?>
<section class="login-panel">
    <div class="login-copy">
        <p class="eyebrow">Smart Tiles Web Application</p>
        <h1>Inventory, sales and tile estimation in one secure workspace.</h1>
        <p>Built for small and medium tile businesses with protected access, stock tracking, sales records, estimation and reporting.</p>
    </div>
    <form method="post" class="login-card" novalidate>
        <?= csrf_field() ?>
        <h2>Sign in</h2>
        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <?= e(implode(' ', $errors)) ?>
            </div>
        <?php endif; ?>
        <div class="mb-3">
            <label for="identifier" class="form-label">Username or email</label>
            <input type="text" class="form-control" id="identifier" name="identifier" value="<?= e($identifier) ?>" autocomplete="username" required>
        </div>
        <div class="mb-4">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
        <p class="login-hint">Default admin: <strong>admin</strong> / <strong>admin123</strong></p>
    </form>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

