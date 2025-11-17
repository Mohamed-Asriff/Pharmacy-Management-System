<?php
require_once '../config.php';
require_once '../db.php';
requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}


$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['items']) || empty($input['items'])) {
    echo json_encode(['success' => false, 'message' => 'No items in sale']);
    exit;
}

try {
    $db->beginTransaction();
    
    
    $datePrefix = date('Ymd');
    $invoiceCount = $db->query("SELECT COUNT(*) as count FROM sales WHERE DATE(created_at) = CURDATE()")->fetch(PDO::FETCH_ASSOC)['count'];
    $invoiceNo = 'INV-' . $datePrefix . '-' . str_pad($invoiceCount + 1, 3, '0', STR_PAD_LEFT);
    
    
    $saleQuery = "INSERT INTO sales (invoice_no, user_id, total_amount, paid_amount, change_amount, payment_method) 
                  VALUES (?, ?, ?, ?, ?, ?)";
    $saleStmt = $db->prepare($saleQuery);
    $saleStmt->execute([
        $invoiceNo,
        $_SESSION['user_id'], 
        $input['total_amount'],
        $input['paid_amount'],
        $input['change_amount'],
        $input['payment_method']
    ]);
    
    $saleId = $db->lastInsertId();
    
    
    $itemQuery = "INSERT INTO sale_items (sale_id, medicine_id, quantity, price, subtotal) 
                  VALUES (?, ?, ?, ?, ?)";
    $itemStmt = $db->prepare($itemQuery);
    
    $stockQuery = "UPDATE medicines SET quantity = quantity - ? WHERE id = ?";
    $stockStmt = $db->prepare($stockQuery);
    
    foreach ($input['items'] as $item) {
        
        $checkStock = $db->prepare("SELECT quantity, name FROM medicines WHERE id = ?");
        $checkStock->execute([$item['medicine_id']]);
        $medicine = $checkStock->fetch(PDO::FETCH_ASSOC);
        
        if (!$medicine) {
            throw new Exception("Medicine not found: ID {$item['medicine_id']}");
        }
        
        if ($medicine['quantity'] < $item['quantity']) {
            throw new Exception("Insufficient stock for {$medicine['name']}. Available: {$medicine['quantity']}, Requested: {$item['quantity']}");
        }
        
        
        $itemStmt->execute([
            $saleId,
            $item['medicine_id'],
            $item['quantity'],
            $item['price'],
            $item['subtotal']
        ]);
        
        
        $stockStmt->execute([$item['quantity'], $item['medicine_id']]);
    }
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Sale completed successfully',
        'invoice_no' => $invoiceNo,
        'sale_id' => $saleId
    ]);
    
} catch (Exception $e) {
    $db->rollBack();
    error_log('Sale completion error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$stockQuery = "UPDATE medicines SET quantity = quantity - ? WHERE id = ?";
$stockStmt = $db->prepare($stockQuery);

foreach ($input['items'] as $item) {
    
    $stockStmt->execute([
        intval($item['quantity']),
        intval($item['medicine_id'])
    ]);
}
?>