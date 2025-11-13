<?php
/**
 * check_expiry.php
 * 
 * Check products nearing expiry and create alerts.
 * Run this daily via cron or manually.
 */

require_once __DIR__ . '/../includes/db_connect.php';

$days_before_expiry = 7; // alert X days before expiry
$today = date('Y-m-d');
$alert_date = date('Y-m-d', strtotime("+$days_before_expiry days"));

try {
    // Select products that are expiring within $days_before_expiry
    $stmt = $pdo->prepare("
        SELECT id, name, expiry_date
        FROM products
        WHERE expiry_date IS NOT NULL
          AND expiry_date <= ?
    ");
    $stmt->execute([$alert_date]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($products)) {
        echo "No products are near expiry.\n";
        exit;
    }

    // Prepare statements for inserting alerts and checking duplicates
    $insert_alert = $pdo->prepare("
        INSERT INTO alerts (type, product_id, message)
        VALUES ('expiry', ?, ?)
    ");

    $check_alert = $pdo->prepare("
        SELECT COUNT(*) FROM alerts 
        WHERE type='expiry' AND product_id=? AND message=?
    ");

    foreach ($products as $product) {
        $msg = "Expiry alert: {$product['name']} expires on {$product['expiry_date']}";

        // Avoid duplicate alerts
        $check_alert->execute([$product['id'], $msg]);
        if ($check_alert->fetchColumn() == 0) {
            $insert_alert->execute([$product['id'], $msg]);
            echo "Created alert for {$product['name']} (expires {$product['expiry_date']})\n";
        } else {
            echo "Alert already exists for {$product['name']}\n";
        }
    }

    echo "Expiry check completed.\n";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
