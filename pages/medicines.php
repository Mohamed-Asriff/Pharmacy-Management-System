
<?php
require_once '../config.php';
require_once '../db.php';
requireAuth();

$page_title = "Medicines Management";
$page_description = "Manage pharmacy medicines inventory";

$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

if ($filter === 'low_stock') {
    $query = "SELECT m.*, c.name as category_name, s.name as supplier_name 
              FROM medicines m 
              LEFT JOIN categories c ON m.category_id = c.id 
              LEFT JOIN suppliers s ON m.supplier_id = s.id 
              WHERE m.quantity <= m.alert_threshold
              ORDER BY m.quantity ASC";
    $page_title = "Low Stock Medicines";
    $page_description = "Medicines with low stock levels";
} else {
    $query = "SELECT m.*, c.name as category_name, s.name as supplier_name 
              FROM medicines m 
              LEFT JOIN categories c ON m.category_id = c.id 
              LEFT JOIN suppliers s ON m.supplier_id = s.id 
              ORDER BY m.created_at DESC";
}

$stmt = $db->query($query);
$medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);


if (isset($_GET['delete_id'])) {
    try {
        $stmt = $db->prepare("DELETE FROM medicines WHERE id = ?");
        $stmt->execute([$_GET['delete_id']]);
        $_SESSION['success'] = "Medicine deleted successfully.";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error deleting medicine: " . $e->getMessage();
    }
    header('Location: medicines.php');
    exit;
}


try {
    $query = "SELECT m.*, c.name as category_name, s.name as supplier_name 
              FROM medicines m 
              LEFT JOIN categories c ON m.category_id = c.id 
              LEFT JOIN suppliers s ON m.supplier_id = s.id 
              ORDER BY m.created_at DESC";
    $stmt = $db->query($query);
    $medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <div class="page-actions">
                    <h2>Medicines Inventory</h2>
                    <a href="add-medicine.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Medicine
                    </a>
                </div>

                
                <div class="card">
                    <div class="card-header">
                        <h3>All Medicines (<?php echo count($medicines); ?>)</h3>
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchMedicines" placeholder="Search medicines...">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="data-table" id="medicinesTable">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>SKU</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Expiry Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($medicines as $medicine): 
                                        // Calculate expiry status
                                        $expiry_status = '';
                                        $expiry_class = '';
                                        if ($medicine['expiry_date']) {
                                            $expiry_date = new DateTime($medicine['expiry_date']);
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
                                                <?php if ($medicine['expiry_date']): ?>
                                                    <span class="expiry-date <?php echo $expiry_class; ?>">
                                                        <?php echo date('M j, Y', strtotime($medicine['expiry_date'])); ?>
                                                        <?php if ($expiry_status): ?>
                                                            <br><small class="expiry-warning"><?php echo $expiry_status; ?></small>
                                                        <?php endif; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
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
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="update-medicine.php?id=<?php echo $medicine['id']; ?>" class="btn btn-sm btn-edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?delete_id=<?php echo $medicine['id']; ?>" 
                                                       class="btn btn-sm btn-delete" 
                                                       onclick="return confirm('Are you sure you want to delete this medicine?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
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
</body>
</html>
