<?php
require_once __DIR__ . '/../includes/db_connect.php'; // Add this line
require_once __DIR__ . '/../includes/functions.php';
require_login();

// mark read action
if (isset($_GET['mark_read'])) {
    $id = (int)$_GET['mark_read'];
    $pdo->prepare('UPDATE alerts SET status="read" WHERE id=?')->execute([$id]);
    header('Location: alerts.php'); 
    exit;
}

$stmt = $pdo->query('SELECT a.*, p.name FROM alerts a JOIN products p ON a.product_id=p.id ORDER BY a.created_at DESC');
$alerts = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Alerts</title></head>
<body>
<h2>System Alerts</h2>
<table border="1" cellpadding="6">
<tr><th>Type</th><th>Product</th><th>Message</th><th>Date</th><th>Status</th><th>Action</th></tr>
<?php foreach ($alerts as $a): ?>
<tr>
<td><?= htmlspecialchars($a['type']) ?></td>
<td><?= htmlspecialchars($a['name']) ?></td>
<td><?= htmlspecialchars($a['message']) ?></td>
<td><?= $a['created_at'] ?></td>
<td><?= $a['status'] ?></td>
<td><?php if ($a['status']==='unread'): ?><a href="?mark_read=<?= $a['id'] ?>">Mark read</a><?php endif; ?></td>
</tr>
<?php endforeach; ?>
</table>
<p><a href="dashboard.php">Back</a></p>
</body></html>
