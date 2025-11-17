<?php
require_once '../config.php';
require_once '../db.php';
requireAuth();


$page_title = "Dashboard";
$page_description = "Pharmacy Management System Overview";


try {
    
    $stmt = $db->query("SELECT COUNT(*) as total_medicines FROM medicines");
    $total_medicines = $stmt->fetch(PDO::FETCH_ASSOC)['total_medicines'];

    
    $stmt = $db->query("SELECT COUNT(*) as low_stock FROM medicines WHERE quantity <= alert_threshold");
    $low_stock = $stmt->fetch(PDO::FETCH_ASSOC)['low_stock'];

    
    $stmt = $db->query("SELECT COUNT(*) as expiring_soon FROM medicines WHERE expiry_date IS NOT NULL AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND expiry_date >= CURDATE()");
    $expiring_soon = $stmt->fetch(PDO::FETCH_ASSOC)['expiring_soon'];

    
    $stmt = $db->query("SELECT COUNT(*) as expired FROM medicines WHERE expiry_date IS NOT NULL AND expiry_date < CURDATE()");
    $expired_medicines = $stmt->fetch(PDO::FETCH_ASSOC)['expired'];

    
    $stmt = $db->query("SELECT COUNT(*) as today_sales FROM sales WHERE DATE(created_at) = CURDATE()");
    $today_sales = $stmt->fetch(PDO::FETCH_ASSOC)['today_sales'];

    
    $stmt = $db->query("SELECT COALESCE(SUM(total_amount), 0) as today_revenue FROM sales WHERE DATE(created_at) = CURDATE()");
    $today_revenue = $stmt->fetch(PDO::FETCH_ASSOC)['today_revenue'];

    
    $stmt = $db->query("SELECT s.invoice_no, s.total_amount, s.created_at, u.name as cashier 
                       FROM sales s 
                       LEFT JOIN users u ON s.user_id = u.id 
                       ORDER BY s.created_at DESC 
                       LIMIT 5");
    $recent_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    $stmt = $db->query("SELECT name, expiry_date, quantity 
                       FROM medicines 
                       WHERE expiry_date IS NOT NULL 
                       AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                       ORDER BY expiry_date ASC 
                       LIMIT 10");
    $expiring_medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                
                <div class="stats-grid">
                    <a href="medicines.php" class="stat-card clickable-card">
                        <div class="stat-icon primary">
                            <i class="fas fa-pills"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_medicines; ?></h3>
                            <p>Total Medicines</p>
                            <small>Click to view all medicines</small>
                        </div>
                        <div class="card-hover-effect">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                    
                    <a href="medicines.php?filter=low_stock" class="stat-card clickable-card">
                        <div class="stat-icon warning">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $low_stock; ?></h3>
                            <p>Low Stock Alert</p>
                            <small>Click to view low stock items</small>
                        </div>
                        <div class="card-hover-effect">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>

                    <a href="medicines.php?filter=expiring" class="stat-card clickable-card">
                        <div class="stat-icon danger">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $expiring_soon + $expired_medicines; ?></h3>
                            <p>Expiry Alert</p>
                            <small>
                                <?php if ($expired_medicines > 0 && $expiring_soon > 0): ?>
                                    <?php echo $expired_medicines; ?> expired | <?php echo $expiring_soon; ?> expiring soon
                                <?php elseif ($expired_medicines > 0): ?>
                                    <?php echo $expired_medicines; ?> expired
                                <?php elseif ($expiring_soon > 0): ?>
                                    <?php echo $expiring_soon; ?> expiring soon
                                <?php else: ?>
                                    No expiry issues
                                <?php endif; ?>
                            </small>
                        </div>
                        <div class="card-hover-effect">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                    
                    <a href="sales.php" class="stat-card clickable-card">
                        <div class="stat-icon success">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $today_sales; ?></h3>
                            <p>Today's Sales</p>
                            <small>Click to view sales history</small>
                        </div>
                        <div class="card-hover-effect">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                    
                    <a href="reports.php" class="stat-card clickable-card">
                        <div class="stat-icon revenue">
                            <i class="fas fa-rupee-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Rs. <?php echo number_format($today_revenue, 2); ?></h3>
                            <p>Today's Revenue</p>
                            <small>Click to view detailed reports</small>
                        </div>
                        <div class="card-hover-effect">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                </div>

                
                <?php if (!empty($expiring_medicines)): ?>
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-calendar-times"></i> Expiring Medicines</h3>
                        <a href="medicines.php?filter=expiring" class="btn-link">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Medicine Name</th>
                                        <th>Expiry Date</th>
                                        <th>Days Left</th>
                                        <th>Stock</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($expiring_medicines as $medicine): 
                                        $expiry_date = new DateTime($medicine['expiry_date']);
                                        $today = new DateTime();
                                        $days_left = $today->diff($expiry_date)->days;
                                        $is_expired = $expiry_date < $today;
                                        
                                        if ($is_expired) {
                                            $status_class = 'danger';
                                            $status_text = 'Expired';
                                            $days_text = 'Expired ' . $days_left . ' days ago';
                                        } elseif ($days_left <= 7) {
                                            $status_class = 'danger';
                                            $status_text = 'Critical';
                                            $days_text = $days_left . ' days';
                                        } elseif ($days_left <= 30) {
                                            $status_class = 'warning';
                                            $status_text = 'Warning';
                                            $days_text = $days_left . ' days';
                                        } else {
                                            $status_class = 'info';
                                            $status_text = 'OK';
                                            $days_text = $days_left . ' days';
                                        }
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($medicine['name']); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($medicine['expiry_date'])); ?></td>
                                            <td><?php echo $days_text; ?></td>
                                            <td><?php echo $medicine['quantity']; ?> units</td>
                                            <td>
                                                <span class="badge badge-<?php echo $status_class; ?>">
                                                    <?php echo $status_text; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                
                <div class="card quick-actions-card">
                    <div class="card-header">
                        <h3>Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions-grid">
                            <a href="pos.php" class="quick-action-btn">
                                <i class="fas fa-cash-register"></i>
                                <span>New Sale</span>
                            </a>
                            <a href="medicines.php" class="quick-action-btn">
                                <i class="fas fa-pills"></i>
                                <span>Manage Medicines</span>
                            </a>
                            <a href="stocks.php" class="quick-action-btn">
                                <i class="fas fa-boxes"></i>
                                <span>Stock Management</span>
                            </a>
                            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                            <a href="reports.php" class="quick-action-btn">
                                <i class="fas fa-chart-bar"></i>
                                <span>View Reports</span>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                
                <div class="card">
                    <div class="card-header">
                        <h3>Recent Sales</h3>
                        <a href="sales.php" class="btn-link">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_sales)): ?>
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Invoice No</th>
                                            <th>Cashier</th>
                                            <th>Amount</th>
                                            <th>Date & Time</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_sales as $sale): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($sale['invoice_no']); ?></td>
                                                <td><?php echo htmlspecialchars($sale['cashier']); ?></td>
                                                <td>Rs. <?php echo number_format($sale['total_amount'], 2); ?></td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($sale['created_at'])); ?></td>
                                                <td>
                                                    <a href="sales.php?view=<?php echo $sale['invoice_no']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="no-data">No recent sales found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/script.js"></script>
    <script>
        
        document.addEventListener('DOMContentLoaded', function() {
            const clickableCards = document.querySelectorAll('.clickable-card');
            
            clickableCards.forEach(card => {
                card.addEventListener('click', function(e) {
                    
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.cssText = `
                        position: absolute;
                        border-radius: 50%;
                        background: rgba(255, 255, 255, 0.6);
                        transform: scale(0);
                        animation: ripple 600ms linear;
                        width: ${size}px;
                        height: ${size}px;
                        left: ${x}px;
                        top: ${y}px;
                    `;
                    
                    this.style.position = 'relative';
                    this.style.overflow = 'hidden';
                    this.appendChild(ripple);
                    
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
        });
    </script>

    <style>
        .stat-icon.danger {
            background: linear-gradient(135deg, #f56565 0%, #c53030 100%);
        }

        .alert-section {
            margin-bottom: 30px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }

        .alert-danger {
            background: #fee;
            color: #c33;
            border-left: 4px solid #c33;
        }

        .alert-warning {
            background: #fffbea;
            color: #f59e0b;
            border-left: 4px solid #f59e0b;
        }

        .alert i {
            font-size: 18px;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-danger {
            background: #fee;
            color: #c33;
        }

        .badge-warning {
            background: #fffbea;
            color: #f59e0b;
        }

        .badge-info {
            background: #e0f2fe;
            color: #0369a1;
        }

        .clickable-card {
            display: flex;
            text-decoration: none;
            color: inherit;
            position: relative;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .clickable-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .clickable-card:hover .card-hover-effect {
            opacity: 1;
            transform: translateX(5px);
        }

        .card-hover-effect {
            position: absolute;
            top: 20px;
            right: 20px;
            opacity: 0;
            transition: all 0.3s ease;
            color: var(--primary);
            font-size: 18px;
        }

        .stat-info small {
            display: block;
            margin-top: 5px;
            font-size: 12px;
            color: var(--secondary);
            opacity: 0.8;
        }

        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        
        .stat-card {
            position: relative;
            overflow: hidden;
        }

        
        .quick-action-btn {
            transition: all 0.3s ease;
        }

        .quick-action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(44, 123, 229, 0.3);
        }

        
        @media (max-width: 768px) {
            .clickable-card:hover {
                transform: translateY(-2px);
            }
            
            .card-hover-effect {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
</body>
</html>