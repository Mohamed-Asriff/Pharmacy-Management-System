<?php
require_once '../config.php';
require_once '../db.php';
requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    try {
        $db->beginTransaction();
        
      
        $invoice_no = 'INV-' . date('Ymd') . '-' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        
       
        $stmt = $db->prepare("INSERT INTO sales (invoice_no, user_id, total_amount, paid_amount, change_amount, payment_method) 
                             VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $invoice_no,
            $_SESSION['user_id'],
            $input['total'],
            $input['paidAmount'],
            $input['paidAmount'] - $input['total'],
            $input['paymentMethod']
        ]);
        
        $sale_id = $db->lastInsertId();
        
        
        foreach ($input['cart'] as $item) {
           
            $stmt = $db->prepare("INSERT INTO sale_items (sale_id, medicine_id, quantity, price, subtotal) 
                                 VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $sale_id,
                $item['id'],
                $item['quantity'],
                $item['price'],
                $item['price'] * $item['quantity']
            ]);
            
           
            $stmt = $db->prepare("UPDATE medicines SET quantity = quantity - ? WHERE id = ?");
            $stmt->execute([$item['quantity'], $item['id']]);
            
            
            $stmt = $db->prepare("INSERT INTO stock_movements (medicine_id, quantity_change, type, reference_type, reference_id) 
                                 VALUES (?, ?, 'out', 'sale', ?)");
            $stmt->execute([$item['id'], $item['quantity'], $sale_id]);
        }
        
        $db->commit();
        
        echo json_encode(['success' => true, 'invoice_no' => $invoice_no]);
        
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>