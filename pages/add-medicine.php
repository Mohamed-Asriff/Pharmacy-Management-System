
<?php
require_once '../config.php';
require_once '../db.php';
requireAuth();

$page_title = "Add New Medicine";
$page_description = "Add a new medicine to inventory";


try {
    $categories = $db->query("SELECT id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $suppliers = $db->query("SELECT id, name FROM suppliers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}


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
    $image = 'default-medicine.svg'; // Default image

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
        $filename = $_FILES['image']['name'];
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($filetype, $allowed)) {
            $new_filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
            $upload_path = '../images/products/' . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image = $new_filename;
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Invalid file type. Allowed types: jpg, jpeg, png, gif, svg, webp";
        }
    }

   
    if (empty($name) || empty($price) || $quantity < 0) {
        $error = "Please fill in all required fields correctly.";
    } else {
        try {
            
            if (!empty($sku)) {
                $stmt = $db->prepare("SELECT id FROM medicines WHERE sku = ?");
                $stmt->execute([$sku]);
                if ($stmt->rowCount() > 0) {
                    $error = "SKU already exists. Please use a different SKU.";
                }
            }

            if (!isset($error)) {
                $stmt = $db->prepare("INSERT INTO medicines (name, sku, category_id, supplier_id, description, price, cost_price, quantity, alert_threshold, expiry_date, image) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $sku, $category_id, $supplier_id, $description, $price, $cost_price, $quantity, $alert_threshold, $expiry_date, $image]);
                
                $_SESSION['success'] = "Medicine added successfully!";
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
                    <h2>Add New Medicine</h2>
                    <a href="medicines.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Medicines
                    </a>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" id="medicineForm" enctype="multipart/form-data">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name">Medicine Name *</label>
                                    <input type="text" id="name" name="name" required 
                                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="sku">SKU Code</label>
                                    <input type="text" id="sku" name="sku" 
                                           value="<?php echo isset($_POST['sku']) ? htmlspecialchars($_POST['sku']) : ''; ?>">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="category_id">Category</label>
                                    <select id="category_id" name="category_id">
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" 
                                                <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
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
                                                <?php echo (isset($_POST['supplier_id']) && $_POST['supplier_id'] == $supplier['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($supplier['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" rows="3"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="price">Selling Price (Rs.) *</label>
                                    <input type="number" id="price" name="price" step="0.01" min="0" required 
                                           value="<?php echo isset($_POST['price']) ? $_POST['price'] : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="cost_price">Cost Price (Rs.)</label>
                                    <input type="number" id="cost_price" name="cost_price" step="0.01" min="0"
                                           value="<?php echo isset($_POST['cost_price']) ? $_POST['cost_price'] : ''; ?>">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="quantity">Initial Quantity *</label>
                                    <input type="number" id="quantity" name="quantity" min="0" required 
                                           value="<?php echo isset($_POST['quantity']) ? $_POST['quantity'] : '0'; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="alert_threshold">Low Stock Alert Threshold</label>
                                    <input type="number" id="alert_threshold" name="alert_threshold" min="1" 
                                           value="<?php echo isset($_POST['alert_threshold']) ? $_POST['alert_threshold'] : '10'; ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="expiry_date">Expiry Date</label>
                                <input type="date" id="expiry_date" name="expiry_date"
                                       value="<?php echo isset($_POST['expiry_date']) ? $_POST['expiry_date'] : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="image">Product Image</label>
                                <input type="file" id="image" name="image" accept="image/*">
                                <small style="color: #6e84a3; display: block; margin-top: 5px;">
                                    <i class="fas fa-info-circle"></i> Supported formats: JPG, PNG, GIF, SVG, WEBP (Max 5MB)
                                </small>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Add Medicine
                                </button>
                                <button type="reset" class="btn btn-secondary">Reset</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/script.js"></script>
</body>
</html>
