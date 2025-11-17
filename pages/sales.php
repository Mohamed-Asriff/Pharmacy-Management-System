<?php
require_once '../config.php';
require_once '../db.php';
requireAuth();

$page_title = "Sales History";
$page_description = "View all sales transactions";

// Fetch statistics separately (ALL sales, not limited)
try {
    // Total transactions (ALL sales)
    $totalTransactions = $db->query("SELECT COUNT(*) as count FROM sales")->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Total revenue (ALL sales)
    $totalRevenue = $db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM sales")->fetch(PDO::FETCH_ASSOC)['total'];
    
    
    $today = date('Y-m-d');
    $todayRevenue = $db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM sales WHERE DATE(created_at) = '$today'")->fetch(PDO::FETCH_ASSOC)['total'];
    
    
    $query = "SELECT s.*, u.name as cashier_name 
              FROM sales s 
              LEFT JOIN users u ON s.user_id = u.id 
              ORDER BY s.created_at DESC 
              LIMIT 100";
    $stmt = $db->query($query);
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $totalTransactions = 0;
    $totalRevenue = 0;
    $todayRevenue = 0;
    $sales = [];
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
                    <h2>Sales History</h2>
                    <a href="pos.php" class="btn btn-primary">
                        <i class="fas fa-cash-register"></i> New Sale
                    </a>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon success">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $totalTransactions; ?></h3>
                            <p>Total Transactions</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon revenue">
                            <i class="fas fa-rupee-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Rs. <?php echo number_format($totalRevenue, 2); ?></h3>
                            <p>Total Revenue</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon primary">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Rs. <?php echo number_format($todayRevenue, 2); ?></h3>
                            <p>Today's Revenue</p>
                        </div>
                    </div>
                </div>

                
                <div class="card">
                    <div class="card-header">
                        <h3>Recent Sales</h3>
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchSales" placeholder="Search sales...">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="data-table" id="salesTable">
                                <thead>
                                    <tr>
                                        <th>Invoice No</th>
                                        <th>Date & Time</th>
                                        <th>Cashier</th>
                                        <th>Total Amount</th>
                                        <th>Paid Amount</th>
                                        <th>Change</th>
                                        <th>Payment Method</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sales as $sale): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($sale['invoice_no']); ?></strong>
                                            </td>
                                            <td><?php echo date('M j, Y g:i A', strtotime($sale['created_at'])); ?></td>
                                            <td><?php echo htmlspecialchars($sale['cashier_name']); ?></td>
                                            <td>Rs. <?php echo number_format($sale['total_amount'], 2); ?></td>
                                            <td>Rs. <?php echo number_format($sale['paid_amount'], 2); ?></td>
                                            <td>Rs. <?php echo number_format($sale['change_amount'], 2); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo strtolower($sale['payment_method']); ?>">
                                                    <?php echo ucfirst($sale['payment_method']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-primary view-sale" 
                                                        data-sale-id="<?php echo $sale['id']; ?>">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
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
    <script>
        // Sales search functionality
        document.getElementById('searchSales')?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#salesTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // View sale details
        document.querySelectorAll('.view-sale').forEach(button => {
            button.addEventListener('click', function() {
                const saleId = this.dataset.saleId;
                // Implement view sale details modal or page
                alert('View sale details for ID: ' + saleId);
            });
        });
    </script>
</body>
</html>
