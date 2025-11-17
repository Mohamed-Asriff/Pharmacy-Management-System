<?php
require_once '../config.php';
require_once '../db.php';
requireAuth();


if ($_SESSION['user_role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

$page_title = "Reports & Analytics";
$page_description = "View system reports and analytics";


$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');


try {
    
    $sales_query = "SELECT 
                    COUNT(*) as total_sales,
                    SUM(total_amount) as total_revenue,
                    AVG(total_amount) as avg_sale,
                    MIN(total_amount) as min_sale,
                    MAX(total_amount) as max_sale
                    FROM sales 
                    WHERE DATE(created_at) BETWEEN ? AND ?";
    $stmt = $db->prepare($sales_query);
    $stmt->execute([$start_date, $end_date]);
    $sales_summary = $stmt->fetch(PDO::FETCH_ASSOC);


    $daily_sales_query = "SELECT 
                         DATE(created_at) as sale_date,
                         COUNT(*) as sales_count,
                         SUM(total_amount) as daily_revenue
                         FROM sales 
                         WHERE DATE(created_at) BETWEEN ? AND ?
                         GROUP BY DATE(created_at)
                         ORDER BY sale_date";
    $stmt = $db->prepare($daily_sales_query);
    $stmt->execute([$start_date, $end_date]);
    $daily_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);


    $top_medicines_query = "SELECT 
                           m.name,
                           m.sku,
                           SUM(si.quantity) as total_sold,
                           SUM(si.subtotal) as total_revenue
                           FROM sale_items si
                           JOIN medicines m ON si.medicine_id = m.id
                           JOIN sales s ON si.sale_id = s.id
                           WHERE DATE(s.created_at) BETWEEN ? AND ?
                           GROUP BY m.id, m.name, m.sku
                           ORDER BY total_sold DESC
                           LIMIT 10";
    $stmt = $db->prepare($top_medicines_query);
    $stmt->execute([$start_date, $end_date]);
    $top_medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    $payment_methods_query = "SELECT 
                             payment_method,
                             COUNT(*) as transaction_count,
                             SUM(total_amount) as total_amount
                             FROM sales 
                             WHERE DATE(created_at) BETWEEN ? AND ?
                             GROUP BY payment_method";
    $stmt = $db->prepare($payment_methods_query);
    $stmt->execute([$start_date, $end_date]);
    $payment_methods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    $low_stock_query = "SELECT 
                       name,
                       sku,
                       quantity,
                       alert_threshold,
                       price
                       FROM medicines 
                       WHERE quantity <= alert_threshold
                       ORDER BY quantity ASC";
    $stmt = $db->query($low_stock_query);
    $low_stock = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    $cashier_sales_query = "SELECT 
                           u.name as cashier_name,
                           COUNT(*) as sales_count,
                           SUM(s.total_amount) as total_revenue
                           FROM sales s
                           JOIN users u ON s.user_id = u.id
                           WHERE DATE(s.created_at) BETWEEN ? AND ?
                           GROUP BY u.id, u.name
                           ORDER BY total_revenue DESC";
    $stmt = $db->prepare($cashier_sales_query);
    $stmt->execute([$start_date, $end_date]);
    $cashier_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <?php include '../partials/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include '../partials/header.php'; ?>
            
            <div class="content">
                <div class="page-actions">
                    <h2>Reports & Analytics</h2>
                    <div class="report-actions">
                        <button class="btn btn-primary" onclick="printReport()">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                        <button class="btn btn-success" onclick="exportToExcel()">
                            <i class="fas fa-file-excel"></i> Export to Excel
                        </button>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                
                <div class="card">
                    <div class="card-header">
                        <h3>Filter Reports</h3>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="filter-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" id="start_date" name="start_date" 
                                           value="<?php echo $start_date; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" id="end_date" name="end_date" 
                                           value="<?php echo $end_date; ?>" required>
                                </div>
                                <div class="form-group" style="align-self: flex-end;">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter"></i> Apply Filter
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="resetFilter()">
                                        <i class="fas fa-refresh"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon revenue">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $sales_summary['total_sales'] ?? 0; ?></h3>
                            <p>Total Sales</p>
                            <small>Period: <?php echo date('M j, Y', strtotime($start_date)); ?> - <?php echo date('M j, Y', strtotime($end_date)); ?></small>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon success">
                            <i class="fas fa-rupee-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Rs. <?php echo number_format($sales_summary['total_revenue'] ?? 0, 2); ?></h3>
                            <p>Total Revenue</p>
                            <small>Gross Income</small>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon primary">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Rs. <?php echo number_format($sales_summary['avg_sale'] ?? 0, 2); ?></h3>
                            <p>Average Sale</p>
                            <small>Per Transaction</small>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon warning">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo count($low_stock); ?></h3>
                            <p>Low Stock Items</p>
                            <small>Requires Attention</small>
                        </div>
                    </div>
                </div>

                <div class="reports-grid">
                    
                    <div class="card chart-card">
                        <div class="card-header">
                            <h3>Sales Trend</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="salesTrendChart" height="250"></canvas>
                        </div>
                    </div>

                    
                    <div class="card chart-card">
                        <div class="card-header">
                            <h3>Payment Methods</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="paymentMethodsChart" height="250"></canvas>
                        </div>
                    </div>

                    
                    <div class="card">
                        <div class="card-header">
                            <h3>Top Selling Medicines</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Medicine</th>
                                            <th>SKU</th>
                                            <th>Units Sold</th>
                                            <th>Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($top_medicines as $medicine): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($medicine['name']); ?></td>
                                                <td><?php echo htmlspecialchars($medicine['sku']); ?></td>
                                                <td><?php echo $medicine['total_sold']; ?></td>
                                                <td>Rs. <?php echo number_format($medicine['total_revenue'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    
                    <div class="card">
                        <div class="card-header">
                            <h3>Low Stock Alert</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Medicine</th>
                                            <th>SKU</th>
                                            <th>Current Stock</th>
                                            <th>Alert Level</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($low_stock as $item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                                <td><?php echo htmlspecialchars($item['sku']); ?></td>
                                                <td class="<?php echo $item['quantity'] == 0 ? 'out-of-stock' : 'low-stock'; ?>">
                                                    <?php echo $item['quantity']; ?>
                                                </td>
                                                <td><?php echo $item['alert_threshold']; ?></td>
                                                <td>Rs. <?php echo number_format($item['price'], 2); ?></td>
                                                <td>
                                                    <?php if ($item['quantity'] == 0): ?>
                                                        <span class="status-badge out-of-stock">Out of Stock</span>
                                                    <?php else: ?>
                                                        <span class="status-badge low-stock">Low Stock</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    
                    <div class="card">
                        <div class="card-header">
                            <h3>Sales Performance by Cashier</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Cashier</th>
                                            <th>Sales Count</th>
                                            <th>Total Revenue</th>
                                            <th>Average Sale</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cashier_sales as $cashier): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($cashier['cashier_name']); ?></td>
                                                <td><?php echo $cashier['sales_count']; ?></td>
                                                <td>Rs. <?php echo number_format($cashier['total_revenue'], 2); ?></td>
                                                <td>Rs. <?php echo number_format($cashier['total_revenue'] / $cashier['sales_count'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                
                    <div class="card">
                        <div class="card-header">
                            <h3>Detailed Sales Summary</h3>
                        </div>
                        <div class="card-body">
                            <div class="summary-grid">
                                <div class="summary-item">
                                    <span class="summary-label">Highest Sale</span>
                                    <span class="summary-value">Rs. <?php echo number_format($sales_summary['max_sale'] ?? 0, 2); ?></span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">Lowest Sale</span>
                                    <span class="summary-value">Rs. <?php echo number_format($sales_summary['min_sale'] ?? 0, 2); ?></span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">Total Transactions</span>
                                    <span class="summary-value"><?php echo $sales_summary['total_sales'] ?? 0; ?></span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">Payment Methods Used</span>
                                    <span class="summary-value"><?php echo count($payment_methods); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        
        document.addEventListener('DOMContentLoaded', function() {
            
            const salesTrendCtx = document.getElementById('salesTrendChart').getContext('2d');
            const salesTrendChart = new Chart(salesTrendCtx, {
                type: 'line',
                data: {
                    labels: [<?php echo implode(',', array_map(function($sale) { return "'" . date('M j', strtotime($sale['sale_date'])) . "'"; }, $daily_sales)); ?>],
                    datasets: [{
                        label: 'Daily Revenue',
                        data: [<?php echo implode(',', array_column($daily_sales, 'daily_revenue')); ?>],
                        borderColor: '#2c7be5',
                        backgroundColor: 'rgba(44, 123, 229, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Daily Sales Revenue Trend'
                        }
                    }
                }
            });

            
            const paymentMethodsCtx = document.getElementById('paymentMethodsChart').getContext('2d');
            const paymentMethodsChart = new Chart(paymentMethodsCtx, {
                type: 'doughnut',
                data: {
                    labels: [<?php echo implode(',', array_map(function($method) { return "'" . ucfirst($method['payment_method']) . "'"; }, $payment_methods)); ?>],
                    datasets: [{
                        data: [<?php echo implode(',', array_column($payment_methods, 'total_amount')); ?>],
                        backgroundColor: [
                            '#2c7be5',
                            '#00d97e',
                            '#f6c343'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        title: {
                            display: true,
                            text: 'Payment Method Distribution'
                        }
                    }
                }
            });
        });

    
        function printReport() {
            window.print();
        }

        function exportToExcel() {
            alert('Export to Excel functionality would be implemented here.');
        
        }

        function resetFilter() {
            document.getElementById('start_date').value = '<?php echo date('Y-m-01'); ?>';
            document.getElementById('end_date').value = '<?php echo date('Y-m-t'); ?>';
            document.querySelector('.filter-form').submit();
        }

        
        setInterval(function() {
            
        }, 300000);
    </script>

    <style>
        .reports-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }

        .chart-card {
            grid-column: span 1;
        }

        .filter-form .form-row {
            align-items: flex-end;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .summary-item {
            background: var(--light);
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid var(--primary);
        }

        .summary-label {
            display: block;
            font-size: 14px;
            color: var(--secondary);
            margin-bottom: 8px;
        }

        .summary-value {
            display: block;
            font-size: 24px;
            font-weight: 700;
            color: var(--dark);
        }

        .report-actions {
            display: flex;
            gap: 10px;
        }

        @media (max-width: 1024px) {
            .reports-grid {
                grid-template-columns: 1fr;
            }
            
            .chart-card {
                grid-column: span 1;
            }
        }

        @media print {
            .sidebar, .main-header, .page-actions .btn, .filter-form {
                display: none !important;
            }
            
            .main-content {
                margin-left: 0 !important;
            }
            
            .content {
                padding: 0 !important;
            }
        }
    </style>
</body>
</html>