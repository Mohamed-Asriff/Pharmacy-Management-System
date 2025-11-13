<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

// Only admin can access
require_role('admin');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sku = trim($_POST['sku']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $cost = trim($_POST['cost']);
    $quantity = trim($_POST['quantity']);
    $alert_threshold = trim($_POST['alert_threshold']);
    $expiry_date = trim($_POST['expiry_date']) ?: null; // allow NULL

    if (!$name || !$price || !$quantity) {
        $error = "Name, Price, and Quantity are required!";
    } else {
        try {
            // Check if SKU already exists
            if ($sku) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE sku=?");
                $stmt->execute([$sku]);
                if ($stmt->fetchColumn() > 0) {
                    throw new Exception("SKU already exists!");
                }
            }

            $stmt = $pdo->prepare("INSERT INTO products (sku, name, description, price, cost, quantity, alert_threshold, expiry_date) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([$sku, $name, $description, $price, $cost, $quantity, $alert_threshold, $expiry_date]);
            $success = "Product added successfully!";
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Add Product</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        label { display: block; margin: 10px 0 5px; }
        input, textarea { width: 300px; padding: 5px; }
        textarea { height: 80px; }
        .btn { margin-top: 10px; padding: 6px 12px; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h2>Add New Product</h2>
    <p><a href="products.php">Back to Products</a></p>

    <?php if($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>

    <form method="POST">
        <label>SKU (Optional)</label>
        <input type="text" name="sku">

        <label>Name</label>
        <input type="text" name="name" required>

        <label>Description</label>
        <textarea name="description"></textarea>

        <label>Price</label>
        <input type="number" step="0.01" name="price" required>

        <label>Cost</label>
        <input type="number" step="0.01" name="cost">

        <label>Quantity</label>
        <input type="number" name="quantity" required>

        <label>Alert Threshold</label>
        <input type="number" name="alert_threshold" value="10">

        <label>Expiry Date (YYYY-MM-DD)</label>
        <input type="date" name="expiry_date">

        <button type="submit" class="btn">Add Product</button>
    </form>
</body>
</html>
