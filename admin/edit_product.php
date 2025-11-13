<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

// Only admin can access
require_role('admin');

$error = '';
$success = '';

// Get product ID from query string
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("Invalid Product ID");
}

// Fetch product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Product not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sku = trim($_POST['sku']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $cost = trim($_POST['cost']);
    $quantity = trim($_POST['quantity']);
    $alert_threshold = trim($_POST['alert_threshold']);
    $expiry_date = trim($_POST['expiry_date']) ?: null;

    if (!$name || !$price || !$quantity) {
        $error = "Name, Price, and Quantity are required!";
    } else {
        try {
            // Check if SKU is unique (ignore current product)
            if ($sku) {
                $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM products WHERE sku=? AND id<>?");
                $stmtCheck->execute([$sku, $id]);
                if ($stmtCheck->fetchColumn() > 0) {
                    throw new Exception("SKU already exists for another product!");
                }
            }

            $stmtUpdate = $pdo->prepare("UPDATE products SET sku=?, name=?, description=?, price=?, cost=?, quantity=?, alert_threshold=?, expiry_date=? WHERE id=?");
            $stmtUpdate->execute([$sku, $name, $description, $price, $cost, $quantity, $alert_threshold, $expiry_date, $id]);

            $success = "Product updated successfully!";
            // Refresh product data
            $stmt->execute([$id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

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
    <title>Edit Product</title>
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
    <h2>Edit Product</h2>
    <p><a href="products.php">Back to Products</a></p>

    <?php if($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>

    <form method="POST">
        <label>SKU (Optional)</label>
        <input type="text" name="sku" value="<?= htmlspecialchars($product['sku']) ?>">

        <label>Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>

        <label>Description</label>
        <textarea name="description"><?= htmlspecialchars($product['description']) ?></textarea>

        <label>Price</label>
        <input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" required>

        <label>Cost</label>
        <input type="number" step="0.01" name="cost" value="<?= $product['cost'] ?>">

        <label>Quantity</label>
        <input type="number" name="quantity" value="<?= $product['quantity'] ?>" required>

        <label>Alert Threshold</label>
        <input type="number" name="alert_threshold" value="<?= $product['alert_threshold'] ?>">

        <label>Expiry Date (YYYY-MM-DD)</label>
        <input type="date" name="expiry_date" value="<?= $product['expiry_date'] ?>">

        <button type="submit" class="btn">Update Product</button>
    </form>
</body>
</html>
