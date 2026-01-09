-- Database: tk_construction_office
CREATE DATABASE IF NOT EXISTS tk_construction_office;
USE tk_construction_office;

-- Users table with plain text password (as per requirement)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, -- Stored as plain text
    role ENUM('admin', 'staff') DEFAULT 'staff',
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_code VARCHAR(50) UNIQUE NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    category ENUM('Flooring', 'Waterproofing', 'Chemicals', 'Hardware') NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    quantity INT NOT NULL DEFAULT 0,
    min_stock_level INT DEFAULT 10,
    unit VARCHAR(20) DEFAULT 'Each',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Customers table
CREATE TABLE customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_name VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    email VARCHAR(100),
    address TEXT,
    customer_type ENUM('Retail', 'Contractor', 'Corporate') DEFAULT 'Retail',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sales table
CREATE TABLE sales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_no VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT,
    customer_name VARCHAR(255) NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    discount DECIMAL(15,2) DEFAULT 0.00,
    net_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    advance_paid DECIMAL(15,2) DEFAULT 0.00,
    balance DECIMAL(15,2) DEFAULT 0.00,
    payment_method ENUM('Cash', 'Bank Transfer', 'Cheque', 'Credit') NOT NULL,
    cheque_no VARCHAR(50),
    bank_name VARCHAR(100),
    status ENUM('Pending', 'Completed', 'Cancelled') DEFAULT 'Pending',
    sold_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (sold_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Sale items table
CREATE TABLE sale_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    quantity INT NOT NULL,
    total DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Projects table
CREATE TABLE projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_code VARCHAR(50) UNIQUE NOT NULL,
    client_name VARCHAR(255) NOT NULL,
    location VARCHAR(500) NOT NULL,
    contact_person VARCHAR(255),
    phone VARCHAR(15),
    email VARCHAR(100),
    total_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    advance_received DECIMAL(15,2) DEFAULT 0.00,
    balance DECIMAL(15,2) DEFAULT 0.00,
    status ENUM('Ongoing', 'Completed', 'Hold') DEFAULT 'Ongoing',
    start_date DATE,
    estimated_end_date DATE,
    actual_end_date DATE,
    description TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Project payments table
CREATE TABLE project_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    payment_date DATE NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    payment_method ENUM('Cash', 'Bank Transfer', 'Cheque') NOT NULL,
    cheque_no VARCHAR(50),
    bank_name VARCHAR(100),
    description VARCHAR(500),
    received_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Labour management table
CREATE TABLE labour (
    id INT PRIMARY KEY AUTO_INCREMENT,
    labour_name VARCHAR(255) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    nic VARCHAR(20),
    address TEXT,
    designation VARCHAR(100),
    daily_rate DECIMAL(15,2) DEFAULT 0.00,
    advance_taken DECIMAL(15,2) DEFAULT 0.00,
    balance DECIMAL(15,2) DEFAULT 0.00,
    remarks TEXT,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cheques table
CREATE TABLE cheques (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cheque_no VARCHAR(100) UNIQUE NOT NULL,
    bank_name VARCHAR(255) NOT NULL,
    branch VARCHAR(255),
    amount DECIMAL(15,2) NOT NULL,
    cheque_date DATE NOT NULL,
    received_date DATE NOT NULL,
    payment_type ENUM('Sales', 'Project') NOT NULL,
    reference_id INT NOT NULL, -- sale_id or project_id
    payer_name VARCHAR(255) NOT NULL,
    status ENUM('Pending', 'Cleared', 'Returned', 'Cancelled') DEFAULT 'Pending',
    clearance_date DATE,
    return_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inventory transactions table
CREATE TABLE inventory_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    transaction_type ENUM('Purchase', 'Sale', 'Adjustment', 'Return') NOT NULL,
    quantity_change INT NOT NULL,
    previous_quantity INT NOT NULL,
    new_quantity INT NOT NULL,
    reference_id INT, -- sale_id or purchase_id
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default admin user (plain text password as per requirement)
INSERT INTO users (username, email, password, role, full_name, phone) VALUES
('admin', 'admin@tkconstruction.lk', 'TKethi', 'admin', 'System Administrator', '0771234567');

-- Sample products data
INSERT INTO products (product_code, product_name, category, unit_price, quantity, unit) VALUES
('FL-001', 'Premium Floor Tiles 60x60', 'Flooring', 4500.00, 100, 'Box'),
('FL-002', 'Ceramic Floor Tiles 30x30', 'Flooring', 2500.00, 150, 'Box'),
('WP-001', 'Liquid Waterproofing 20L', 'Waterproofing', 12500.00, 50, 'Can'),
('WP-002', 'Bituminous Coating', 'Waterproofing', 8500.00, 75, 'Drum'),
('CH-001', 'Construction Adhesive 5L', 'Chemicals', 3500.00, 200, 'Bottle'),
('CH-002', 'Concrete Hardener 10kg', 'Chemicals', 4200.00, 120, 'Bag'),
('HW-001', 'Steel Trowel 12"', 'Hardware', 850.00, 300, 'Piece'),
('HW-002', 'Masonry Brush', 'Hardware', 450.00, 500, 'Piece');