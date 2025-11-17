<?php
require_once '../config.php';
require_once '../db.php';
requireAuth();

$page_title = "POS System";
$page_description = "Point of Sale - Billing System";

try {
    $medicines = $db->query("SELECT id, name, sku, price, quantity FROM medicines WHERE quantity > 0 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
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
                <div class="page-actions">
                    <h2>Point of Sale</h2>
                    <div class="pos-actions">
                        <button class="btn btn-success" onclick="completeSale()">
                            <i class="fas fa-check-circle"></i> Complete Sale
                        </button>
                        <button class="btn btn-danger" onclick="clearCart()">
                            <i class="fas fa-trash"></i> Clear All
                        </button>
                    </div>
                </div>

                <div class="pos-layout">
                    
                    <div class="pos-left">
                        
                        <div class="card pos-card">
                            <div class="card-header">
                                <h3><i class="fas fa-barcode"></i> Quick Scan</h3>
                            </div>
                            <div class="card-body">
                                <div class="scan-section">
                                    <div class="barcode-input-group">
                                        <input type="text" 
                                               id="barcodeInput" 
                                               placeholder="Scan barcode or enter product code..." 
                                               autocomplete="off"
                                               autofocus>
                                        <button class="btn btn-primary" onclick="scanProduct()">
                                            <i class="fas fa-search"></i> Scan
                                        </button>
                                    </div>
                                    <div id="barcodeFeedback" class="barcode-feedback"></div>
                                </div>
                            </div>
                        </div>

                        
                        <div class="card pos-card">
                            <div class="card-header">
                                <h3><i class="fas fa-search"></i> Search Products</h3>
                            </div>
                            <div class="card-body">
                                <div class="search-section">
                                    <div class="search-box-full">
                                        <i class="fas fa-search"></i>
                                        <input type="text" id="productSearch" placeholder="Search medicines by name, SKU, or category...">
                                    </div>
                                    <div id="searchResults" class="search-results"></div>
                                </div>
                            </div>
                        </div>

                        
                        <div class="card pos-card">
                            <div class="card-header">
                                <h3><i class="fas fa-pills"></i> Available Products</h3>
                                <span class="product-count"><?php echo count($medicines); ?> products</span>
                            </div>
                            <div class="card-body">
                                <div class="products-grid" id="productsGrid">
                                    <?php foreach ($medicines as $medicine): ?>
                                        <div class="product-card" data-product-id="<?php echo $medicine['id']; ?>">
                                            <div class="product-info">
                                                <h4><?php echo htmlspecialchars($medicine['name']); ?></h4>
                                                <p class="product-sku">SKU: <?php echo htmlspecialchars($medicine['sku']); ?></p>
                                                <p class="product-stock">Stock: <?php echo $medicine['quantity']; ?></p>
                                            </div>
                                            <div class="product-price">
                                                <span class="price">Rs. <?php echo number_format($medicine['price'], 2); ?></span>
                                                <button class="btn btn-sm btn-primary add-to-cart" 
                                                        onclick="addToCart(<?php echo $medicine['id']; ?>)">
                                                    <i class="fas fa-plus"></i> Add
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="pos-right">
                        
                        <div class="card pos-card sale-card">
                            <div class="card-header">
                                <h3><i class="fas fa-shopping-cart"></i> Current Sale</h3>
                                <div class="cart-summary">
                                    <span class="item-count">0 items</span>
                                    <span class="total-amount">Rs. 0.00</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="sale-table-container">
                                    <table class="sale-table" id="saleTable">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Price</th>
                                                <th>Qty</th>
                                                <th>Subtotal</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="saleTableBody">
                                            
                                        </tbody>
                                    </table>
                                    <div id="emptyCart" class="empty-cart">
                                        <i class="fas fa-shopping-cart"></i>
                                        <p>Your cart is empty</p>
                                        <small>Add products to start a sale</small>
                                    </div>
                                </div>
                                
                                <div class="sale-totals">
                                    <div class="total-row">
                                        <span>Subtotal:</span>
                                        <span id="subtotalAmount">Rs. 0.00</span>
                                    </div>
                                    <div class="total-row">
                                        <span>Tax (0%):</span>
                                        <span id="taxAmount">Rs. 0.00</span>
                                    </div>
                                    <div class="total-row grand-total">
                                        <span><strong>Total:</strong></span>
                                        <span id="totalAmount"><strong>Rs. 0.00</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                        <div class="card pos-card payment-card">
                            <div class="card-header">
                                <h3><i class="fas fa-credit-card"></i> Payment</h3>
                            </div>
                            <div class="card-body">
                                <div class="payment-section">
                                    <div class="payment-methods">
                                        <div class="payment-method-group">
                                            <label>Payment Method</label>
                                            <div class="method-buttons">
                                                <button class="method-btn active" data-method="cash">
                                                    <i class="fas fa-money-bill-wave"></i>
                                                    <span>Cash</span>
                                                </button>
                                                <button class="method-btn" data-method="card">
                                                    <i class="fas fa-credit-card"></i>
                                                    <span>Card</span>
                                                </button>
                                                <button class="method-btn" data-method="upi">
                                                    <i class="fas fa-mobile-alt"></i>
                                                    <span>UPI</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="payment-inputs">
                                        <div class="form-group">
                                            <label for="paidAmount">Amount Paid (Rs.)</label>
                                            <div class="amount-input-group">
                                                <span class="currency-symbol">Rs.</span>
                                                <input type="number" id="paidAmount" step="0.01" min="0" placeholder="0.00">
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="changeAmount">Change Due (Rs.)</label>
                                            <div class="change-display">
                                                <span id="changeAmount">0.00</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="quick-amounts">
                                        <label>Quick Amounts:</label>
                                        <div class="amount-buttons">
                                            <button class="amount-btn" data-amount="100">100</button>
                                            <button class="amount-btn" data-amount="500">500</button>
                                            <button class="amount-btn" data-amount="1000">1000</button>
                                            <button class="amount-btn" onclick="setExactAmount()">Exact</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/pos.js"></script>
    <script>
        
        document.addEventListener('DOMContentLoaded', function() {
            initializePOS();
        });
    </script>

    <style>
        .pos-layout {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 25px;
            height: calc(100vh - 200px);
        }

        .pos-left {
            display: flex;
            flex-direction: column;
            gap: 20px;
            overflow-y: auto;
        }

        .pos-right {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .pos-card {
            margin-bottom: 0;
        }

        .sale-card {
            flex: 1;
        }

        .payment-card {
            flex-shrink: 0;
        }

        .pos-actions {
            display: flex;
            gap: 10px;
        }

        
        .barcode-input-group {
            display: flex;
            gap: 10px;
        }

        .barcode-input-group input {
            flex: 1;
        }

        .barcode-feedback {
            margin-top: 10px;
            font-size: 12px;
            min-height: 20px;
        }

        
        .search-box-full {
            position: relative;
        }

        .search-box-full i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary);
        }

        .search-box-full input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
        }

        .search-results {
            margin-top: 10px;
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: white;
        }

        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            max-height: 400px;
            overflow-y: auto;
        }

        .product-card {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 15px;
            background: white;
            transition: all 0.3s ease;
        }

        .product-card:hover {
            border-color: var(--primary);
            box-shadow: 0 2px 8px rgba(44, 123, 229, 0.1);
        }

        .product-info h4 {
            font-size: 14px;
            margin-bottom: 5px;
            color: var(--dark);
        }

        .product-sku, .product-stock {
            font-size: 12px;
            color: var(--secondary);
            margin: 2px 0;
        }

        .product-price {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }

        .price {
            font-weight: 600;
            color: var(--primary);
        }

        .product-count {
            background: var(--primary);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        
        .sale-table-container {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 20px;
        }

        .sale-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .sale-table th {
            background: var(--light);
            padding: 10px;
            text-align: left;
            font-weight: 600;
            color: var(--dark);
            border-bottom: 2px solid var(--border);
        }

        .sale-table td {
            padding: 10px;
            border-bottom: 1px solid var(--border);
        }

        .sale-table tr:last-child td {
            border-bottom: none;
        }

        .empty-cart {
            text-align: center;
            padding: 40px 20px;
            color: var(--secondary);
        }

        .empty-cart i {
            font-size: 48px;
            margin-bottom: 15px;
            color: var(--border);
        }

        .empty-cart p {
            margin-bottom: 5px;
            font-size: 16px;
        }

        .empty-cart small {
            font-size: 12px;
        }

        .cart-summary {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .item-count, .total-amount {
            font-size: 14px;
            font-weight: 500;
        }

        .total-amount {
            color: var(--primary);
        }

        
        .sale-totals {
            border-top: 2px solid var(--border);
            padding-top: 15px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .grand-total {
            border-top: 1px solid var(--border);
            padding-top: 8px;
            margin-top: 8px;
            font-size: 16px;
        }

        
        .payment-methods {
            margin-bottom: 20px;
        }

        .method-buttons {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 10px;
        }

        .method-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 12px;
            border: 2px solid var(--border);
            border-radius: 8px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .method-btn.active {
            border-color: var(--primary);
            background: rgba(44, 123, 229, 0.1);
        }

        .method-btn i {
            font-size: 20px;
            margin-bottom: 5px;
            color: var(--secondary);
        }

        .method-btn.active i {
            color: var(--primary);
        }

        .method-btn span {
            font-size: 12px;
            font-weight: 500;
        }

        .amount-input-group {
            position: relative;
        }

        .currency-symbol {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-weight: 600;
            color: var(--secondary);
        }

        .amount-input-group input {
            padding-left: 40px;
        }

        .change-display {
            background: var(--light);
            padding: 12px 15px;
            border-radius: 8px;
            font-weight: 600;
            color: var(--primary);
        }

        .quick-amounts {
            margin-top: 20px;
        }

        .amount-buttons {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 10px;
        }

        .amount-btn {
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .amount-btn:hover {
            border-color: var(--primary);
            background: rgba(44, 123, 229, 0.1);
        }

        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .qty-btn {
            width: 25px;
            height: 25px;
            border: 1px solid var(--border);
            border-radius: 4px;
            background: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qty-btn:hover {
            background: var(--light);
        }

        .qty-input {
            width: 40px;
            text-align: center;
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 4px;
        }

        
        @media (max-width: 1024px) {
            .pos-layout {
                grid-template-columns: 1fr;
                height: auto;
            }
            
            .pos-right {
                max-width: 100%;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .pos-actions {
                flex-direction: column;
            }
            
            .method-buttons {
                grid-template-columns: 1fr;
            }
            
            .amount-buttons {
                grid-template-columns: repeat(4, 1fr);
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>