<?php
$page_title = $page_title ?? 'Attendance Monitor';
$show_nav   = $show_nav ?? true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page_title) ?> | Attendance Monitor</title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php if ($show_nav && is_logged_in()): ?>
<header class="top">
  <strong>Attendance Monitor</strong>
  <nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="students.php">Students</a>
    <a href="attendace.php">Attendance</a>
    <a href="reports.php">Reports</a>
    <?php if ($_SESSION['user']['role'] === 'admin'): ?><a href="users.php">Users</a><?php endif; ?>
    <a href="logout.php">Log out (<?= e($_SESSION['user']['username']) ?>)</a>
  </nav>
</header>
<?php endif; ?>
<main>
<?php if ($f = flash()): ?>
  <div class="msg <?= e($f[1]) ?>"><?= e($f[0]) ?></div>
<?php endif; ?>
