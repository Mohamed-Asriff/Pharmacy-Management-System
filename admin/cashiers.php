<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

// Only admin can access
require_role('admin');

// Fetch all cashiers
try {
    $stmt = $pdo->query("SELECT id, username, cashier_number, created_at FROM users WHERE role='cashier' ORDER BY id DESC");
    $cashiers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching cashiers: " . $e->getMessage());
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>All Cashiers</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        a { text-decoration: none; color: #0066cc; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h2>All Cashiers</h2>
    <p><a href="dashboard.php">Back to Dashboard</a> | <a href="add_cashier.php">Add New Cashier</a></p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Cashier Number</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php if($cashiers): ?>
                <?php foreach($cashiers as $c): ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td><?= htmlspecialchars($c['username']) ?></td>
                        <td><?= htmlspecialchars($c['cashier_number']) ?></td>
                        <td><?= $c['created_at'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4">No cashiers found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
