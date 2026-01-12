-- E-Commerce Website Database Schema
-- Run this SQL file in phpMyAdmin or MySQL command line

CREATE DATABASE IF NOT EXISTS ecommerce_db;
USE ecommerce_db;

-- Admins table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Suppliers table
CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Customers table
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    image VARCHAR(255),
    supplier_id INT,
    category VARCHAR(50),
    featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contact messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin (password: admin123)
-- Note: Run setup_admin.php after database setup to set the correct password hash
-- Or manually set password using: UPDATE admins SET password = '$2y$10$YourHashHere' WHERE username = 'admin';
INSERT INTO admins (username, email, password) VALUES 
('admin', 'admin@ecommerce.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert sample suppliers
INSERT INTO suppliers (name, email, phone, address) VALUES
('Tech Supplies Co.', 'tech@supplier.com', '123-456-7890', '123 Tech Street, City'),
('Fashion World Ltd.', 'fashion@supplier.com', '234-567-8901', '456 Fashion Ave, City'),
('Home Essentials Inc.', 'home@supplier.com', '345-678-9012', '789 Home Road, City');

-- Insert sample products
INSERT INTO products (name, description, price, stock_quantity, image, supplier_id, category, featured) VALUES
('Laptop Computer', 'High-performance laptop with 16GB RAM and 512GB SSD', 999.99, 15, 'laptop.jpg', 1, 'Electronics', 1),
('Wireless Mouse', 'Ergonomic wireless mouse with long battery life', 29.99, 50, 'mouse.jpg', 1, 'Electronics', 1),
('Cotton T-Shirt', 'Comfortable 100% cotton t-shirt in various colors', 19.99, 100, 'tshirt.jpg', 2, 'Clothing', 1),
('Jeans', 'Classic fit denim jeans', 49.99, 75, 'jeans.jpg', 2, 'Clothing', 0),
('Coffee Maker', 'Programmable coffee maker with thermal carafe', 79.99, 30, 'coffeemaker.jpg', 3, 'Home', 1),
('Desk Lamp', 'LED desk lamp with adjustable brightness', 34.99, 40, 'lamp.jpg', 3, 'Home', 0);

