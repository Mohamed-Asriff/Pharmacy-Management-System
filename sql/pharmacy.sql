-- Pharmacy Management System Database
CREATE DATABASE IF NOT EXISTS pharmacy_management;
USE pharmacy_management;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'cashier') DEFAULT 'cashier',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Suppliers table
CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Medicines table
CREATE TABLE medicines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category_id INT,
    supplier_id INT,
    sku VARCHAR(50) UNIQUE,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    cost_price DECIMAL(10,2),
    quantity INT DEFAULT 0,
    alert_threshold INT DEFAULT 10,
    expiry_date DATE,
    image VARCHAR(255) DEFAULT 'default-medicine.jpg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
);

-- Sales table
CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_no VARCHAR(50) UNIQUE NOT NULL,
    user_id INT,
    total_amount DECIMAL(10,2) NOT NULL,
    paid_amount DECIMAL(10,2) NOT NULL,
    change_amount DECIMAL(10,2) DEFAULT 0,
    payment_method ENUM('cash', 'card', 'upi') DEFAULT 'cash',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Sale items table
CREATE TABLE sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    medicine_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE RESTRICT
);

-- Stock movements table
CREATE TABLE stock_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medicine_id INT NOT NULL,
    quantity_change INT NOT NULL,
    type ENUM('in', 'out') NOT NULL,
    reference_type ENUM('purchase', 'sale', 'adjustment') NOT NULL,
    reference_id INT,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE
);

-- Customers table for online orders
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    postal_code VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Online orders table
CREATE TABLE online_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_no VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash_on_delivery', 'card', 'upi') DEFAULT 'cash_on_delivery',
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    order_status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    delivery_address TEXT NOT NULL,
    delivery_city VARCHAR(100),
    delivery_postal_code VARCHAR(20),
    delivery_phone VARCHAR(20),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- Online order items table
CREATE TABLE online_order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    medicine_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES online_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE RESTRICT
);

-- Insert default admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES 
('Admin User', 'admin@pharmacy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Cashier User', 'cashier@pharmacy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cashier');

-- Insert sample categories
INSERT INTO categories (name, description) VALUES 
('Generic Medicine', 'Common generic medicines'),
('Diabetes', 'Medicines for diabetes treatment'),
('Cardiac', 'Heart and blood pressure medicines'),
('Antibiotics', 'Anti-bacterial medications'),
('Pain Relief', 'Pain killers and analgesics');

-- Insert sample suppliers
INSERT INTO suppliers (name, email, phone, address) VALUES 
('Medi Supplies Ltd', 'contact@medisupplies.com', '+94112345678', '123 Medical Street, Colombo'),
('Pharma Distributors', 'info@pharmadist.com', '+94119876543', '456 Health Avenue, Kandy');

-- Insert sample medicines
INSERT INTO medicines (name, category_id, supplier_id, sku, price, cost_price, quantity, alert_threshold,image) VALUES
('Cetrimide Cream 15g', 1, 1, 'CETCR15', 35.00, 22.00, 120, 15, 'Cetrimide Cream 15g.jpg'),
('Domperidone 10mg', 1, 1, 'DOM010', 18.00, 12.00, 140, 20, 'Domperidone 10mg.jpg'),
('Omeprazole 20mg', 1, 2, 'OME020', 22.00, 15.00, 160, 15, 'Omeprazole 20mg.jpg'),
('Erythromycin 250mg', 1, 2, 'ERY250', 28.00, 20.00, 110, 20, 'Erythromycin 250mg.jpg'),
('Ciprofloxacin 500mg', 1, 1, 'CIP500', 32.00, 24.00, 95, 20, 'Ciprofloxacin 500mg.jpg'),
('Doxycycline 100mg', 1, 2, 'DOX100', 40.00, 28.00, 130, 15, 'Doxycycline 100mg.jpg'),
('Clarithromycin 250mg', 1, 1, 'CLA250', 45.00, 32.00, 85, 20, 'Clarithromycin 250mg.jpg'),
('Linezolid 600mg', 1, 1, 'LIN600', 180.00, 150.00, 45, 10, 'Linezolid 600mg.jpg'),
('Cefixime 200mg', 1, 2, 'CEF200', 55.00, 40.00, 90, 15, 'Cefixime 200mg.jpg'),
('Cefuroxime 500mg', 1, 2, 'CEF500', 75.00, 55.00, 70, 10, 'Cefuroxime 500mg.jpg'),

('Januvia 50mg', 2, 1, 'JAN050', 195.00, 160.00, 60, 10, 'Januvia 50mg.jpg'),
('Glimepiride 2mg', 2, 2, 'GLI002', 25.00, 18.00, 150, 20, 'Glimepiride 2mg.jpg'),
('Gliclazide 80mg', 2, 1, 'GLI080', 20.00, 14.00, 200, 25, 'Gliclazide 80mg.jpg'),
('Voglibose 0.2mg', 2, 2, 'VOG002', 30.00, 22.00, 130, 15, 'Voglibose 0.2mg.jpg'),
('Insulin 30.70 10ml', 2, 1, 'INS3070', 650.00, 580.00, 40, 10, 'Insulin 300 10ml.jpg'),
('Insulin Glargine 100IU', 2, 2, 'INSG100', 980.00, 890.00, 25, 10, 'Insulin Glargine 100IU.jpg'),
('Sitagliptin 100mg', 2, 1, 'SIT100', 210.00, 180.00, 50, 10, 'Sitagliptin 100mg.jpg'),
('Pioglitazone 15mg', 2, 2, 'PIO015', 35.00, 25.00, 160, 20, 'Pioglitazone 15mg.jpg'),
('Dapagliflozin 10mg', 2, 1, 'DAP010', 160.00, 120.00, 70, 10, 'Dapagliflozin 10mg.jpg'),
('Empagliflozin 25mg', 2, 2, 'EMP025', 170.00, 130.00, 65, 10, 'Empagliflozin 25mg.jpg'),

