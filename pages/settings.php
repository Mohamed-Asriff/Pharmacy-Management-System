
<?php
require_once '../config.php';
require_once '../db.php';
requireAuth();

$page_title = "Settings";
$page_description = "System and account settings";

$success = '';
$error = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add your settings logic here
    // This can include system preferences, notification settings, etc.
    $success = "Settings updated successfully!";
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
                    <h2>Settings</h2>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="settings-container">
                    <!-- General Settings -->
                    <div class="card">
                        <div class="card-header">
                            <h3>General Settings</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="form-group">
                                    <label for="language">Language</label>
                                    <select id="language" name="language">
                                        <option value="en">English</option>
                                        <option value="es">Spanish</option>
                                        <option value="fr">French</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="timezone">Timezone</label>
                                    <select id="timezone" name="timezone">
                                        <option value="UTC">UTC</option>
                                        <option value="EST">Eastern Time</option>
                                        <option value="PST">Pacific Time</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="date_format">Date Format</label>
                                    <select id="date_format" name="date_format">
                                        <option value="Y-m-d">YYYY-MM-DD</option>
                                        <option value="d/m/Y">DD/MM/YYYY</option>
                                        <option value="m/d/Y">MM/DD/YYYY</option>
                                    </select>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Notification Settings -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Notification Settings</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="form-group checkbox-group">
                                    <input type="checkbox" id="email_notifications" name="email_notifications" checked>
                                    <label for="email_notifications">Email Notifications</label>
                                </div>
                                
                                <div class="form-group checkbox-group">
                                    <input type="checkbox" id="low_stock_alerts" name="low_stock_alerts" checked>
                                    <label for="low_stock_alerts">Low Stock Alerts</label>
                                </div>
                                
                                <div class="form-group checkbox-group">
                                    <input type="checkbox" id="sales_reports" name="sales_reports">
                                    <label for="sales_reports">Daily Sales Reports</label>
                                </div>
                                
                                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                <div class="form-group checkbox-group">
                                    <input type="checkbox" id="system_alerts" name="system_alerts" checked>
                                    <label for="system_alerts">System Alerts</label>
                                </div>
                                <?php endif; ?>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Notification Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Admin Only Settings -->
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3>Admin Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="admin-actions">
                                <a href="user-management.php" class="admin-action-btn">
                                    <i class="fas fa-users-cog"></i>
                                    <span>User Management</span>
                                    <small>Manage system users and permissions</small>
                                </a>
                                
                                <a href="backup.php" class="admin-action-btn">
                                    <i class="fas fa-database"></i>
                                    <span>Database Backup</span>
                                    <small>Backup and restore system data</small>
                                </a>
                                
                                <a href="system-logs.php" class="admin-action-btn">
                                    <i class="fas fa-clipboard-list"></i>
                                    <span>System Logs</span>
                                    <small>View system activity logs</small>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Danger Zone -->
                    <div class="card danger-zone">
                        <div class="card-header">
                            <h3>Danger Zone</h3>
                        </div>
                        <div class="card-body">
                            <div class="danger-actions">
                                <div class="danger-action">
                                    <h4>Clear Cache</h4>
                                    <p>Clear all system cache files</p>
                                    <button class="btn btn-warning" onclick="clearCache()">
                                        <i class="fas fa-broom"></i> Clear Cache
                                    </button>
                                </div>
                                
                                <div class="danger-action">
                                    <h4>Delete Account</h4>
                                    <p>Permanently delete your account and all data</p>
                                    <button class="btn btn-danger" onclick="deleteAccount()" disabled>
                                        <i class="fas fa-trash"></i> Delete Account
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/script.js"></script>
    <script>
        function clearCache() {
            if (confirm('Are you sure you want to clear all cache?')) {
                alert('Cache cleared successfully!');
            }
        }
        
        function deleteAccount() {
            alert('Account deletion is disabled for security reasons.');
        }
    </script>
</body>
</html>
