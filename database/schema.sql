CREATE DATABASE IF NOT EXISTS smart_tiles_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE smart_tiles_db;

CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS inventory (
    tile_id INT AUTO_INCREMENT PRIMARY KEY,
    tile_name VARCHAR(150) NOT NULL,
    category VARCHAR(100),
    color VARCHAR(100),
    size VARCHAR(50) NOT NULL,
    finish VARCHAR(100),
    unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock_quantity INT NOT NULL DEFAULT 0,
    reorder_level INT NOT NULL DEFAULT 10,
    tiles_per_box INT NOT NULL DEFAULT 1,
    supplier VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_inventory_tile_size (tile_name, size)
);

CREATE TABLE IF NOT EXISTS sales (
    sale_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(150) NOT NULL,
    customer_phone VARCHAR(50),
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_by INT NULL,
    CONSTRAINT fk_sales_user
        FOREIGN KEY (created_by) REFERENCES users(user_id)
        ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS sale_items (
    sale_item_id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    tile_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    line_total DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_sale_items_sale
        FOREIGN KEY (sale_id) REFERENCES sales(sale_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_sale_items_tile
        FOREIGN KEY (tile_id) REFERENCES inventory(tile_id)
);

CREATE TABLE IF NOT EXISTS estimations (
    estimation_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(150) NOT NULL,
    tile_id INT NULL,
    room_length DECIMAL(10,2) NOT NULL,
    room_width DECIMAL(10,2) NOT NULL,
    tile_length_cm DECIMAL(10,2) NOT NULL,
    tile_width_cm DECIMAL(10,2) NOT NULL,
    wastage_percent DECIMAL(5,2) NOT NULL DEFAULT 10.00,
    needed_tiles INT NOT NULL,
    boxes_needed INT NOT NULL,
    estimated_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_estimations_tile
        FOREIGN KEY (tile_id) REFERENCES inventory(tile_id)
        ON DELETE SET NULL,
    CONSTRAINT fk_estimations_user
        FOREIGN KEY (created_by) REFERENCES users(user_id)
        ON DELETE SET NULL
);

INSERT INTO users (username, email, password_hash, role)
VALUES
    ('admin', 'admin@smarttiles.local', '$2y$10$2UL3MsBWQ9JoC89JrtF53.Mi82cLinzo8JxsLPMcYV0KeBonz2qYS', 'admin'),
    ('staff', 'staff@smarttiles.local', '$2y$10$2UL3MsBWQ9JoC89JrtF53.Mi82cLinzo8JxsLPMcYV0KeBonz2qYS', 'staff')
ON DUPLICATE KEY UPDATE
    email = VALUES(email),
    role = VALUES(role);

INSERT INTO inventory (tile_name, category, color, size, finish, unit_price, stock_quantity, reorder_level, tiles_per_box, supplier)
VALUES
    ('Carrara White Ceramic', 'Floor', 'White', '60x60 cm', 'Glossy', 185.00, 120, 20, 4, 'Mauritius Tile Supply'),
    ('Basalt Grey Porcelain', 'Outdoor', 'Grey', '30x60 cm', 'Matte', 145.00, 80, 15, 6, 'Island Ceramics'),
    ('Ocean Blue Mosaic', 'Wall', 'Blue', '30x30 cm', 'Glossy', 95.00, 45, 12, 10, 'Decor Plus')
ON DUPLICATE KEY UPDATE
    tile_name = VALUES(tile_name);
