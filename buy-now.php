<?php
session_start();
require_once 'db.php';

// Check if customer is logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: login.php');
    exit;
}

// Check if medicine_id is provided
if (!isset($_POST['medicine_id']) || !isset($_POST['quantity'])) {
    header('Location: index.php');
    exit;
}

$medicine_id = (int)$_POST['medicine_id'];
$quantity = (int)$_POST['quantity'];

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Clear existing cart and add only this item
$_SESSION['cart'] = [];
$_SESSION['cart'][$medicine_id] = $quantity;

// Redirect to checkout
header('Location: checkout.php');
exit;
?>
