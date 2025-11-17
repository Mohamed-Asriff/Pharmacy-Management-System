<?php
session_start();

// Check if customer is logged in
if (!isset($_SESSION['customer_id'])) {
    echo 'not_logged_in';
    exit;
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle add to cart
if (isset($_POST['medicine_id']) && isset($_POST['quantity'])) {
    $medicine_id = intval($_POST['medicine_id']);
    $quantity = intval($_POST['quantity']);
    
    if ($quantity > 0) {
        // If item already in cart, add to existing quantity
        if (isset($_SESSION['cart'][$medicine_id])) {
            $_SESSION['cart'][$medicine_id] += $quantity;
        } else {
            $_SESSION['cart'][$medicine_id] = $quantity;
        }
        
        echo 'success';
        exit;
    }
}

echo 'error';
exit;
?>
