
<?php
require_once '../config.php';
require_once '../db.php';
requireAuth();

$page_title = "Stock Management";
$page_description = "Manage medicine stock levels";

// Fetch stock with medicine info
try {
    $query = "SELECT m.id, m.name, m.sku, m.quantity, m.alert_threshold, m.expiry_date, c.name as category_name,
                     (SELECT SUM(quantity_change) FROM stock_movements sm WHERE sm.medicine_id = m.id AND sm.type = 'in') as total_in,
                     (SELECT SUM(quantity_change) FROM stock_movements sm WHERE sm.medicine_id = m.id AND sm.type = 'out') as total_out
              FROM medicines m 
              LEFT JOIN categories c ON m.category_id = c.id 
              ORDER BY m.quantity ASC";
    $stmt = $db->query($query);
    $stock = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Medi Zone</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <?php include '../partials/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include '../partials/header.php'; ?>
            
            <div class="content">
                <div class="page-actions">
                    <h2>Stock Management</h2>
                    <div class="stock-summary">
                        <span class="summary-item">
                            <strong><?php echo count($stock); ?></strong> Medicines
                        </span>
                        <span class="summary-item low-stock">
                            <strong><?php echo count(array_filter($stock, function($item) { return $item['quantity'] <= $item['alert_threshold']; })); ?></strong> Low Stock
                        </span>
                        <span class="summary-item out-of-stock">
                            <strong><?php echo count(array_filter($stock, function($item) { return $item['quantity'] == 0; })); ?></strong> Out of Stock
                        </span>
                    </div>
                </div>

                <!-- Stock Table -->
                <div class="card">
                    <div class="card-header">
                        <h3>Current Stock Levels</h3>
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchStock" placeholder="Search stock...">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="data-table" id="stockTable">
                                <thead>
                                    <tr>
                                        <th>Medicine</th>
                                        <th>Category</th>
                                        <th>Current Stock</th>
                                        <th>Alert Level</th>
                                        <th>Expiry Date</th>
                                        <th>Stock In</th>
                                        <th>Stock Out</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stock as $item): 
                                        // Calculate expiry status
                                        $expiry_status = '';
                                        $expiry_class = '';
                                        if ($item['expiry_date']) {
                                            $expiry_date = new DateTime($item['expiry_date']);
                                            $today = new DateTime();
                                            $days_left = $today->diff($expiry_date)->days;
                                            $is_expired = $expiry_date < $today;
                                            
                                            if ($is_expired) {
                                                $expiry_class = 'expired';
                                                $expiry_status = 'Expired';
                                            } elseif ($days_left <= 30) {
                                                $expiry_class = 'expiring-soon';
                                                $expiry_status = 'Expiring Soon';
                                            }
                                        }
                                    ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                                <?php if ($item['sku']): ?>
                                                    <br><small class="text-muted">SKU: <?php echo htmlspecialchars($item['sku']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['category_name'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="stock-quantity <?php 
                                                    echo $item['quantity'] == 0 ? 'out-of-stock' : 
                                                           ($item['quantity'] <= $item['alert_threshold'] ? 'low-stock' : ''); 
                                                ?>">
                                                    <?php echo $item['quantity']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $item['alert_threshold']; ?></td>
                                            <td>
                                                <?php if ($item['expiry_date']): ?>
                                                    <span class="expiry-date <?php echo $expiry_class; ?>">
                                                        <?php echo date('M j, Y', strtotime($item['expiry_date'])); ?>
                                                        <?php if ($expiry_status): ?>
                                                            <br><small class="expiry-warning"><?php echo $expiry_status; ?></small>
                                                        <?php endif; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $item['total_in'] ?? 0; ?></td>
                                            <td><?php echo abs($item['total_out'] ?? 0); ?></td>
                                            <td>
                                                <?php if ($item['quantity'] == 0): ?>
                                                    <span class="status-badge out-of-stock">Out of Stock</span>
                                                <?php elseif ($item['quantity'] <= $item['alert_threshold']): ?>
                                                    <span class="status-badge low-stock">Low Stock</span>
                                                <?php else: ?>
                                                    <span class="status-badge in-stock">Adequate</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/script.js"></script>
    <style>
        .expiry-date {
            display: inline-block;
            font-size: 13px;
        }

        .expiry-date.expired {
            color: #c53030;
            font-weight: 600;
        }

        .expiry-date.expiring-soon {
            color: #f59e0b;
            font-weight: 600;
        }

        .expiry-warning {
            display: block;
            font-size: 11px;
            font-weight: 600;
            margin-top: 2px;
        }

        .expired .expiry-warning {
            color: #c53030;
        }

        .expiring-soon .expiry-warning {
            color: #f59e0b;
        }

        .text-muted {
            color: #6e84a3;
            font-style: italic;
        }
    </style>
    <script>
        // Stock search functionality
        document.getElementById('searchStock')?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#stockTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
