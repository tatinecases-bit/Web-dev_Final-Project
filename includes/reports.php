<?php
require_once __DIR__ . '/auth.php';
require_login();

$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to']   ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = date('Y-m-01');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   $to   = date('Y-m-d');

$stmt = $pdo->prepare("SELECT s.student_no, s.full_name, s.course,
    COALESCE(SUM(a.status='present'),0) p, COALESCE(SUM(a.status='late'),0) l,
    COALESCE(SUM(a.status='absent'),0) ab, COALESCE(SUM(a.status='excused'),0) x
    FROM students s
    LEFT JOIN attendance a ON a.student_id = s.id AND a.attendance_date BETWEEN ? AND ?
    GROUP BY s.id, s.student_no, s.full_name, s.course ORDER BY s.full_name");
$stmt->execute([$from, $to]);
$rows = $stmt->fetchAll();

function rate($r) {
    $den = $r['p'] + $r['l'] + $r['ab'];
    return $den ? round(($r['p'] + $r['l']) / $den * 100) : null;
}

if (isset($_GET['export'])) {          // CSV download (must run before any HTML output)
    header('Content-Type: text/csv; charset=utf-8');
    header("Content-Disposition: attachment; filename=attendance_{$from}_to_{$to}.csv");
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Student no', 'Name', 'Course', 'Present', 'Late', 'Absent', 'Excused', 'Attendance %']);
    foreach ($rows as $r) fputcsv($out, [$r['student_no'], $r['full_name'], $r['course'], (int)$r['p'], (int)$r['l'], (int)$r['ab'], (int)$r['x'], rate($r) ?? '']);
    fclose($out); exit;
}

$page_title = 'Reports';
include __DIR__ . '/header.php';
?>
<h1>Reports</h1>
<form method="get" class="inline">
  <label>From <input type="date" name="from" value="<?= e($from) ?>"></label>
  <label>To <input type="date" name="to" value="<?= e($to) ?>"></label>
  <button>Show report</button>
  <button name="export" value="1" class="ghost">Download CSV</button>
</form>
<table>
  <tr><th>Student</th><th>Course</th><th>Present</th><th>Late</th><th>Absent</th><th>Excused</th><th>Attendance</th></tr>
  <?php foreach ($rows as $r): $pc = rate($r); ?>
  <tr>
    <td><?= e($r['full_name']) ?><br><small class="muted"><?= e($r['student_no']) ?></small></td>
    <td><?= e($r['course']) ?></td>
    <td><?= (int)$r['p'] ?></td><td><?= (int)$r['l'] ?></td><td><?= (int)$r['ab'] ?></td><td><?= (int)$r['x'] ?></td>
    <td class="<?= ($pc !== null && $pc < 75) ? 'low' : '' ?>"><?= $pc === null ? '-' : $pc . '%' ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<p class="muted">Attendance % = (present + late) / (present + late + absent). Excused days are not counted. Below 75% is highlighted.</p>
<?php include __DIR__ . '/footer.php'; ?>
