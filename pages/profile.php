
<?php
require_once '../config.php';
require_once '../db.php';
requireAuth();

$page_title = "My Profile";
$page_description = "Manage your account profile";


try {
    $stmt = $db->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header('Location: dashboard.php');
        exit;
    }
} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    
    if ($email !== $user['email']) {
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->rowCount() > 0) {
            $errors[] = "Email already exists";
        }
    }
    
    
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $errors[] = "Current password is required to change password";
        } else {
            
            $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $current_user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!password_verify($current_password, $current_user['password'])) {
                $errors[] = "Current password is incorrect";
            } elseif ($new_password !== $confirm_password) {
                $errors[] = "New passwords do not match";
            } elseif (strlen($new_password) < 6) {
                $errors[] = "New password must be at least 6 characters long";
            }
        }
    }
    
    if (empty($errors)) {
        try {
            if (!empty($new_password)) {
            
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
                $stmt->execute([$name, $email, $hashed_password, $_SESSION['user_id']]);
            } else {
                
                $stmt = $db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                $stmt->execute([$name, $email, $_SESSION['user_id']]);
            }
            
            
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            
            $success = "Profile updated successfully!";
            
            
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
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
                    <h2>My Profile</h2>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="profile-container">
                    <div class="card">
                        <div class="card-header">
                            <h3>Profile Information</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" id="profileForm">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="name">Full Name *</label>
                                        <input type="text" id="name" name="name" required 
                                               value="<?php echo htmlspecialchars($user['name']); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="email">Email Address *</label>
                                        <input type="email" id="email" name="email" required 
                                               value="<?php echo htmlspecialchars($user['email']); ?>">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="role">Role</label>
                                        <input type="text" id="role" value="<?php echo ucfirst($user['role']); ?>" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="member_since">Member Since</label>
                                        <input type="text" id="member_since" 
                                               value="<?php echo date('F j, Y', strtotime($user['created_at'])); ?>" readonly>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <h4>Change Password</h4>
                                    <p class="text-muted">Leave blank if you don't want to change password</p>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="current_password">Current Password</label>
                                            <input type="password" id="current_password" name="current_password">
                                        </div>
                                        <div class="form-group">
                                            <label for="new_password">New Password</label>
                                            <input type="password" id="new_password" name="new_password">
                                        </div>
                                        <div class="form-group">
                                            <label for="confirm_password">Confirm New Password</label>
                                            <input type="password" id="confirm_password" name="confirm_password">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" name="update_profile" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Profile
                                    </button>
                                    <button type="reset" class="btn btn-secondary">Reset</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    
                    <div class="card">
                        <div class="card-header">
                            <h3>Account Statistics</h3>
                        </div>
                        <div class="card-body">
                            <div class="stats-grid-mini">
                                <?php
                                
                                try {
                                    
                                    $stmt = $db->prepare("SELECT COUNT(*) as total_sales FROM sales WHERE user_id = ?");
                                    $stmt->execute([$_SESSION['user_id']]);
                                    $total_sales = $stmt->fetch(PDO::FETCH_ASSOC)['total_sales'];
                                    
                                    
                                    $stmt = $db->prepare("SELECT COUNT(*) as today_sales FROM sales WHERE user_id = ? AND DATE(created_at) = CURDATE()");
                                    $stmt->execute([$_SESSION['user_id']]);
                                    $today_sales = $stmt->fetch(PDO::FETCH_ASSOC)['today_sales'];
                                    
                                    
                                    $stmt = $db->prepare("SELECT COALESCE(SUM(total_amount), 0) as total_revenue FROM sales WHERE user_id = ?");
                                    $stmt->execute([$_SESSION['user_id']]);
                                    $total_revenue = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'];
                                    
                                } catch(PDOException $e) {
                                    $total_sales = $today_sales = $total_revenue = 0;
                                }
                                ?>
                                
                                <div class="stat-mini">
                                    <div class="stat-icon-mini primary">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                    <div class="stat-info-mini">
                                        <h3><?php echo $total_sales; ?></h3>
                                        <p>Total Sales</p>
                                    </div>
                                </div>
                                
                                <div class="stat-mini">
                                    <div class="stat-icon-mini success">
                                        <i class="fas fa-calendar-day"></i>
                                    </div>
                                    <div class="stat-info-mini">
                                        <h3><?php echo $today_sales; ?></h3>
                                        <p>Today's Sales</p>
                                    </div>
                                </div>
                                
                                <div class="stat-mini">
                                    <div class="stat-icon-mini revenue">
                                        <i class="fas fa-rupee-sign"></i>
                                    </div>
                                    <div class="stat-info-mini">
                                        <h3>Rs. <?php echo number_format($total_revenue, 2); ?></h3>
                                        <p>Total Revenue</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/script.js"></script>
</body>
</html>
