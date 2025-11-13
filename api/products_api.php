<?php
require_once __DIR__ . '/../includes/db_connect.php';
header('Content-Type: application/json');
$q = $_GET['q'] ?? '';
if ($q === '') { echo json_encode([]); exit; }
$stmt = $pdo->prepare('SELECT id, sku, name, price, quantity, expiry_date FROM products WHERE name LIKE ? OR sku LIKE ? LIMIT 30');
$like = "%$q%";
$stmt->execute([$like,$like]);
$rows = $stmt->fetchAll();
echo json_encode($rows);