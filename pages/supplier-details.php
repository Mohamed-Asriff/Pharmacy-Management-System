<?php
require_once '../config.php';
require_once '../db.php';
requireAuth();

// Only admin can view supplier details
if ($_SESSION['user_role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

$page_title = "Supplier Details";
$page_description = "View supplier information and associated medicines";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: suppliers.php');
    exit;
}

$supplier_id = $_GET['id'];

try {
    // Fetch supplier details
    $stmt = $db->prepare("SELECT * FROM suppliers WHERE id = ?");
    $stmt->execute([$supplier_id]);
    $supplier = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$supplier) {
        $_SESSION['error'] = "Supplier not found.";
        header('Location: suppliers.php');
        exit;
    }
    
    // Fetch supplier's medicines
    $stmt = $db->prepare("SELECT m.*, c.name as category_name 
                         FROM medicines m 
                         LEFT JOIN categories c ON m.category_id = c.id 
                         WHERE m.supplier_id = ? 
                         ORDER BY m.created_at DESC");
    $stmt->execute([$supplier_id]);
    $medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate supplier statistics
    $total_medicines = count($medicines);
    $total_stock = array_sum(array_column($medicines, 'quantity'));
    $total_value = array_sum(array_map(function($medicine) {
        return $medicine['quantity'] * $medicine['cost_price'];
    }, $medicines));
    
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
                    <h2>Supplier Details</h2>
                    <div>
                        <a href="suppliers.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Suppliers
                        </a>
                        <a href="edit-supplier.php?id=<?php echo $supplier_id; ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Supplier
                        </a>
                    </div>
                </div>

                <!-- Supplier Information -->
                <div class="card">
                    <div class="card-header">
                        <h3>Supplier Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="supplier-info-grid">
                            <div class="info-group">
                                <label>Supplier Name</label>
                                <p class="info-value"><?php echo htmlspecialchars($supplier['name']); ?></p>
                            </div>
                            
                            <?php if ($supplier['email']): ?>
                            <div class="info-group">
                                <label>Email Address</label>
                                <p class="info-value">
                                    <i class="fas fa-envelope"></i>
                                    <?php echo htmlspecialchars($supplier['email']); ?>
                                </p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($supplier['phone']): ?>
                            <div class="info-group">
                                <label>Phone Number</label>
                                <p class="info-value">
                                    <i class="fas fa-phone"></i>
                                    <?php echo htmlspecialchars($supplier['phone']); ?>
                                </p>
                            </div>
                            <?php endif; ?>
                            
                            <div class="info-group">
                                <label>Added Date</label>
                                <p class="info-value"><?php echo date('F j, Y', strtotime($supplier['created_at'])); ?></p>
                            </div>
                            
                            <?php if ($supplier['address']): ?>
                            <div class="info-group full-width">
                                <label>Address</label>
                                <p class="info-value"><?php echo nl2br(htmlspecialchars($supplier['address'])); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Supplier Statistics -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon primary">
                            <i class="fas fa-pills"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_medicines; ?></h3>
                            <p>Total Medicines</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon success">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_stock; ?></h3>
                            <p>Total Stock</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon revenue">
                            <i class="fas fa-rupee-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Rs. <?php echo number_format($total_value, 2); ?></h3>
                            <p>Inventory Value</p>
                        </div>
                    </div>
                </div>

                <!-- Supplier's Medicines -->
                <div class="card">
                    <div class="card-header">
                        <h3>Supplied Medicines (<?php echo $total_medicines; ?>)</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($total_medicines > 0): ?>
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Medicine Name</th>
                                            <th>SKU</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Stock</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($medicines as $medicine): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($medicine['name']); ?></strong>
                                                    <?php if ($medicine['description']): ?>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars(substr($medicine['description'], 0, 50)); ?>...</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($medicine['sku']); ?></td>
                                                <td><?php echo htmlspecialchars($medicine['category_name'] ?? 'N/A'); ?></td>
                                                <td>Rs. <?php echo number_format($medicine['price'], 2); ?></td>
                                                <td>
                                                    <span class="stock-quantity <?php echo $medicine['quantity'] <= $medicine['alert_threshold'] ? 'low-stock' : ''; ?>">
                                                        <?php echo $medicine['quantity']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($medicine['quantity'] == 0): ?>
                                                        <span class="status-badge out-of-stock">Out of Stock</span>
                                                    <?php elseif ($medicine['quantity'] <= $medicine['alert_threshold']): ?>
                                                        <span class="status-badge low-stock">Low Stock</span>
                                                    <?php else: ?>
                                                        <span class="status-badge in-stock">In Stock</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="no-data">No medicines found for this supplier.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/script.js"></script>
    
    <style>
        .supplier-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .info-group {
            margin-bottom: 15px;
        }
        
        .info-group label {
            font-weight: 600;
            color: var(--secondary);
            font-size: 14px;
            margin-bottom: 5px;
            display: block;
        }
        
        .info-value {
            font-size: 16px;
            color: var(--dark);
            margin: 0;
        }
        
        .info-value i {
            margin-right: 8px;
            color: var(--primary);
        }
        
        .full-width {
            grid-column: 1 / -1;
        }
        
        @media (max-width: 768px) {
            .supplier-info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>