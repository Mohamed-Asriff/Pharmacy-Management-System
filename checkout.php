<?php
session_start();
require_once 'db.php';

// Check if customer is logged in
$is_logged_in = isset($_SESSION['customer_id']);
$customer_name = $is_logged_in ? $_SESSION['customer_name'] : '';
$customer_id = $_SESSION['customer_id'] ?? null;

// Redirect to login if not logged in
if (!$is_logged_in) {
    header('Location: login.php');
    exit;
}

// Redirect to cart if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Fetch customer details
$stmt = $db->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch cart items details
$cart_items = [];
$total_amount = 0;

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

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $delivery_address = trim($_POST['delivery_address']);
    $delivery_city = trim($_POST['delivery_city']);
    $delivery_postal_code = trim($_POST['delivery_postal_code']);
    $delivery_phone = trim($_POST['delivery_phone']);
    $payment_method = $_POST['payment_method'];
    $notes = trim($_POST['notes']);
    
    if (empty($delivery_address) || empty($delivery_phone)) {
        $error = "Please fill in all required delivery information.";
    } else {
        try {
            $db->beginTransaction();
            
            // Generate order number
            $order_no = 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Insert order
            $stmt = $db->prepare("INSERT INTO online_orders 
                                 (order_no, customer_id, total_amount, payment_method, payment_status, order_status, 
                                  delivery_address, delivery_city, delivery_postal_code, delivery_phone, notes) 
                                 VALUES (?, ?, ?, ?, 'pending', 'pending', ?, ?, ?, ?, ?)");
            $stmt->execute([
                $order_no, 
                $customer_id, 
                $total_amount, 
                $payment_method,
                $delivery_address,
                $delivery_city,
                $delivery_postal_code,
                $delivery_phone,
                $notes
            ]);
            
            $order_id = $db->lastInsertId();
            
            // Insert order items
            foreach ($cart_items as $item) {
                $stmt = $db->prepare("INSERT INTO online_order_items 
                                     (order_id, medicine_id, quantity, price, subtotal) 
                                     VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $order_id,
                    $item['medicine']['id'],
                    $item['quantity'],
                    $item['medicine']['price'],
                    $item['subtotal']
                ]);
            }
            
            $db->commit();
            
            // Clear cart
            $_SESSION['cart'] = [];
            
            // Redirect to success page
            $_SESSION['success'] = "Order placed successfully! Order No: " . $order_no;
            header('Location: order-success.php?order=' . $order_no);
            exit;
            
        } catch(PDOException $e) {
            $db->rollBack();
            $error = "Error placing order: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Medi Zone</title>
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

        .container-checkout {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .checkout-header {
            margin-bottom: 30px;
        }

        .checkout-header h1 {
            color: #12263f;
            margin-bottom: 10px;
        }

        .checkout-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .checkout-form {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .order-summary {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .form-section {
            margin-bottom: 30px;
        }

        .form-section h2 {
            color: #12263f;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e3ebf6;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #12263f;
            font-weight: 600;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #d2ddec;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 10px;
        }

        .payment-method {
            position: relative;
        }

        .payment-method input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .payment-method label {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            border: 2px solid #d2ddec;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-method input[type="radio"]:checked + label {
            border-color: #667eea;
            background: #f0f4ff;
        }

        .payment-method label i {
            font-size: 24px;
            margin-bottom: 8px;
            color: #667eea;
        }

        .order-item {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #e3ebf6;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 60px;
            height: 60px;
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

        .item-info {
            flex: 1;
        }

        .item-info h4 {
            margin: 0 0 5px 0;
            color: #12263f;
            font-size: 14px;
        }

        .item-info p {
            margin: 0;
            color: #6e84a3;
            font-size: 12px;
        }

        .item-price {
            text-align: right;
            color: #12263f;
            font-weight: 600;
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

        .btn-place-order {
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

        .btn-place-order:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: #fee;
            color: #c33;
        }

        @media (max-width: 768px) {
            .checkout-content {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .payment-methods {
                grid-template-columns: 1fr;
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
            <a href="cart.php" class="btn-nav">
                <i class="fas fa-shopping-cart"></i> Cart
            </a>
            <span style="color: white;">
                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($customer_name); ?>
            </span>
        </div>
    </nav>

    <div class="container-checkout">
        <div class="checkout-header">
            <h1><i class="fas fa-credit-card"></i> Checkout</h1>
            <p style="color: #6e84a3;">Complete your order details</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" class="checkout-content">
            <div class="checkout-form">
                <!-- Delivery Information -->
                <div class="form-section">
                    <h2><i class="fas fa-truck"></i> Delivery Information</h2>
                    
                    <div class="form-group">
                        <label for="delivery_address">Delivery Address *</label>
                        <input type="text" id="delivery_address" name="delivery_address" required
                               value="<?php echo htmlspecialchars($customer['address'] ?? ''); ?>"
                               placeholder="Street address, building, apartment">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="delivery_city">City</label>
                            <input type="text" id="delivery_city" name="delivery_city"
                                   value="<?php echo htmlspecialchars($customer['city'] ?? ''); ?>"
                                   placeholder="City">
                        </div>
                        <div class="form-group">
                            <label for="delivery_postal_code">Postal Code</label>
                            <input type="text" id="delivery_postal_code" name="delivery_postal_code"
                                   value="<?php echo htmlspecialchars($customer['postal_code'] ?? ''); ?>"
                                   placeholder="Postal code">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="delivery_phone">Contact Phone *</label>
                        <input type="tel" id="delivery_phone" name="delivery_phone" required
                               value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>"
                               placeholder="Phone number for delivery contact">
                    </div>

                    <div class="form-group">
                        <label for="notes">Order Notes (Optional)</label>
                        <textarea id="notes" name="notes" placeholder="Any special instructions for delivery..."></textarea>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="form-section">
                    <h2><i class="fas fa-credit-card"></i> Payment Method</h2>
                    
                    <div class="payment-methods">
                        <div class="payment-method">
                            <input type="radio" id="cod" name="payment_method" value="cash_on_delivery" checked>
                            <label for="cod">
                                <i class="fas fa-money-bill-wave"></i>
                                <span>Cash on Delivery</span>
                            </label>
                        </div>
                        <div class="payment-method">
                            <input type="radio" id="card" name="payment_method" value="card">
                            <label for="card">
                                <i class="fas fa-credit-card"></i>
                                <span>Credit/Debit Card</span>
                            </label>
                        </div>
                        <div class="payment-method">
                            <input type="radio" id="upi" name="payment_method" value="upi">
                            <label for="upi">
                                <i class="fas fa-mobile-alt"></i>
                                <span>UPI Payment</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="order-summary">
                <h2 style="margin-top: 0;">Order Summary</h2>
                
                <div style="margin-bottom: 20px;">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="order-item">
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
                            <div class="item-info">
                                <h4><?php echo htmlspecialchars($item['medicine']['name']); ?></h4>
                                <p>Qty: <?php echo $item['quantity']; ?> × Rs. <?php echo number_format($item['medicine']['price'], 2); ?></p>
                            </div>
                            <div class="item-price">
                                Rs. <?php echo number_format($item['subtotal'], 2); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-row">
                    <span>Subtotal</span>
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

                <button type="submit" name="place_order" class="btn-place-order">
                    <i class="fas fa-check-circle"></i> Place Order
                </button>

                <p style="text-align: center; margin-top: 15px; color: #6e84a3; font-size: 12px;">
                    <i class="fas fa-lock"></i> Your payment information is secure
                </p>
            </div>
        </form>
    </div>
</body>
</html>
