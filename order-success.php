<?php
session_start();

// Check if customer is logged in
$is_logged_in = isset($_SESSION['customer_id']);
$customer_name = $is_logged_in ? $_SESSION['customer_name'] : '';

// Get order number from URL
$order_no = $_GET['order'] ?? '';

// If no order number, redirect to index
if (empty($order_no)) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success - Medi Zone</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .success-container {
            background: white;
            border-radius: 20px;
            padding: 60px 80px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease 0.2s backwards;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        .success-icon i {
            font-size: 60px;
            color: white;
        }

        .success-container h1 {
            color: #12263f;
            font-size: 36px;
            margin-bottom: 15px;
        }

        .success-container p {
            color: #6e84a3;
            font-size: 18px;
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .order-number {
            background: #f5f7fa;
            padding: 20px;
            border-radius: 12px;
            margin: 30px 0;
            border-left: 4px solid #667eea;
        }

        .order-number strong {
            color: #12263f;
            font-size: 20px;
        }

        .order-number span {
            color: #667eea;
            font-size: 24px;
            font-weight: bold;
        }

        .btn-home {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .success-message {
            color: #28a745;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .info-box {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }

        .info-box p {
            margin: 5px 0;
            color: #2e7d32;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        
        <h1>Order Successful!</h1>
        
        <p class="success-message">
            <i class="fas fa-check-circle"></i> Your order has been placed successfully
        </p>
        
        <div class="order-number">
            <strong>Order Number:</strong><br>
            <span><?php echo htmlspecialchars($order_no); ?></span>
        </div>
        
        <p>Thank you for your order, <strong><?php echo htmlspecialchars($customer_name); ?></strong>!</p>
        <p>We will process your order and deliver it to your address soon.</p>
        
        <div class="info-box">
            <p><i class="fas fa-info-circle"></i> You will receive a confirmation email shortly</p>
            <p><i class="fas fa-truck"></i> Expected delivery: 3-5 business days</p>
        </div>
        
        <a href="index.php" class="btn-home">
            <i class="fas fa-home"></i> Continue Shopping
        </a>
    </div>
</body>
</html>
