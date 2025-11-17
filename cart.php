<?php
session_start();
require_once 'db.php';

// Check if customer is logged in
$is_logged_in = isset($_SESSION['customer_id']);
$customer_name = $is_logged_in ? $_SESSION['customer_name'] : '';

// Redirect to login if not logged in
if (!$is_logged_in) {
    header('Location: login.php');
    exit;
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_quantity'])) {
        $medicine_id = intval($_POST['medicine_id']);
        $quantity = intval($_POST['quantity']);
        
        if ($quantity > 0) {
            $_SESSION['cart'][$medicine_id] = $quantity;
        } else {
            unset($_SESSION['cart'][$medicine_id]);
        }
        header('Location: cart.php');
        exit;
    }
    
    if (isset($_POST['remove_item'])) {
        $medicine_id = intval($_POST['medicine_id']);
        unset($_SESSION['cart'][$medicine_id]);
        $_SESSION['success'] = 'Item removed from cart.';
        header('Location: cart.php');
        exit;
    }
    
    if (isset($_POST['clear_cart'])) {
        $_SESSION['cart'] = [];
        $_SESSION['success'] = 'Cart cleared.';
        header('Location: cart.php');
        exit;
    }
}

// Fetch cart items details
$cart_items = [];
$total_amount = 0;

