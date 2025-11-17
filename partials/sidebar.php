
<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}
?>

<div class="sidebar">
    <div class="sidebar-header">
        <h2><i class="fas fa-clinic-medical"></i> Medi Zone</h2>
        <div class="user-info">
            <div class="user-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="user-details">
                <a href="profile.php" style="color:white;text-decoration:none;">
<strong ><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>
                
                </a>
                <span><?php echo ucfirst($_SESSION['user_role']); ?></span>
            </div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="medicines.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'medicines.php' ? 'active' : ''; ?>">
                    <i class="fas fa-pills"></i> Medicines
                </a>
            </li>
            <li>
                <a href="stocks.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'stock.php' ? 'active' : ''; ?>">
                    <i class="fas fa-boxes"></i> Stock Management
                </a>
            </li>
            <li>
                <a href="pos.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'pos.php' ? 'active' : ''; ?>">
                    <i class="fas fa-cash-register"></i> POS System
                </a>
            </li>
            <li>
                <a href="sales.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'sales.php' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i> Sales History
                </a>
            </li>
            
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <li>
                <a href="suppliers.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'suppliers.php' ? 'active' : ''; ?>">
                    <i class="fas fa-truck"></i> Suppliers
                </a>
            </li>
            <li>
                <a href="reports.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <a href="../logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
        <p>Medi Zone &copy; <?php echo date('Y'); ?></p>
    </div>
</div>
