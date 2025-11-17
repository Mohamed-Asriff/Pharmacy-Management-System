<?php
require_once '../config.php';
require_once '../db.php';
requireAuth();

$page_title = "Update Medicine";
$page_description = "Update medicine information";

// Check if medicine ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: medicines.php');
    exit;
}

$medicine_id = $_GET['id'];

// Fetch categories and suppliers for dropdowns
try {
    $categories = $db->query("SELECT id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $suppliers = $db->query("SELECT id, name FROM suppliers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Fetch current medicine data
try {
    $stmt = $db->prepare("SELECT m.*, c.name as category_name, s.name as supplier_name 
                         FROM medicines m 
                         LEFT JOIN categories c ON m.category_id = c.id 
                         LEFT JOIN suppliers s ON m.supplier_id = s.id 
                         WHERE m.id = ?");
    $stmt->execute([$medicine_id]);
    $medicine = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$medicine) {
        $_SESSION['error'] = "Medicine not found.";
        header('Location: medicines.php');
        exit;
    }
} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $sku = trim($_POST['sku']);
    $category_id = $_POST['category_id'] ?: null;
    $supplier_id = $_POST['supplier_id'] ?: null;
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $cost_price = floatval($_POST['cost_price']);
    $quantity = intval($_POST['quantity']);
    $alert_threshold = intval($_POST['alert_threshold']);
    $expiry_date = $_POST['expiry_date'] ?: null;

    // Validate required fields
    if (empty($name) || empty($price) || $quantity < 0) {
        $error = "Please fill in all required fields correctly.";
    } else {
        try {
            // Check if SKU already exists (excluding current medicine)
            if (!empty($sku)) {
                $stmt = $db->prepare("SELECT id FROM medicines WHERE sku = ? AND id != ?");
                $stmt->execute([$sku, $medicine_id]);
                if ($stmt->rowCount() > 0) {
                    $error = "SKU already exists. Please use a different SKU.";
                }
            }

            if (!isset($error)) {
                $stmt = $db->prepare("UPDATE medicines 
                                     SET name = ?, sku = ?, category_id = ?, supplier_id = ?, 
                                         description = ?, price = ?, cost_price = ?, quantity = ?, 
                                         alert_threshold = ?, expiry_date = ?
                                     WHERE id = ?");
                $stmt->execute([$name, $sku, $category_id, $supplier_id, $description, 
                              $price, $cost_price, $quantity, $alert_threshold, $expiry_date, $medicine_id]);
                
                $_SESSION['success'] = "Medicine updated successfully!";
                header('Location: medicines.php');
                exit;
            }
        } catch(PDOException $e) {
            $error = "Database error: " . $e->getMessage();
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
                    <h2>Update Medicine</h2>
                    <a href="medicines.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Medicines
                    </a>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h3>Edit Medicine Information</h3>
                        <div class="medicine-badge">
                            <span class="status-badge <?php echo $medicine['quantity'] == 0 ? 'out-of-stock' : ($medicine['quantity'] <= $medicine['alert_threshold'] ? 'low-stock' : 'in-stock'); ?>">
                                <?php echo $medicine['quantity'] == 0 ? 'Out of Stock' : ($medicine['quantity'] <= $medicine['alert_threshold'] ? 'Low Stock' : 'In Stock'); ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="updateMedicineForm">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name">Medicine Name *</label>
                                    <input type="text" id="name" name="name" required 
                                           value="<?php echo htmlspecialchars($medicine['name']); ?>"
                                           placeholder="Enter medicine name">
                                </div>
                                
                                <div class="form-group">
                                    <label for="sku">SKU Code</label>
                                    <input type="text" id="sku" name="sku" 
                                           value="<?php echo htmlspecialchars($medicine['sku']); ?>"
                                           placeholder="Enter SKU code">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="category_id">Category</label>
                                    <select id="category_id" name="category_id">
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" 
                                                <?php echo ($medicine['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="supplier_id">Supplier</label>
                                    <select id="supplier_id" name="supplier_id">
                                        <option value="">Select Supplier</option>
                                        <?php foreach ($suppliers as $supplier): ?>
                                            <option value="<?php echo $supplier['id']; ?>"
                                                <?php echo ($medicine['supplier_id'] == $supplier['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($supplier['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" rows="3" 
                                          placeholder="Enter medicine description"><?php echo htmlspecialchars($medicine['description']); ?></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="price">Selling Price (Rs.) *</label>
                                    <input type="number" id="price" name="price" step="0.01" min="0" required 
                                           value="<?php echo $medicine['price']; ?>"
                                           placeholder="0.00">
                                </div>
                                
                                <div class="form-group">
                                    <label for="cost_price">Cost Price (Rs.)</label>
                                    <input type="number" id="cost_price" name="cost_price" step="0.01" min="0"
                                           value="<?php echo $medicine['cost_price']; ?>"
                                           placeholder="0.00">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="quantity">Current Quantity *</label>
                                    <input type="number" id="quantity" name="quantity" min="0" required 
                                           value="<?php echo $medicine['quantity']; ?>"
                                           placeholder="0">
                                    <small class="text-muted">Current stock: <?php echo $medicine['quantity']; ?> units</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="alert_threshold">Low Stock Alert Threshold</label>
                                    <input type="number" id="alert_threshold" name="alert_threshold" min="1" 
                                           value="<?php echo $medicine['alert_threshold']; ?>"
                                           placeholder="10">
                                    <small class="text-muted">Get alerted when stock falls below this number</small>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="expiry_date">Expiry Date</label>
                                <input type="date" id="expiry_date" name="expiry_date"
                                       value="<?php echo $medicine['expiry_date']; ?>">
                            </div>

                            <!-- Stock Information -->
                            <div class="form-section">
                                <h4>Stock Information</h4>
                                <div class="stock-info-grid">
                                    <div class="stock-info-item">
                                        <span class="stock-label">Current Stock Value:</span>
                                        <span class="stock-value">Rs. <?php echo number_format($medicine['quantity'] * $medicine['cost_price'], 2); ?></span>
                                    </div>
                                    <div class="stock-info-item">
                                        <span class="stock-label">Potential Revenue:</span>
                                        <span class="stock-value">Rs. <?php echo number_format($medicine['quantity'] * $medicine['price'], 2); ?></span>
                                    </div>
                                    <div class="stock-info-item">
                                        <span class="stock-label">Profit Margin:</span>
                                        <span class="stock-value <?php echo (($medicine['price'] - $medicine['cost_price']) / $medicine['cost_price'] * 100) >= 0 ? 'profit' : 'loss'; ?>">
                                            <?php 
                                            if ($medicine['cost_price'] > 0) {
                                                $margin = (($medicine['price'] - $medicine['cost_price']) / $medicine['cost_price']) * 100;
                                                echo number_format($margin, 2) . '%';
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Medicine
                                </button>
                                <button type="reset" class="btn btn-secondary">Reset Changes</button>
                                <a href="medicines.php" class="btn btn-outline">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('updateMedicineForm');
            const priceInput = document.getElementById('price');
            const costPriceInput = document.getElementById('cost_price');
            
            // Calculate and update profit margin in real-time
            function updateProfitMargin() {
                const price = parseFloat(priceInput.value) || 0;
                const costPrice = parseFloat(costPriceInput.value) || 0;
                
                if (costPrice > 0) {
                    const margin = ((price - costPrice) / costPrice) * 100;
                    document.querySelector('.stock-value.profit, .stock-value.loss').textContent = margin.toFixed(2) + '%';
                    
                    // Update class based on profit/loss
                    const marginElement = document.querySelector('.stock-value.profit, .stock-value.loss');
                    marginElement.className = margin >= 0 ? 'stock-value profit' : 'stock-value loss';
                }
            }
            
            priceInput.addEventListener('input', updateProfitMargin);
            costPriceInput.addEventListener('input', updateProfitMargin);
            
            // Form validation
            form.addEventListener('submit', function(e) {
                const price = parseFloat(priceInput.value);
                const costPrice = parseFloat(costPriceInput.value);
                const quantity = parseInt(document.getElementById('quantity').value);
                
                let errors = [];
                
                if (price <= 0) {
                    errors.push('Selling price must be greater than 0');
                }
                
                if (costPrice < 0) {
                    errors.push('Cost price cannot be negative');
                }
                
                if (quantity < 0) {
                    errors.push('Quantity cannot be negative');
                }
                
                if (costPrice > price) {
                    errors.push('Cost price cannot be higher than selling price');
                }
                
                if (errors.length > 0) {
                    e.preventDefault();
                    alert('Please fix the following errors:\n' + errors.join('\n'));
                }
            });
            
            // Auto-format currency inputs
            [priceInput, costPriceInput].forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.value) {
                        this.value = parseFloat(this.value).toFixed(2);
                    }
                });
            });
        });
    </script>

    <style>
        .medicine-badge {
            display: flex;
            align-items: center;
        }
        
        .form-section {
            margin: 30px 0;
            padding: 20px;
            background: var(--light);
            border-radius: 8px;
            border-left: 4px solid var(--primary);
        }
        
        .form-section h4 {
            margin-bottom: 15px;
            color: var(--dark);
            font-size: 16px;
        }
        
        .stock-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .stock-info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background: white;
            border-radius: 6px;
            border: 1px solid var(--border);
        }
        
        .stock-label {
            font-weight: 500;
            color: var(--secondary);
            font-size: 14px;
        }
        
        .stock-value {
            font-weight: 600;
            color: var(--dark);
        }
        
        .stock-value.profit {
            color: var(--success);
        }
        
        .stock-value.loss {
            color: var(--danger);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--secondary);
            color: var(--secondary);
        }
        
        .btn-outline:hover {
            background: var(--secondary);
            color: white;
        }
        
        .text-muted {
            color: var(--secondary);
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }
        
        @media (max-width: 768px) {
            .stock-info-grid {
                grid-template-columns: 1fr;
            }
            
            .stock-info-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }
    </style>
</body>
</html>