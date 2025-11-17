
<?php
require_once 'db.php';
require_once 'config.php';

$error = '';
$success = '';

// Handle Registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $name = trim($_POST['reg_name']);
    $email = trim($_POST['reg_email']);
    $password = trim($_POST['reg_password']);
    $confirm_password = trim($_POST['reg_confirm_password']);
    $phone = trim($_POST['reg_phone']);
    $address = trim($_POST['reg_address']);
    $city = trim($_POST['reg_city']);
    $postal_code = trim($_POST['reg_postal_code']);

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        try {
            // Check if email already exists
            $stmt = $db->prepare("SELECT id FROM customers WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $error = 'Email already registered. Please login.';
            } else {
                // Insert new customer
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO customers (name, email, password, phone, address, city, postal_code) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $hashed_password, $phone, $address, $city, $postal_code]);
                
                $success = 'Registration successful! Please login to continue.';
            }
        } catch(PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Handle Login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $user_type = $_POST['user_type'];

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        try {
            if ($user_type === 'customer') {
                // Customer login
                $query = "SELECT id, name, email, password FROM customers WHERE email = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$email]);
                
                if ($stmt->rowCount() == 1) {
                    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (password_verify($password, $customer['password'])) {
                        $_SESSION['customer_id'] = $customer['id'];
                        $_SESSION['customer_name'] = $customer['name'];
                        $_SESSION['customer_email'] = $customer['email'];
                        $_SESSION['user_type'] = 'customer';
                        
                        header('Location: index.php');
                        exit;
                    } else {
                        $error = 'Invalid password.';
                    }
                } else {
                    $error = 'No customer account found with that email.';
                }
            } else {
                // Admin/Cashier login
                $query = "SELECT id, name, email, password, role FROM users WHERE email = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$email]);
                
                if ($stmt->rowCount() == 1) {
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (password_verify($password, $user['password'])) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_role'] = $user['role'];
                        $_SESSION['user_type'] = 'staff';
                        
                        header('Location: pages/dashboard.php');
                        exit;
                    } else {
                        $error = 'Invalid password.';
                    }
                } else {
                    $error = 'No staff account found with that email.';
                }
            }
        } catch(PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Management - Login</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .login-tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 2px solid #e3ebf6;
        }

        .tab-btn {
            flex: 1;
            padding: 12px;
            background: transparent;
            border: none;
            color: #6e84a3;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .tab-btn.active {
            color: #667eea;
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: #667eea;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .user-type-select {
            margin-bottom: 20px;
        }

        .user-type-select label {
            display: block;
            margin-bottom: 8px;
            color: #12263f;
            font-weight: 600;
        }

        .user-type-select select {
            width: 100%;
            padding: 12px;
            border: 1px solid #d2ddec;
            border-radius: 8px;
            font-size: 14px;
            background: white;
        }

        .register-note {
            margin-top: 10px;
            padding: 10px;
            background: #f0f7ff;
            border-left: 3px solid #667eea;
            font-size: 13px;
            color: #6e84a3;
            display: none;
        }

        .register-note.show {
            display: block;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1><i class="fas fa-clinic-medical"></i> Medi Zone</h1>
                <p>Pharmacy Management System</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Tabs -->
            <div class="login-tabs">
                <button class="tab-btn active" onclick="switchTab('login')">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
                <button class="tab-btn" onclick="switchTab('register')">
                    <i class="fas fa-user-plus"></i> Register
                </button>
            </div>

            <!-- Login Form -->
            <div id="login-tab" class="tab-content active">
                <form method="POST" action="">
                    <input type="hidden" name="login" value="1">
                    
                    <div class="user-type-select">
                        <label for="user_type">Login As</label>
                        <select id="user_type" name="user_type" required onchange="toggleRegisterNote()">
                            <option value="customer">Customer</option>
                            <option value="staff">Admin / Cashier</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" required placeholder="Enter your email">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" required placeholder="Enter your password">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-login">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>

                <div id="register-note" class="register-note">
                    <i class="fas fa-info-circle"></i> Don't have a customer account? Switch to the <strong>Register</strong> tab to create one!
                </div>
            </div>

            <!-- Register Form -->
            <div id="register-tab" class="tab-content">
                <form method="POST" action="">
                    <input type="hidden" name="register" value="1">
                    
                    <div style="background: #f0f7ff; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; color: #6e84a3;">
                        <i class="fas fa-info-circle"></i> Register as a customer to order medicines online with home delivery.
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="reg_name">Full Name *</label>
                            <div class="input-group">
                                <i class="fas fa-user"></i>
                                <input type="text" id="reg_name" name="reg_name" required placeholder="Your full name">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="reg_phone">Phone Number</label>
                            <div class="input-group">
                                <i class="fas fa-phone"></i>
                                <input type="tel" id="reg_phone" name="reg_phone" placeholder="Your phone number">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="reg_email">Email Address *</label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="reg_email" name="reg_email" required placeholder="Your email address">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="reg_password">Password *</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="reg_password" name="reg_password" required placeholder="Minimum 6 characters">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="reg_confirm_password">Confirm Password *</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="reg_confirm_password" name="reg_confirm_password" required placeholder="Re-enter password">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="reg_address">Address</label>
                        <div class="input-group">
                            <i class="fas fa-map-marker-alt"></i>
                            <input type="text" id="reg_address" name="reg_address" placeholder="Street address">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="reg_city">City</label>
                            <div class="input-group">
                                <i class="fas fa-city"></i>
                                <input type="text" id="reg_city" name="reg_city" placeholder="City">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="reg_postal_code">Postal Code</label>
                            <div class="input-group">
                                <i class="fas fa-mail-bulk"></i>
                                <input type="text" id="reg_postal_code" name="reg_postal_code" placeholder="Postal code">
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-login">
                        <i class="fas fa-user-plus"></i> Register Account
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            
            // Show selected tab
            if (tab === 'login') {
                document.getElementById('login-tab').classList.add('active');
                document.querySelectorAll('.tab-btn')[0].classList.add('active');
            } else {
                document.getElementById('register-tab').classList.add('active');
                document.querySelectorAll('.tab-btn')[1].classList.add('active');
            }
        }

        function toggleRegisterNote() {
            const userType = document.getElementById('user_type').value;
            const note = document.getElementById('register-note');
            
            if (userType === 'customer') {
                note.classList.add('show');
            } else {
                note.classList.remove('show');
            }
        }

        // Initial check
        toggleRegisterNote();
    </script>
</body>
</html>
