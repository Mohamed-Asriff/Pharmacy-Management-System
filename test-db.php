<?php
/**
 * Database Connection Test Page
 * This page tests the database connection and displays the status
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test - Medi Zone</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .test-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            max-width: 600px;
            width: 100%;
        }

        .test-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .test-header h1 {
            color: #667eea;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .test-header p {
            color: #6e84a3;
            font-size: 14px;
        }

        .status-card {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }

        .status-success {
            background: #d4edda;
            border-color: #00d97e;
            color: #155724;
        }

        .status-error {
            background: #f8d7da;
            border-color: #e63757;
            color: #721c24;
        }

        .status-icon {
            font-size: 48px;
            text-align: center;
            margin-bottom: 15px;
        }

        .status-success .status-icon {
            color: #00d97e;
        }

        .status-error .status-icon {
            color: #e63757;
        }

        .status-message {
            font-size: 18px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 15px;
        }

        .details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .details h3 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #12263f;
        }

        .details p {
            font-size: 13px;
            color: #6e84a3;
            margin: 5px 0;
            padding: 5px 0;
            border-bottom: 1px solid #e3ebf6;
        }

        .details p:last-child {
            border-bottom: none;
        }

        .details strong {
            color: #12263f;
            display: inline-block;
            width: 150px;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
        }

        .btn-secondary {
            background: #6e84a3;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6f8a;
        }

        .info-box {
            background: #fff3cd;
            border-left: 4px solid #f6c343;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .info-box h4 {
            color: #856404;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .info-box ol {
            margin-left: 20px;
            color: #856404;
            font-size: 13px;
        }

        .info-box li {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <div class="test-header">
            <h1><i class="fas fa-database"></i> Database Connection Test</h1>
            <p>Medi Zone - Pharmacy Management System</p>
        </div>

        <?php
        // Database connection test
        $host = 'localhost';
        $db_name = 'pharmacy-management';
        $username = 'root';
        $password = '';
        $port = '3306';
        
        $connection_success = false;
        $error_message = '';
        $server_info = '';
        
        try {
            $conn = new PDO(
                "mysql:host=" . $host . ";port=" . $port . ";charset=utf8",
                $username, 
                $password
            );
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Check if database exists
            $stmt = $conn->query("SHOW DATABASES LIKE '" . $db_name . "'");
            $database_exists = $stmt->rowCount() > 0;
            
            if ($database_exists) {
                // Connect to specific database
                $conn = new PDO(
                    "mysql:host=" . $host . ";port=" . $port . ";dbname=" . $db_name . ";charset=utf8",
                    $username, 
                    $password
                );
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                $connection_success = true;
                $server_info = $conn->getAttribute(PDO::ATTR_SERVER_VERSION);
                
                // Get table count
                $stmt = $conn->query("SHOW TABLES");
                $table_count = $stmt->rowCount();
                
            } else {
                $error_message = "Database '" . $db_name . "' does not exist!";
            }
            
        } catch(PDOException $e) {
            $error_message = $e->getMessage();
        }
        ?>

        <?php if ($connection_success): ?>
            <div class="status-card status-success">
                <div class="status-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="status-message">
                    ✓ Database Connection Successful!
                </div>
                <div class="details">
                    <h3>Connection Details:</h3>
                    <p><strong>Host:</strong> <?php echo $host; ?></p>
                    <p><strong>Port:</strong> <?php echo $port; ?></p>
                    <p><strong>Database:</strong> <?php echo $db_name; ?></p>
                    <p><strong>Username:</strong> <?php echo $username; ?></p>
                    <p><strong>MySQL Version:</strong> <?php echo $server_info; ?></p>
                    <p><strong>Tables Found:</strong> <?php echo $table_count; ?> tables</p>
                </div>
            </div>
        <?php else: ?>
            <div class="status-card status-error">
                <div class="status-icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="status-message">
                    ✗ Database Connection Failed!
                </div>
                <div class="details">
                    <h3>Error Details:</h3>
                    <p style="color: #721c24; word-break: break-word;">
                        <?php echo htmlspecialchars($error_message); ?>
                    </p>
                </div>
                
                <div class="details" style="margin-top: 15px;">
                    <h3>Current Configuration:</h3>
                    <p><strong>Host:</strong> <?php echo $host; ?></p>
                    <p><strong>Port:</strong> <?php echo $port; ?></p>
                    <p><strong>Database:</strong> <?php echo $db_name; ?></p>
                    <p><strong>Username:</strong> <?php echo $username; ?></p>
                </div>
            </div>

            <div class="info-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Troubleshooting Steps:</h4>
                <ol>
                    <li>Make sure XAMPP Apache and MySQL services are running</li>
                    <li>Open phpMyAdmin at <strong>http://localhost/phpmyadmin</strong></li>
                    <li>Create a database named <strong><?php echo $db_name; ?></strong></li>
                    <li>Import the SQL file from <strong>sql/pharmacy.sql</strong></li>
                    <li>Refresh this page to test again</li>
                </ol>
            </div>
        <?php endif; ?>

        <div class="btn-group">
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Home
            </a>
            <a href="login.php" class="btn btn-secondary">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
        </div>
    </div>
</body>
</html>
