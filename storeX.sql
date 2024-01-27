-- Create the stores table
CREATE TABLE stores (
    store_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    store_name VARCHAR(100) NOT NULL,
    location_name VARCHAR(255) NOT NULL,
    location_type ENUM('main_store', 'satellite') NOT NULL,
    UNIQUE KEY unique_store_location (store_name, location_name)
);

-- Create the users table
CREATE TABLE users (
    user_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('owner', 'staff') NOT NULL,
    comp_staff BOOLEAN NOT NULL DEFAULT 0,
    store_id INT,
    remember_token VARCHAR(255),
    token_expiry INT,
    full_name VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    commission_accumulated DECIMAL(10,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (store_id) REFERENCES stores(store_id)
);

CREATE TABLE main_entry (
    main_entry_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    total_quantity DECIMAL(10, 2) NOT NULL,
    quantity_description VARCHAR(255),
    store_id INT,
    CONSTRAINT uc_product_category_store UNIQUE (product_name, category, store_id),
    FOREIGN KEY (store_id) REFERENCES stores(store_id)
);



-- Create the inventory table
CREATE TABLE inventory (
    entry_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    main_entry_id INT,
    quantity DECIMAL(10,2) NOT NULL,
    quantity_description VARCHAR(255),
    price DECIMAL(10,2),
    record_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sale_id INT,
    store_id INT,
    FOREIGN KEY (main_entry_id) REFERENCES main_entry(main_entry_id),
    FOREIGN KEY (store_id) REFERENCES stores(store_id)
);

-- Create the sales table
CREATE TABLE sales (
    sale_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    main_entry_id INT,
    quantity_sold DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    sale_date TIMESTAMP,
    store_id INT,
    FOREIGN KEY (main_entry_id) REFERENCES main_entry(main_entry_id),
    FOREIGN KEY (store_id) REFERENCES stores(store_id)
);

-- Create the suppliers table
CREATE TABLE suppliers (
    supplier_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    supplier_name VARCHAR(100) NOT NULL UNIQUE,
    phone_number VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    address VARCHAR(255)
);

-- Create the notifications table
CREATE TABLE notifications (
    notification_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    store_id INT,
    FOREIGN KEY (store_id) REFERENCES stores(store_id)
);

CREATE TABLE inventory_orders (
    order_id INT NOT NULL AUTO_INCREMENT,
    main_store_id INT,
    destination_store_id INT,
    main_entry_id INT,
    quantity DECIMAL(10,2) NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    cleared TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (order_id),
    INDEX (main_store_id),
    INDEX (destination_store_id),
    INDEX (main_entry_id),
    FOREIGN KEY (main_store_id) REFERENCES main_store(store_id),
    FOREIGN KEY (destination_store_id) REFERENCES satellite_store(store_id),
    FOREIGN KEY (main_entry_id) REFERENCES main_entry(main_entry_id)
);

-- Create the commissions table
CREATE TABLE commissions (
    commission_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    sales_id INT,
    commission_amount DECIMAL(10,2) NOT NULL,
    commission_date DATE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (sales_id) REFERENCES sales(sale_id)
);

DELIMITER //

CREATE TRIGGER subtract_quantity_after_order
AFTER INSERT ON inventory_orders
FOR EACH ROW
BEGIN
    DECLARE main_entry_quantity DECIMAL(10,2);

    -- Get the current quantity from main_entry table in the main_store
    SELECT total_quantity INTO main_entry_quantity
    FROM main_entry ME
    JOIN stores S ON ME.store_id = S.store_id
    WHERE ME.main_entry_id = NEW.main_entry_id
    AND S.location_type = 'main_store';

    -- Subtract the ordered quantity from the main_entry table in the main_store
    UPDATE main_entry ME
    JOIN stores S ON ME.store_id = S.store_id
    SET ME.total_quantity = main_entry_quantity - NEW.quantity
    WHERE ME.main_entry_id = NEW.main_entry_id
    AND S.location_type = 'main_store';
END;
//

DELIMITER ;

