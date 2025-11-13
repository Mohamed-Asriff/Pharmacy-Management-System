<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

$stmt = $conn->query("SELECT DATE(created_at) as date, SUM(total) as total_sales 
                      FROM sales GROUP BY DATE(created_at) ORDER BY date DESC");
?>
<!doctype html>
<html><body>
<h2>Sales Report</h2>
<table border="1" cellpadding="5">
<tr><th>Date</th><th>Total Sales (LKR)</th></tr>
<?php while ($row = $stmt->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($row['date']) ?></td>
<td><?= htmlspecialchars($row['total_sales']) ?></td>
</tr>
<?php endwhile; ?>
</table>
</body></html>
