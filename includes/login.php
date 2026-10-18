<?php
require_once __DIR__ . '/auth.php';
if (is_logged_in()) { header('Location: dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([trim($_POST['username'] ?? '')]);
    $u = $stmt->fetch();
    if ($u && password_verify($_POST['password'] ?? '', $u['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $u['id'], 'username' => $u['username'], 'role' => $u['role']];
        header('Location: dashboard.php'); exit;
    }
    $error = 'Wrong username or password.';
}
$page_title = 'Log in'; $show_nav = false;
include __DIR__ . '/header.php';
?>
<div class="card narrow">
  <h1>Log in</h1>
  <?php if ($error): ?><div class="msg error"><?= e($error) ?></div><?php endif; ?>
  <form method="post">
    <?= csrf_field() ?>
    <label>Username <input name="username" required autofocus></label>
    <label>Password <input type="password" name="password" required></label>
    <button>Log in</button>
  </form>
  <p class="muted">First time? Run <a href="setup_admin.php">setup_admin.php</a> once to create the admin account.</p>
</div>
<?php include __DIR__ . '/footer.php'; ?>
