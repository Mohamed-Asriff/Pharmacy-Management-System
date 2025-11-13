<?php
// Include database connection and functions
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

// Require admin login
require_role('admin');

// Fetch all products
try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching products: " . $e->getMessage());
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Manage Products</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        a { text-decoration: none; color: #0066cc; }
        a:hover { text-decoration: underline; }
        .btn { padding: 4px 10px; background: #0066cc; color: #fff; border-radius: 3px; }
        .btn:hover { background: #0055a3; }
    </style>
</head>
<body>
    <h2>Manage Products</h2>
    <p><a href="add_product.php" class="btn">Add New Product</a> | <a href="dashboard.php">Back to Dashboard</a></p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>SKU</th>
                <th>Name</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Alert Threshold</th>
                <th>Expiry Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if($products): ?>
                <?php foreach($products as $p): ?>
                    <tr>
                        <td><?= $p['id'] ?></td>
                        <td><?= htmlspecialchars($p['sku']) ?></td>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= number_format($p['price'],2) ?></td>
                        <td><?= $p['quantity'] ?></td>
                        <td><?= $p['alert_threshold'] ?></td>
                        <td><?= $p['expiry_date'] ?: '-' ?></td>
                        <td>
                            <a href="edit_product.php?id=<?= $p['id'] ?>" class="btn">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8">No products found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
