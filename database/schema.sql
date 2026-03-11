-- Ontomeel Bookshop Database Schema
-- Last Updated: 2026-03-09

-- Create Database
CREATE DATABASE IF NOT EXISTS ontomeel_bookshop DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ontomeel_bookshop;

-- 1. Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Books Table
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- General Info
    title VARCHAR(255) NOT NULL,
    subtitle VARCHAR(255),
    description TEXT,
    category_id INT,
    genre VARCHAR(100),
    language VARCHAR(50) DEFAULT 'Bengali',
    
    -- Author & Publication
    author VARCHAR(150) NOT NULL,
    co_author VARCHAR(150),
    publisher VARCHAR(150),
    publish_year VARCHAR(4),
    edition VARCHAR(50),
    isbn VARCHAR(50),
    
    -- Inventory & Location
    format ENUM('Hardcover', 'Paperback', 'E-book') DEFAULT 'Paperback',
    page_count INT DEFAULT 0,
    book_condition ENUM('New', 'Used', 'Damaged') DEFAULT 'New',
    shelf_location VARCHAR(50),
    rack_number VARCHAR(50),
    stock_qty INT DEFAULT 0,
    min_stock_level INT DEFAULT 2,
    is_borrowable BOOLEAN DEFAULT TRUE,
    is_suggested BOOLEAN DEFAULT TRUE,
    
    -- Pricing & Supply
    purchase_price DECIMAL(10, 2) DEFAULT 0.00,
    sell_price DECIMAL(10, 2) NOT NULL,
    discount_price DECIMAL(10, 2) DEFAULT 0.00,
    supplier_name VARCHAR(150),
    supplier_contact VARCHAR(255),
    
    -- Media (File Paths)
    cover_image VARCHAR(255),
    photo_2 VARCHAR(255),
    photo_3 VARCHAR(255),
    
    -- Metadata
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- 3. Admin Users
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    role ENUM('SuperAdmin', 'Manager', 'Editor') DEFAULT 'Editor',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Members/Customers
CREATE TABLE IF NOT EXISTS members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    membership_id VARCHAR(50) NOT NULL UNIQUE, -- e.g., OM-2026-BH81
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    address TEXT,
    membership_plan ENUM('General', 'BookLover', 'Collector') DEFAULT 'General',
    acc_balance DECIMAL(10, 2) DEFAULT 0.00,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 5. Orders (Transaction History)
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_no VARCHAR(50) NOT NULL UNIQUE, -- e.g., INV-1001
    member_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    subtotal DECIMAL(10, 2) NOT NULL,
    discount DECIMAL(10, 2) DEFAULT 0.00,
    shipping_cost DECIMAL(10, 2) DEFAULT 0.00,
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_status ENUM('Pending', 'Paid', 'Failed') DEFAULT 'Pending',
    payment_method ENUM('Cash', 'Bkash', 'Nagad', 'Card', 'Wallet') DEFAULT 'Cash',
    order_status ENUM('Processing', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Processing',
    shipping_address TEXT,
    notes TEXT,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
);

-- 6. Order Items
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT DEFAULT 1,
    unit_price DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    reading_progress INT DEFAULT 0, -- 0-100%
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

-- 7. Borrows (Borrowed Books)
CREATE TABLE IF NOT EXISTS borrows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    book_id INT NOT NULL,
    borrow_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    due_date DATE NOT NULL,
    return_date DATE,
    status ENUM('Processing', 'Active', 'Returned', 'Overdue') DEFAULT 'Active',
    reading_progress INT DEFAULT 0, -- 0-100%
    fine_amount DECIMAL(10, 2) DEFAULT 0.00,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

-- 8. Wallet Transactions (Deposit/Withdraw/Payment history)
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    type ENUM('Deposit', 'Purchase', 'Refund', 'Penalty') NOT NULL,
    description VARCHAR(255),
    reference_id VARCHAR(50), -- e.g., Order ID or Bkash TRX ID
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
);

-- Initial Data for Categories
INSERT INTO categories (name, slug) VALUES 
('ফিকশন', 'fiction'),
('নন-ফিকশন', 'non-fiction'),
('কবিতা', 'poetry'),
('প্রবন্ধ', 'essay');
