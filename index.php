
<?php
// Start session and connect to database
session_start();
require_once 'db.php';

// Check if customer is logged in
$is_logged_in = isset($_SESSION['customer_id']);
$customer_name = $is_logged_in ? $_SESSION['customer_name'] : '';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Count cart items
$cart_count = array_sum($_SESSION['cart']);

// Fetch featured medicines
$featured_medicines = [];
try {
    $stmt = $db->prepare("SELECT m.*, c.name as category_name 
                          FROM medicines m 
                          LEFT JOIN categories c ON m.category_id = c.id 
                          WHERE m.quantity > 0 
                          ORDER BY m.created_at DESC");
    $stmt->execute();
    $featured_medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // If error, continue without products
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medi Zone - Pharmacy Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Landing Page Styles */
        .landing-body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navigation */
        .navbar {
            padding: 20px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
        }

        .logo {
            font-size: 28px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo i {
            font-size: 32px;
        }

        .nav-buttons {
            display: flex;
            gap: 15px;
        }

        .btn-landing {
            padding: 12px 30px;
            border: 2px solid white;
            background: transparent;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-landing:hover {
            background: white;
            color: #667eea;
            transform: translateY(-2px);
        }

        .btn-landing-primary {
            background: white;
            color: #667eea;
        }

        .btn-landing-primary:hover {
            background: transparent;
            color: white;
        }

        /* Hero Content */
        .hero-content {
            background-image: url('images/hero-bg.jpg');
            background-size: cover;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 50px;
            text-align: center;
        }

        .hero-text h1 {
            font-size: 56px;
            margin-bottom: 20px;
            animation: fadeInUp 1s ease;
            text-shadow: 4px 4px 5px rgba(0.7,0.7,0.7,0.7);
            color: #667eea;
        }

        .hero-text p {
            font-size: 20px;
            margin-bottom: 40px;
            opacity: 0.9;
            animation: fadeInUp 1.2s ease;
            color: #667eea;
            text-shadow: 4px 4px 5px rgba(0.7,0.7,0.7,0.7);
        }

        .hero-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            animation: fadeInUp 1.4s ease;
        }

        .btn-hero {
            padding: 15px 40px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-hero-primary {
            background: white;
            color: #667eea;
        }

        .btn-hero-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .btn-hero-outline {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-hero-outline:hover {
            background: white;
            color: #667eea;
            transform: translateY(-3px);
        }

        /* Features Section */
        .features-section {
            padding: 100px 50px;
            background: #f5f7fa;
        }

        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-header h2 {
            font-size: 42px;
            color: #12263f;
            margin-bottom: 15px;
        }

        .section-header p {
            font-size: 18px;
            color: #6e84a3;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            text-align: center;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .feature-icon {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 20px;
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .feature-card h3 {
            font-size: 24px;
            color: #12263f;
            margin-bottom: 15px;
        }

        .feature-card p {
            color: #6e84a3;
            line-height: 1.8;
        }

        /* Products Section */
        .products-section {
            padding: 100px 50px;
            background: white;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .product-card {
            background: white;
            border: 1px solid #e3ebf6;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            border-color: #667eea;
        }

        .product-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #f5f7fa 0%, #e3ebf6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-image img {
            transform: scale(1.1);
        }

        .product-image i {
            font-size: 80px;
            color: #667eea;
            opacity: 0.3;
            position: absolute;
        }

        .product-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #00d97e;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .product-badge.low-stock {
            background: #f6c343;
        }

        .product-info {
            padding: 20px;
        }

        .product-category {
            font-size: 12px;
            color: #667eea;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .product-name {
            font-size: 16px;
            font-weight: 600;
            color: #12263f;
            margin-bottom: 10px;
            min-height: 40px;
        }

        .product-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .product-price {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }

        .product-stock {
            font-size: 12px;
            color: #6e84a3;
        }

        .product-stock i {
            margin-right: 5px;
        }

        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-add-cart {
            flex: 1;
            padding: 10px;
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            text-decoration: none;
            font-size: 14px;
        }

        .btn-add-cart:hover {
            background: #667eea;
            color: white;
        }

        .btn-buy-now {
            flex: 1;
            padding: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: 2px solid transparent;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            text-decoration: none;
            font-size: 14px;
        }

        .btn-buy-now:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .view-all-btn {
            text-align: center;
            margin-top: 40px;
        }

        /* Stats Section */
        .stats-section {
            padding: 80px 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .stats-grid-landing {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
        }

        .stat-item {
            padding: 20px;
        }

        .stat-number {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 18px;
            opacity: 0.9;
        }

        /* CTA Section */
        .cta-section {
            padding: 100px 50px;
            background: #12263f;
            color: white;
            text-align: center;
        }

        .cta-section h2 {
            font-size: 42px;
            margin-bottom: 20px;
        }

        .cta-section p {
            font-size: 20px;
            margin-bottom: 40px;
            opacity: 0.9;
        }

        /* Footer */
        .footer {
            background: #0a1628;
            color: white;
            padding: 40px 50px;
            text-align: center;
        }

        .footer p {
            margin: 10px 0;
            opacity: 0.8;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                padding: 20px;
                flex-direction: column;
                gap: 20px;
            }

            .hero-text h1 {
                font-size: 36px;
            }

            .hero-text p {
                font-size: 16px;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .features-section,
            .stats-section,
            .cta-section {
                padding: 60px 20px;
            }

            .section-header h2 {
                font-size: 32px;
            }

            .features-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }
        }
    </style>
</head>
<body class="landing-body">
    <!-- Hero Section -->
    <section class="hero-section">
        <!-- Navigation -->
        <nav class="navbar">
            <div class="logo">
                <i class="fas fa-clinic-medical"></i>
                <span>Medi Zone</span>
            </div>
            <div class="nav-buttons">
                <?php if ($is_logged_in): ?>
                    <a href="cart.php" class="btn-landing" style="position: relative;">
                        <i class="fas fa-shopping-cart"></i> Cart
                        <?php if ($cart_count > 0): ?>
                        <span style="position: absolute; top: -5px; right: -5px; background: #ff4444; color: white; border-radius: 50%; width: 20px; height: 20px; font-size: 11px; display: flex; align-items: center; justify-content: center; font-weight: bold;"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <span class="btn-landing" style="cursor: default; border-color: rgba(255,255,255,0.5);">
                        <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($customer_name); ?>
                    </span>
                    <a href="logout.php" class="btn-landing btn-landing-primary">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn-landing">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="login.php" class="btn-landing btn-landing-primary">
                        <i class="fas fa-rocket"></i> Get Started
                    </a>
                <?php endif; ?>
            </div>
        </nav>

        <!-- Hero Content -->
        <div class="hero-content">
            <div class="hero-text">
                <h1>Modern Pharmacy Management Made Simple</h1>
                <p>Complete Point of Sale and Inventory Management System for Your Pharmacy</p>
                <div class="hero-buttons">
                    <a href="login.php" class="btn-hero btn-hero-primary">
                        <i class="fas fa-sign-in-alt"></i> Start Now
                    </a>
                    <a href="#features" class="btn-hero btn-hero-outline">
                        <i class="fas fa-info-circle"></i> Learn More
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products-section">
        <div class="section-header">
            <h2>Featured Medicines</h2>
            <p>Browse our wide range of quality medicines and healthcare products</p>
        </div>
        
        <div class="products-grid">
            <?php if (!empty($featured_medicines)): ?>
                <?php foreach ($featured_medicines as $medicine): ?>
                    <?php
                    // Determine image path
                    $image_path = 'images/products/';
                    if (!empty($medicine['image']) && file_exists($image_path . $medicine['image'])) {
                        $image_src = $image_path . $medicine['image'];
                    } else {
                        // Use category-based default images
                        $category_images = [
                            'Diabetes' => 'injection.jpg',
                            'Cardiac' => 'tablet.jpg',
                            'Antibiotics' => 'tablet.jpg',
                            'Pain Relief' => 'cream.jpg',
                            'Generic Medicine' => 'default-medicine.jpg'
                        ];
                        $category = $medicine['category_name'] ?? 'Generic Medicine';
                        $default_image = $category_images[$category] ?? 'default-medicine.jpg';
                        $image_src = $image_path . $default_image;
                    }
                    ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo htmlspecialchars($image_src); ?>" 
                                 alt="<?php echo htmlspecialchars($medicine['name']); ?>"
                                 onerror="this.src='images/products/default-medicine.jpg'">
                            <?php if ($medicine['quantity'] > 0): ?>
                                <?php if ($medicine['quantity'] <= $medicine['alert_threshold']): ?>
                                    <span class="product-badge low-stock">Low Stock</span>
                                <?php else: ?>
                                    <span class="product-badge">In Stock</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <div class="product-category">
                                <?php echo htmlspecialchars($medicine['category_name'] ?? 'General'); ?>
                            </div>
                            <div class="product-name">
                                <?php echo htmlspecialchars($medicine['name']); ?>
                            </div>
                            <div class="product-details">
                                <div class="product-price">
                                    Rs. <?php echo number_format($medicine['price'], 2); ?>
                                </div>
                                <div class="product-stock">
                                    <i class="fas fa-boxes"></i>
                                    <?php echo $medicine['quantity']; ?> in stock
                                </div>
                            </div>
                            <div class="product-actions">
                                <?php if ($is_logged_in): ?>
                                    <div style="display: flex; gap: 10px; width: 100%;">
                                        <form method="POST" action="add-to-cart.php" style="flex: 1;" class="add-to-cart-form">
                                            <input type="hidden" name="medicine_id" value="<?php echo $medicine['id']; ?>">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="btn-add-cart" style="width: 100%;">
                                                <i class="fas fa-shopping-cart"></i> Add to Cart
                                            </button>
                                        </form>
                                        <form method="POST" action="buy-now.php" style="flex: 1;">
                                            <input type="hidden" name="medicine_id" value="<?php echo $medicine['id']; ?>">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="btn-buy-now" style="width: 100%;">
                                                <i class="fas fa-bolt"></i> Buy Now
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <a href="login.php" class="btn-add-cart">
                                        <i class="fas fa-shopping-cart"></i> Add to Cart
                                    </a>
                                    <a href="login.php" class="btn-buy-now">
                                        <i class="fas fa-bolt"></i> Buy Now
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #6e84a3;">
                    <i class="fas fa-box-open" style="font-size: 64px; margin-bottom: 20px; opacity: 0.3;"></i>
                    <p>No products available at the moment</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="view-all-btn">
            <a href="login.php" class="btn-hero btn-hero-primary">
                <i class="fas fa-shopping-cart"></i> View All Products
            </a>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-section">
        <div class="section-header">
            <h2>Powerful Features for Your Pharmacy</h2>
            <p>Everything you need to manage your pharmacy efficiently</p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-cash-register"></i>
                </div>
                <h3>Point of Sale</h3>
                <p>Fast and efficient POS system with barcode scanning, multiple payment methods, and instant invoice generation.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-pills"></i>
                </div>
                <h3>Inventory Management</h3>
                <p>Track medicine stock levels, expiry dates, and get automatic low stock alerts to never run out of essential medicines.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <h3>Supplier Management</h3>
                <p>Maintain complete supplier database with contact information and track all purchases from different suppliers.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Sales Reports</h3>
                <p>Generate comprehensive sales reports and analytics to track your pharmacy's performance and revenue.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <h3>Stock Tracking</h3>
                <p>Complete audit trail of all stock movements with automatic updates on every sale and purchase.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-users-cog"></i>
                </div>
                <h3>User Management</h3>
                <p>Role-based access control for administrators and cashiers with secure authentication.</p>
            </div>
        </div>
    </section>

  

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="stats-grid-landing">
            <div class="stat-item">
                <div class="stat-number"><i class="fas fa-check-circle"></i></div>
                <div class="stat-label">Complete Solution</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><i class="fas fa-shield-alt"></i></div>
                <div class="stat-label">Secure & Reliable</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><i class="fas fa-mobile-alt"></i></div>
                <div class="stat-label">Responsive Design</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><i class="fas fa-headset"></i></div>
                <div class="stat-label">Easy to Use</div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <h2>Ready to Transform Your Pharmacy?</h2>
        <p>Start managing your pharmacy more efficiently today</p>
        <a href="login.php" class="btn-hero btn-hero-primary">
            <i class="fas fa-sign-in-alt"></i> Login to Dashboard
        </a>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <p><strong>Medi Zone</strong> - Pharmacy Management System</p>
        <p>© 2025 All Rights Reserved</p>
        <p style="margin-top: 20px; font-size: 14px;">
            <i class="fas fa-user-shield"></i> Demo Credentials: admin@pharmacy.com / admin123
        </p>
    </footer>

    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Handle add to cart with AJAX to stay on same position
        document.querySelectorAll('.add-to-cart-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const button = this.querySelector('.btn-add-cart');
                const originalText = button.innerHTML;
                
                // Show loading state
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
                button.disabled = true;
                
                fetch('add-to-cart.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    // Show success state
                    button.innerHTML = '<i class="fas fa-check"></i> Added!';
                    button.style.background = '#48bb78';
                    button.style.borderColor = '#48bb78';
                    button.style.color = 'white';
                    
                    // Update cart count
                    location.reload();
                    
                    // Reset button after 2 seconds
                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.style.background = '';
                        button.style.borderColor = '';
                        button.style.color = '';
                        button.disabled = false;
                    }, 2000);
                })
                .catch(error => {
                    console.error('Error:', error);
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
            });
        });
    </script>
</body>
</html>