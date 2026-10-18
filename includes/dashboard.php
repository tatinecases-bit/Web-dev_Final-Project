<?php
require_once __DIR__ . '/auth.php';
require_login();

$today = date('Y-m-d');
$students = (int)$pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
$stmt = $pdo->prepare('SELECT status, COUNT(*) c FROM attendance WHERE attendance_date = ? GROUP BY status');
$stmt->execute([$today]);
$t = ['present' => 0, 'late' => 0, 'absent' => 0, 'excused' => 0];
foreach ($stmt as $r) $t[$r['status']] = (int)$r['c'];
$unmarked = max(0, $students - array_sum($t));

$recent = $pdo->query("SELECT attendance_date d,
    SUM(status='present') p, SUM(status='late') l, SUM(status='absent') a, SUM(status='excused') x
    FROM attendance GROUP BY attendance_date ORDER BY attendance_date DESC LIMIT 7")->fetchAll();

$page_title = 'Dashboard';
include __DIR__ . '/header.php';
?>
<h1>Dashboard</h1>
<p class="muted">Today: <?= e($today) ?></p>
<div class="stats">
  <div><b><?= $students ?></b><span>Students</span></div>
  <div class="p"><b><?= $t['present'] ?></b><span>Present</span></div>
  <div class="l"><b><?= $t['late'] ?></b><span>Late</span></div>
  <div class="a"><b><?= $t['absent'] ?></b><span>Absent</span></div>
  <div><b><?= $unmarked ?></b><span>Not marked</span></div>
</div>
<p><a class="btn" href="attendace.php">Take attendance for today</a></p>

<h2>Last 7 days with records</h2>
<table>
  <tr><th>Date</th><th>Present</th><th>Late</th><th>Absent</th><th>Excused</th></tr>
  <?php foreach ($recent as $r): ?>
  <tr><td><a href="attendace.php?date=<?= e($r['d']) ?>"><?= e($r['d']) ?></a></td>
      <td><?= (int)$r['p'] ?></td><td><?= (int)$r['l'] ?></td><td><?= (int)$r['a'] ?></td><td><?= (int)$r['x'] ?></td></tr>
  <?php endforeach; if (!$recent): ?><tr><td colspan="5" class="muted">No attendance recorded yet.</td></tr><?php endif; ?>
</table>
<?php include __DIR__ . '/footer.php'; ?>
