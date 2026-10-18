<?php
require_once __DIR__ . '/auth.php';

$count = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
if ($count === 0) {
    $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, full_name, role) VALUES (?,?,?,?)');
    $stmt->execute(['admin', password_hash('1234', PASSWORD_DEFAULT), 'Administrator', 'admin']);
    $msg = 'Admin created. Username: admin, password: 1234. Change it in Users and delete this file.';
} else {
    $msg = 'Setup already done: a user exists. Delete this file.';
}
$page_title = 'Setup'; $show_nav = false;
include __DIR__ . '/header.php';
?>
<div class="card narrow">
  <h1>Setup</h1>
  <p><?= e($msg) ?></p>
  <a href="login.php">Go to login</a>
</div>
<?php include __DIR__ . '/footer.php'; ?>