('Losartan 50mg', 3, 2, 'LOS050', 30.00, 20.00, 200, 20, 'Losartan 50mg.jpg'),
('Enalapril 5mg', 3, 1, 'ENA005', 18.00, 12.00, 180, 20, 'Enalapril 5mg.jpg'),
('Ramipril 5mg', 3, 1, 'RAM005', 25.00, 18.00, 150, 20, 'Ramipril 5mg.jpg'),
('Atenolol 50mg', 3, 2, 'ATE050', 28.00, 20.00, 170, 20, 'Atenolol 50mg.jpg'),
('Bisoprolol 2.5mg', 3, 1, 'BIS025', 35.00, 25.00, 140, 15, 'Bisoprolol 2.5mg.jpg'),
('Telmisartan 40mg', 3, 2, 'TEL040', 42.00, 30.00, 120, 20, 'Telmisartan 40mg.jpg'),
('Furosemide 40mg', 3, 1, 'FUR040', 20.00, 12.00, 190, 25, 'Furosemide 40mg.jpg'),
('Spironolactone 25mg', 3, 2, 'SPI025', 38.00, 26.00, 130, 20, 'Spironolactone 25mg.jpg'),
('Clopidogrel 75mg', 3, 1, 'CLO075', 70.00, 55.00, 80, 10, 'Clopidogrel 75mg.jpg'),
('Atorvastatin 20mg', 3, 2, 'ATO020', 55.00, 40.00, 115, 15, 'Atorvastatin 20mg.jpg'),

('Ibuprofen 200mg', 5, 2, 'IBU200', 10.00, 7.00, 300, 40, 'Ibuprofen 200mg.jpg'),
('Diclofenac 50mg', 5, 1, 'DIC050', 8.00, 5.00, 260, 30, 'Diclofenac 50mg.jpg'),
('Naproxen 250mg', 5, 2, 'NAP250', 18.00, 12.00, 190, 25, 'Naproxen 250mg.jpg'),
('Aceclofenac 100mg', 5, 1, 'ACE100', 22.00, 16.00, 150, 20, 'Aceclofenac 100mg.jpg'),
('Ketorolac 10mg', 5, 2, 'KET010', 30.00, 22.00, 110, 15, 'Ketorolac 10mg.jpg'),
('Tramadol 50mg', 5, 1, 'TRA050', 45.00, 32.00, 85, 15, 'Tramadol 50mg.jpg'),
('Piroxicam 20mg', 5, 2, 'PIR020', 25.00, 18.00, 160, 20, 'Piroxicam 20mg.jpg'),
('Mefenamic Acid 500mg', 5, 1, 'MEF500', 20.00, 14.00, 180, 25, 'Mefenamic Acid 500mg.jpg'),
('Etoricoxib 90mg', 5, 2, 'ETO090', 60.00, 45.00, 95, 15, 'Etoricoxib 90mg.jpg'),
('Celecoxib 200mg', 5, 1, 'CEL200', 55.00, 40.00, 120, 20, 'Celecoxib 200mg.jpg'),

('Amoxiclav 625mg', 4, 2, 'AMO625', 50.00, 40.00, 120, 20, 'Amoxiclav 625mg.jpg'),
('Ceftriaxone 1g', 4, 1, 'CEF1G', 150.00, 120.00, 60, 10, 'Ceftriaxone 1g.jpg'),
('Aztreonam 1g', 4, 2, 'AZT001', 180.00, 150.00, 40, 10, 'Aztreonam 1g.jpg'),
('Meropenem 1g', 4, 1, 'MER001', 350.00, 290.00, 25, 10, 'Meropenem 1g.jpg'),
('Imipenem 500mg', 4, 1, 'IMI500', 310.00, 250.00, 35, 10, 'Imipenem 500mg.jpg'),
('Tetracycline 250mg', 4, 2, 'TET250', 25.00, 18.00, 160, 20, 'Tetracycline 250mg.jpg'),
('Minocycline 100mg', 4, 1, 'MIN100', 48.00, 35.00, 90, 15, 'Minocycline 100mg.jpg'),
('Paracetamol 500mg', 1, 1, 'PAR500', 12.00, 8.00, 250, 30, 'Paracetamol 500mg.jpg'),
('Cetirizine 10mg', 1, 2, 'CET010', 15.00, 10.00, 220, 25, 'Cetirizine 10mg.jpg'),
('Ranitidine 150mg', 1, 1, 'RAN150', 20.00, 14.00, 180, 20, 'Ranitidine 150mg.jpg'),
('Salbutamol Inhaler', 1, 2, 'SALINH', 300.00, 250.00, 80, 10, 'Salbutamol Inhaler.jpg'),
('Loratadine 10mg', 1, 1, 'LOR010', 18.00, 12.00, 200, 20, 'Loratadine 10mg.jpg');
