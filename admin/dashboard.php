<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

// Require user login
require_login();

// Get current user role
$role = $_SESSION['role'];

try {
    // Quick stats
    $stmt = $pdo->query('SELECT COUNT(*) FROM products');
    $products_count = $stmt->fetchColumn();

    $stmt = $pdo->query('SELECT COUNT(*) FROM sales');
    $sales_count = $stmt->fetchColumn();

    $alert_stmt = $pdo->query("SELECT COUNT(*) FROM alerts WHERE status='unread'");
    $alerts_unread = $alert_stmt->fetchColumn();

    // Fetch products for table
    $product_stmt = $pdo->query('SELECT * FROM products ORDER BY created_at DESC');
    $products = $product_stmt->fetchAll();

} catch (PDOException $e) {
    die("Error fetching dashboard data: " . $e->getMessage());
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h2, h3 { color: #333; }
        ul { list-style-type: none; padding: 0; }
        li { margin-bottom: 10px; }
        a { text-decoration: none; color: #0066cc; }
        a:hover { text-decoration: underline; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .section { margin-top: 20px; }
        .low-stock { background-color: #ffe6e6; } /* highlight low stock */
    </style>
</head>
<body>
    <h2>Dashboard (<?= htmlspecialchars($role) ?>)</h2>
    <p>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></p>

    <ul>
        <li>Products: <?= $products_count ?></li>
        <li>Sales: <?= $sales_count ?></li>
        <li>Unread Alerts: <a href="alerts.php"><?= $alerts_unread ?></a></li>
    </ul>

    <div class="section">
        <?php if($role === 'admin'): ?>
            <h3>Admin Actions</h3>
            <ul>
                <li><a href="products.php">Manage Products</a></li>
                <li><a href="cashiers.php">View All Cashiers</a></li>
                <li><a href="add_cashier.php">Add New Cashier</a></li>
            </ul>
        <?php endif; ?>

        <h3>POS</h3>
        <ul>
            <li><a href="../pos/create_sale.php">Open POS</a></li>
        </ul>
    </div>

    <div class="section">
        <h3>Products List</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>SKU</th>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <?php if($role === 'admin'): ?>
                    <th>Cost</th>
                <?php endif; ?>
                <th>Quantity</th>
                <th>Alert Threshold</th>
                <th>Expiry Date</th>
                <th>Created At</th>
            </tr>
            <?php foreach ($products as $p): ?>
            <tr <?php if($p['quantity'] <= $p['alert_threshold']) echo 'class="low-stock"'; ?>>
                <td><?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['sku']) ?></td>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td><?= htmlspecialchars($p['description']) ?></td>
                <td><?= $p['price'] ?></td>
                <?php if($role === 'admin'): ?>
                    <td><?= $p['cost'] ?></td>
                <?php endif; ?>
                <td><?= $p['quantity'] ?></td>
                <td><?= $p['alert_threshold'] ?></td>
                <td><?= $p['expiry_date'] ?></td>
                <td><?= $p['created_at'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <p><a href="../auth/logout.php">Logout</a></p>
</body>
</html>
