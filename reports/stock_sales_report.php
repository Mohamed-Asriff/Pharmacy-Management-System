<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();


$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-d');


$sql = "SELECT p.id, p.name, p.quantity, p.alert_threshold,
COALESCE(SUM(si.qty),0) AS sold_qty,
COALESCE(SUM(si.subtotal),0) AS total_sales
FROM products p
LEFT JOIN sale_items si ON si.product_id=p.id
LEFT JOIN sales s ON si.sale_id=s.id AND s.created_at BETWEEN ? AND ?
GROUP BY p.id
ORDER BY total_sales DESC";


$stmt = $pdo->prepare($sql);
$stmt->execute([$from . ' 00:00:00', $to . ' 23:59:59']);
$rows = $stmt->fetchAll();
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Stock Level Sales Report</title></head><body>
<h2>Stock Level Sales Report (<?= htmlspecialchars($from) ?> to <?= htmlspecialchars($to) ?>)</h2>
<table border="1" cellpadding="6">
<tr><th>Product</th><th>Sold Qty</th><th>Total Sales</th><th>Current Stock</th><th>Alert Level</th><th>Status</th></tr>
<?php foreach ($rows as $r): ?>
<tr>
<td><?= htmlspecialchars($r['name']) ?></td>
<td><?= $r['sold_qty'] ?: 0 ?></td>
<td><?= number_format($r['total_sales'],2) ?></td>
<td><?= $r['quantity'] ?></td>
<td><?= $r['alert_threshold'] ?></td>
<td><?= ($r['quantity'] <= $r['alert_threshold']) ? '<span style="color:red">Low Stock</span>' : 'OK' ?></td>
</tr>
<?php endforeach; ?>
</table>
<p><a href="../admin/dashboard.php">Back</a></p>
</body></html>
```


---


## index.php


```php
<?php
header('Location: /pharmacy-pos/auth/login.php');
exit;