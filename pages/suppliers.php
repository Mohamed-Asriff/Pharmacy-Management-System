<?php
require_once '../config.php';
require_once '../db.php';
requireAuth();

// Only admin can access suppliers management
if ($_SESSION['user_role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

$page_title = "Suppliers Management";
$page_description = "Manage medicine suppliers and vendors";

// Handle delete supplier
if (isset($_GET['delete_id'])) {
    try {
        // Check if supplier has medicines before deleting
        $check_stmt = $db->prepare("SELECT COUNT(*) as medicine_count FROM medicines WHERE supplier_id = ?");
        $check_stmt->execute([$_GET['delete_id']]);
        $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['medicine_count'] > 0) {
            $_SESSION['error'] = "Cannot delete supplier. There are medicines associated with this supplier.";
        } else {
            $stmt = $db->prepare("DELETE FROM suppliers WHERE id = ?");
            $stmt->execute([$_GET['delete_id']]);
            $_SESSION['success'] = "Supplier deleted successfully.";
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error deleting supplier: " . $e->getMessage();
    }
    header('Location: suppliers.php');
    exit;
}

// Fetch all suppliers with their medicine counts
try {
    $query = "SELECT s.*, 
                     COUNT(m.id) as total_medicines,
                     COALESCE(SUM(m.quantity), 0) as total_stock,
                     COALESCE(SUM(m.quantity * m.cost_price), 0) as total_inventory_value
              FROM suppliers s 
              LEFT JOIN medicines m ON s.id = m.supplier_id 
              GROUP BY s.id 
              ORDER BY s.created_at DESC";
    $stmt = $db->query($query);
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                <!-- Success/Error Messages -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <div class="page-actions">
                    <h2>Suppliers Management</h2>
                    <button class="btn btn-primary" onclick="openAddSupplierModal()">
                        <i class="fas fa-plus"></i> Add New Supplier
                    </button>
                </div>

                <!-- Suppliers Summary -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon primary">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo count($suppliers); ?></h3>
                            <p>Total Suppliers</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon success">
                            <i class="fas fa-pills"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo array_sum(array_column($suppliers, 'total_medicines')); ?></h3>
                            <p>Total Medicines</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon revenue">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo array_sum(array_column($suppliers, 'total_stock')); ?></h3>
                            <p>Total Stock Items</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon warning">
                            <i class="fas fa-rupee-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Rs. <?php echo number_format(array_sum(array_column($suppliers, 'total_inventory_value')), 2); ?></h3>
                            <p>Inventory Value</p>
                        </div>
                    </div>
                </div>

                <!-- Suppliers Table -->
                <div class="card">
                    <div class="card-header">
                        <h3>All Suppliers (<?php echo count($suppliers); ?>)</h3>
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchSuppliers" placeholder="Search suppliers...">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="data-table" id="suppliersTable">
                                <thead>
                                    <tr>
                                        <th>Supplier Name</th>
                                        <th>Contact Info</th>
                                        <th>Medicines</th>
                                        <th>Stock Value</th>
                                        <th>Status</th>
                                        <th>Added Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($suppliers as $supplier): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($supplier['name']); ?></strong>
                                                <?php if ($supplier['address']): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars(substr($supplier['address'], 0, 50)); ?>...</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($supplier['email']): ?>
                                                    <div><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($supplier['email']); ?></div>
                                                <?php endif; ?>
                                                <?php if ($supplier['phone']): ?>
                                                    <div><i class="fas fa-phone"></i> <?php echo htmlspecialchars($supplier['phone']); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary"><?php echo $supplier['total_medicines']; ?> medicines</span>
                                                <br>
                                                <small class="text-muted"><?php echo $supplier['total_stock']; ?> in stock</small>
                                            </td>
                                            <td>
                                                <strong>Rs. <?php echo number_format($supplier['total_inventory_value'], 2); ?></strong>
                                            </td>
                                            <td>
                                                <?php if ($supplier['total_medicines'] > 0): ?>
                                                    <span class="status-badge in-stock">Active</span>
                                                <?php else: ?>
                                                    <span class="status-badge out-of-stock">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($supplier['created_at'])); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="btn btn-sm btn-primary" onclick="viewSupplier(<?php echo $supplier['id']; ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-warning" onclick="editSupplier(<?php echo $supplier['id']; ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="?delete_id=<?php echo $supplier['id']; ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('Are you sure you want to delete this supplier? This action cannot be undone.')">
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

    <!-- Add Supplier Modal -->
    <div id="addSupplierModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Supplier</h3>
                <span class="close" onclick="closeAddSupplierModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="addSupplierForm" method="POST" action="add-supplier.php">
                    <div class="form-group">
                        <label for="supplier_name">Supplier Name *</label>
                        <input type="text" id="supplier_name" name="name" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="supplier_email">Email Address</label>
                            <input type="email" id="supplier_email" name="email">
                        </div>
                        <div class="form-group">
                            <label for="supplier_phone">Phone Number</label>
                            <input type="tel" id="supplier_phone" name="phone">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="supplier_address">Address</label>
                        <textarea id="supplier_address" name="address" rows="3"></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Add Supplier
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="closeAddSupplierModal()">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../js/script.js"></script>
    <script>
        // Suppliers search functionality
        document.getElementById('searchSuppliers')?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#suppliersTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Modal functions
        function openAddSupplierModal() {
            document.getElementById('addSupplierModal').style.display = 'block';
        }

        function closeAddSupplierModal() {
            document.getElementById('addSupplierModal').style.display = 'none';
        }

        function viewSupplier(supplierId) {
            // Implement view supplier details
            window.location.href = 'supplier-details.php?id=' + supplierId;
        }

        function editSupplier(supplierId) {
            // Implement edit supplier
            window.location.href = 'edit-supplier.php?id=' + supplierId;
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('addSupplierModal');
            if (event.target === modal) {
                closeAddSupplierModal();
            }
        }
    </script>

    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--light);
            border-radius: 12px 12px 0 0;
        }

        .modal-header h3 {
            margin: 0;
            color: var(--dark);
        }

        .close {
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            color: var(--secondary);
        }

        .close:hover {
            color: var(--dark);
        }

        .modal-body {
            padding: 25px;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-primary {
            background: rgba(44, 123, 229, 0.1);
            color: var(--primary);
        }

        @media (max-width: 768px) {
            .modal-content {
                margin: 10% auto;
                width: 95%;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
            
            .action-buttons .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</body>
</html>