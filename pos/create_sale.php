<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';
require_login(); // Make sure user is logged in

// Show example JSON if method is GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    ?>
    <!doctype html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>POS - Create Sale</title>
    </head>
    <body>
        <h2>POS - Create Sale (JSON POST)</h2>
        <p>Send a JSON POST to this URL with items and paid value.</p>
        <pre>{"items":[{"product_id":1,"qty":2}],"paid":1000}</pre>
    </body>
    </html>
    <?php
    exit;
}

// Process POST JSON
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || empty($data['items'])) {
    http_response_code(400);
    echo json_encode(['error'=>'Invalid payload']);
    exit;
}

$items = $data['items'];
$paid = $data['paid'] ?? 0;

try {
    $pdo->beginTransaction();
    $total = 0;
    $line_items = [];

    // Lock products for update
    foreach ($items as $it) {
        $pid = (int)$it['product_id'];
        $qty = (int)$it['qty'];

        $stmt = $pdo->prepare('SELECT id, name, price, quantity FROM products WHERE id=? FOR UPDATE');
        $stmt->execute([$pid]);
        $prod = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$prod) throw new Exception("Product not found: $pid");
        if ($prod['quantity'] < $qty) throw new Exception("Insufficient stock for {$prod['name']}");

        $subtotal = $prod['price'] * $qty;
        $total += $subtotal;
        $line_items[] = ['product' => $prod, 'qty' => $qty, 'subtotal' => $subtotal];
    }

    // Create sale
    $invoice_no = 'INV' . time();
    $stmt = $pdo->prepare('INSERT INTO sales (invoice_no, user_id, total, paid) VALUES (?,?,?,?)');
    $stmt->execute([$invoice_no, $_SESSION['user_id'], $total, $paid]);
    $sale_id = $pdo->lastInsertId();

    // Insert sale items and update stock
    $si_stmt = $pdo->prepare('INSERT INTO sale_items (sale_id, product_id, price, qty, subtotal) VALUES (?,?,?,?,?)');
    $upd = $pdo->prepare('UPDATE products SET quantity = quantity - ? WHERE id=?');
    $log = $pdo->prepare('INSERT INTO stock_logs (product_id, change_qty, note) VALUES (?,?,?)');
    $alert_ins = $pdo->prepare('INSERT INTO alerts (type, product_id, message) VALUES ("stock", ?, ?)');

    foreach ($line_items as $li) {
        $prod = $li['product'];
        $qty = $li['qty'];

        $si_stmt->execute([$sale_id, $prod['id'], $prod['price'], $qty, $li['subtotal']]);
        $upd->execute([$qty, $prod['id']]);
        $log->execute([$prod['id'], -$qty, "POS sale: $invoice_no"]);

        // Stock alert
        $check = $pdo->prepare('SELECT name, quantity, alert_threshold FROM products WHERE id=?');
        $check->execute([$prod['id']]);
        $p = $check->fetch(PDO::FETCH_ASSOC);

        if ($p['quantity'] <= $p['alert_threshold']) {
            $msg = "Low stock alert: {$p['name']} only {$p['quantity']} left.";
            $exists = $pdo->prepare('SELECT COUNT(*) FROM alerts WHERE product_id=? AND type="stock" AND message=?');
            $exists->execute([$prod['id'], $msg]);
            if ($exists->fetchColumn() == 0) {
                $alert_ins->execute([$prod['id'], $msg]);
            }
        }
    }

    $pdo->commit();
    echo json_encode(['success'=>true, 'invoice'=>$invoice_no, 'sale_id'=>$sale_id, 'total'=>$total]);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(400);
    echo json_encode(['error'=>$e->getMessage()]);
}
