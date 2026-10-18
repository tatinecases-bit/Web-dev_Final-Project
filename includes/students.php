<?php
require_once __DIR__ . '/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $no = trim($_POST['student_no'] ?? ''); $name = trim($_POST['full_name'] ?? ''); $course = trim($_POST['course'] ?? '');
        if ($no === '' || $name === '') { flash('Student number and name are required.', 'error'); }
        else {
            try {
                $pdo->prepare('INSERT INTO students (student_no, full_name, course) VALUES (?,?,?)')->execute([$no, $name, $course]);
                flash('Student added.');
            } catch (PDOException $e) { flash('That student number already exists.', 'error'); }
        }
    } elseif ($action === 'delete') {
        require_admin();
        $pdo->prepare('DELETE FROM students WHERE id = ?')->execute([(int)$_POST['id']]);
        flash('Student and their attendance records deleted.');
    } elseif ($action === 'edit') {
        try {
            $pdo->prepare('UPDATE students SET student_no=?, full_name=?, course=? WHERE id=?')
                ->execute([trim($_POST['student_no']), trim($_POST['full_name']), trim($_POST['course']), (int)$_POST['id']]);
            flash('Student updated.');
        } catch (PDOException $e) { flash('That student number already exists.', 'error'); }
    }
    header('Location: students.php'); exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare('SELECT * FROM students WHERE id = ?'); $s->execute([(int)$_GET['edit']]); $edit = $s->fetch();
}
$list = $pdo->query('SELECT * FROM students ORDER BY full_name')->fetchAll();

$page_title = 'Students';
include __DIR__ . '/header.php';
?>
<h1>Students</h1>
<form method="post" class="inline card">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="<?= $edit ? 'edit' : 'add' ?>">
  <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>
  <label>Student no. <input name="student_no" required value="<?= e($edit['student_no'] ?? '') ?>"></label>
  <label>Full name <input name="full_name" required value="<?= e($edit['full_name'] ?? '') ?>"></label>
  <label>Course <input name="course" value="<?= e($edit['course'] ?? '') ?>"></label>
  <button><?= $edit ? 'Save changes' : 'Add student' ?></button>
  <?php if ($edit): ?><a href="students.php">Cancel</a><?php endif; ?>
</form>

<input type="search" id="filter" placeholder="Search students" data-filter="studentTable">
<table id="studentTable">
  <tr><th>Student no.</th><th>Name</th><th>Course</th><th></th></tr>
  <?php foreach ($list as $s): ?>
  <tr>
    <td><?= e($s['student_no']) ?></td><td><?= e($s['full_name']) ?></td><td><?= e($s['course']) ?></td>
    <td class="actions">
      <a href="students.php?edit=<?= (int)$s['id'] ?>">Edit</a>
      <?php if ($_SESSION['user']['role'] === 'admin'): ?>
      <form method="post" data-confirm="Delete <?= e($s['full_name']) ?> and all their records?">
        <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
        <button class="link danger">Delete</button>
      </form>
      <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; if (!$list): ?><tr><td colspan="4" class="muted">No students yet.</td></tr><?php endif; ?>
</table>
<?php include __DIR__ . '/footer.php'; ?>
