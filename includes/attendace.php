<?php
require_once __DIR__ . '/auth.php';
require_login();

$valid = ['present', 'late', 'absent', 'excused'];
$date = $_POST['date'] ?? $_GET['date'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $date = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $status = $_POST['status'] ?? []; $remarks = $_POST['remarks'] ?? [];
    $q = $pdo->prepare('INSERT INTO attendance (student_id, attendance_date, status, remarks, recorded_by)
                        VALUES (?,?,?,?,?)
                        ON DUPLICATE KEY UPDATE status=VALUES(status), remarks=VALUES(remarks), recorded_by=VALUES(recorded_by)');
    foreach ($status as $sid => $s) {
        if (in_array($s, $valid, true)) $q->execute([(int)$sid, $date, $s, trim($remarks[$sid] ?? ''), $_SESSION['user']['id']]);
    }
    flash('Attendance saved for ' . $date . '.');
    header('Location: attendace.php?date=' . urlencode($date)); exit;
}

$stmt = $pdo->prepare('SELECT s.id, s.student_no, s.full_name, a.status, a.remarks
    FROM students s LEFT JOIN attendance a ON a.student_id = s.id AND a.attendance_date = ?
    ORDER BY s.full_name');
$stmt->execute([$date]);
$rows = $stmt->fetchAll();

$page_title = 'Attendance';
include __DIR__ . '/header.php';
?>
<h1>Take attendance</h1>
<form method="get" class="inline">
  <label>Date <input type="date" name="date" value="<?= e($date) ?>" onchange="this.form.submit()"></label>
</form>

<form method="post">
  <?= csrf_field() ?><input type="hidden" name="date" value="<?= e($date) ?>">
  <p><button type="button" class="ghost" data-mark-all="present">Mark all present</button></p>
  <table>
    <tr><th>Student</th><th>Present</th><th>Late</th><th>Absent</th><th>Excused</th><th>Remarks</th></tr>
    <?php foreach ($rows as $r): $cur = $r['status'] ?? 'present'; ?>
    <tr>
      <td><?= e($r['full_name']) ?><br><small class="muted"><?= e($r['student_no']) ?><?= $r['status'] ? '' : ' (not yet marked)' ?></small></td>
      <?php foreach ($valid as $v): ?>
      <td><input type="radio" name="status[<?= (int)$r['id'] ?>]" value="<?= $v ?>" <?= $cur === $v ? 'checked' : '' ?> aria-label="<?= e($v . ' ' . $r['full_name']) ?>"></td>
      <?php endforeach; ?>
      <td><input name="remarks[<?= (int)$r['id'] ?>]" value="<?= e($r['remarks'] ?? '') ?>" maxlength="255"></td>
    </tr>
    <?php endforeach; if (!$rows): ?><tr><td colspan="6" class="muted">Add students first.</td></tr><?php endif; ?>
  </table>
  <?php if ($rows): ?><button>Save attendance</button><?php endif; ?>
</form>
<?php include __DIR__ . '/footer.php'; ?>