if (!empty($_SESSION['cart'])) {
    $medicine_ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($medicine_ids), '?'));
    
    $query = "SELECT m.*, c.name as category_name 
              FROM medicines m 
              LEFT JOIN categories c ON m.category_id = c.id 
              WHERE m.id IN ($placeholders)";
    
    $stmt = $db->prepare($query);
    $stmt->execute($medicine_ids);
    $medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($medicines as $medicine) {
        $quantity = $_SESSION['cart'][$medicine['id']];
        $subtotal = $medicine['price'] * $quantity;
        $total_amount += $subtotal;
        
        $cart_items[] = [
            'medicine' => $medicine,
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Medi Zone</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
            text-decoration: none;
        }

        .nav-buttons {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .btn-nav {
            padding: 10px 20px;
            border: 2px solid white;
            background: transparent;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-nav:hover {
            background: white;
            color: #667eea;
        }

        .container-cart {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .cart-header {
            margin-bottom: 30px;
        }

        .cart-header h1 {
            color: #12263f;
            margin-bottom: 10px;
        }

        .cart-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .cart-items {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .cart-summary {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .cart-item {
            display: grid;
            grid-template-columns: 100px 1fr auto;
            gap: 20px;
            padding: 20px;
            border-bottom: 1px solid #e3ebf6;
            align-items: center;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 100px;
            height: 100px;
            background: #f5f7fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .item-details h3 {
            margin: 0 0 10px 0;
            color: #12263f;
        }

        .item-price {
            color: #667eea;
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }

        .quantity-control button {
            width: 30px;
            height: 30px;
            border: 1px solid #d2ddec;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        .quantity-control button:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .quantity-control input {
            width: 60px;
            text-align: center;
            border: 1px solid #d2ddec;
            border-radius: 4px;
            padding: 5px;
        }

        .item-actions {
            text-align: right;
        }

        .item-subtotal {
            font-size: 20px;
            font-weight: bold;
            color: #12263f;
            margin-bottom: 15px;
        }

        .btn-remove {
            background: #f56565;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-remove:hover {
            background: #c53030;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e3ebf6;
        }

        .summary-row.total {
            border-bottom: none;
            font-size: 20px;
            font-weight: bold;
            color: #12263f;
            padding-top: 15px;
            border-top: 2px solid #e3ebf6;
        }

        .btn-checkout {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
        }

        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-clear {
            width: 100%;
            padding: 12px;
            background: white;
            color: #f56565;
            border: 2px solid #f56565;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn-clear:hover {
            background: #f56565;
            color: white;
        }

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-cart i {
            font-size: 80px;
            color: #d2ddec;
            margin-bottom: 20px;
        }

        .empty-cart h2 {
            color: #12263f;
            margin-bottom: 10px;
        }

        .empty-cart p {
            color: #6e84a3;
            margin-bottom: 30px;
        }

        .btn-continue {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        @media (max-width: 768px) {
            .cart-content {
                grid-template-columns: 1fr;
            }

            .cart-item {
                grid-template-columns: 80px 1fr;
            }

            .item-actions {
                grid-column: 2;
                text-align: left;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <a href="index.php" class="logo">
            <i class="fas fa-clinic-medical"></i>
            <span>Medi Zone</span>
        </a>
        <div class="nav-buttons">
            <a href="index.php" class="btn-nav">
                <i class="fas fa-home"></i> Home
            </a>
            <span style="color: white;">
                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($customer_name); ?>
            </span>
            <a href="logout.php" class="btn-nav">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <div class="container-cart">
        <div class="cart-header">
            <h1><i class="fas fa-shopping-cart"></i> Shopping Cart</h1>
            <p style="color: #6e84a3;">Review your items and proceed to checkout</p>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($cart_items)): ?>
            <div class="cart-items">
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h2>Your cart is empty</h2>
                    <p>Add some medicines to get started!</p>
                    <a href="index.php" class="btn-continue">
                        <i class="fas fa-arrow-left"></i> Continue Shopping
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="cart-content">
                <div class="cart-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <div class="item-image">
                                <?php
                                $image_path = 'images/products/';
                                if (!empty($item['medicine']['image']) && file_exists($image_path . $item['medicine']['image'])) {
                                    $image_src = $image_path . $item['medicine']['image'];
                                } else {
                                    $image_src = $image_path . 'default-medicine.svg';
                                }
                                ?>
                                <img src="<?php echo htmlspecialchars($image_src); ?>" 
                                     alt="<?php echo htmlspecialchars($item['medicine']['name']); ?>">
                            </div>
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['medicine']['name']); ?></h3>
                                <div class="item-price">Rs. <?php echo number_format($item['medicine']['price'], 2); ?></div>
                                <form method="POST" class="quantity-control">
                                    <input type="hidden" name="medicine_id" value="<?php echo $item['medicine']['id']; ?>">
                                    <button type="submit" name="update_quantity" 
                                            onclick="this.form.quantity.value = Math.max(1, parseInt(this.form.quantity.value) - 1);">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                           min="1" max="<?php echo $item['medicine']['quantity']; ?>" readonly>
                                    <button type="submit" name="update_quantity" 
                                            onclick="this.form.quantity.value = Math.min(<?php echo $item['medicine']['quantity']; ?>, parseInt(this.form.quantity.value) + 1);">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </form>
                                <small style="color: #6e84a3;">Available: <?php echo $item['medicine']['quantity']; ?> units</small>
                            </div>
                            <div class="item-actions">
                                <div class="item-subtotal">Rs. <?php echo number_format($item['subtotal'], 2); ?></div>
                                <form method="POST">
                                    <input type="hidden" name="medicine_id" value="<?php echo $item['medicine']['id']; ?>">
                                    <button type="submit" name="remove_item" class="btn-remove" 
                                            onclick="return confirm('Remove this item from cart?')">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <h2 style="margin-top: 0;">Order Summary</h2>
                    <div class="summary-row">
                        <span>Items (<?php echo count($cart_items); ?>)</span>
                        <span>Rs. <?php echo number_format($total_amount, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Delivery Fee</span>
                        <span>Rs. 0.00</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>Rs. <?php echo number_format($total_amount, 2); ?></span>
                    </div>
                    <a href="checkout.php" class="btn-checkout" style="text-decoration: none; display: block; text-align: center;">
                        <i class="fas fa-check-circle"></i> Proceed to Checkout
                    </a>
                    <form method="POST">
                        <button type="submit" name="clear_cart" class="btn-clear" 
                                onclick="return confirm('Clear all items from cart?')">
                            <i class="fas fa-trash-alt"></i> Clear Cart
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
