<?php
require_once __DIR__ . '/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $u = trim($_POST['username'] ?? ''); $n = trim($_POST['full_name'] ?? ''); $p = $_POST['password'] ?? '';
        $role = in_array($_POST['role'] ?? '', ['admin', 'teacher'], true) ? $_POST['role'] : 'teacher';
        if ($u === '' || $n === '' || strlen($p) < 4) { flash('Fill in all fields. Password needs at least 4 characters.', 'error'); }
        else {
            try {
                $pdo->prepare('INSERT INTO users (username, password_hash, full_name, role) VALUES (?,?,?,?)')
                    ->execute([$u, password_hash($p, PASSWORD_DEFAULT), $n, $role]);
                flash('User added.');
            } catch (PDOException $e) { flash('That username is taken.', 'error'); }
        }
    } elseif ($action === 'password') {
        $p = $_POST['password'] ?? '';
        if (strlen($p) < 4) { flash('Password needs at least 4 characters.', 'error'); }
        else {
            $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($p, PASSWORD_DEFAULT), (int)$_POST['id']]);
            flash('Password changed.');
        }
    } elseif ($action === 'delete') {
        if ((int)$_POST['id'] === (int)$_SESSION['user']['id']) { flash('You cannot delete your own account.', 'error'); }
        else { $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([(int)$_POST['id']]); flash('User deleted.'); }
    }
    header('Location: users.php'); exit;
}
$list = $pdo->query('SELECT id, username, full_name, role FROM users ORDER BY username')->fetchAll();

$page_title = 'Users';
include __DIR__ . '/header.php';
?>
<h1>Users</h1>
<form method="post" class="inline card">
  <?= csrf_field() ?><input type="hidden" name="action" value="add">
  <label>Username <input name="username" required></label>
  <label>Full name <input name="full_name" required></label>
  <label>Password <input type="password" name="password" required></label>
  <label>Role <select name="role"><option value="teacher">Teacher</option><option value="admin">Admin</option></select></label>
  <button>Add user</button>
</form>
<table>
  <tr><th>Username</th><th>Name</th><th>Role</th><th>Change password</th><th></th></tr>
  <?php foreach ($list as $u): ?>
  <tr>
    <td><?= e($u['username']) ?></td><td><?= e($u['full_name']) ?></td><td><?= e($u['role']) ?></td>
    <td>
      <form method="post" class="inline">
        <?= csrf_field() ?><input type="hidden" name="action" value="password"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
        <input type="password" name="password" placeholder="New password" required><button class="ghost">Save</button>
      </form>
    </td>
    <td>
      <?php if ((int)$u['id'] !== (int)$_SESSION['user']['id']): ?>
      <form method="post" data-confirm="Delete user <?= e($u['username']) ?>?">
        <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
        <button class="link danger">Delete</button>
      </form>
      <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php include __DIR__ . '/footer.php'; ?>
