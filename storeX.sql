-- Create users table
CREATE TABLE users (
    user_id INT NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('owner','staff') NOT NULL,
    comp_staff TINYINT(1) NOT NULL DEFAULT 0,
    store_id INT,
    remember_token VARCHAR(255),
    token_expiry INT,
    full_name VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    gender ENUM('Male','Female','Other') NOT NULL,
    commission_accumulated DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (user_id),
    UNIQUE KEY (username),
    UNIQUE KEY (email),
    FOREIGN KEY (store_id) REFERENCES stores(store_id)
);

-- Create suppliers table
CREATE TABLE suppliers (
    supplier_id INT NOT NULL AUTO_INCREMENT,
    supplier_name VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    address VARCHAR(255),
    PRIMARY KEY (supplier_id),
    UNIQUE KEY (supplier_name)
);

-- Create stores table
CREATE TABLE stores (
    store_id INT NOT NULL AUTO_INCREMENT,
    store_name VARCHAR(100) NOT NULL,
    location_name VARCHAR(255) NOT NULL,
    location_type ENUM('main_store','satellite') NOT NULL,
    PRIMARY KEY (store_id)
);

-- Create sales table
CREATE TABLE sales (
    sale_id INT NOT NULL AUTO_INCREMENT,
    main_entry_id INT,
    quantity_sold DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    sale_date TIMESTAMP,
    store_id INT,
    PRIMARY KEY (sale_id),
    FOREIGN KEY (main_entry_id) REFERENCES main_entry(main_entry_id),
    FOREIGN KEY (store_id) REFERENCES stores(store_id)
);

-- Create notifications table
CREATE TABLE notifications (
    notification_id INT NOT NULL AUTO_INCREMENT,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    store_id INT,
    PRIMARY KEY (notification_id),
    FOREIGN KEY (store_id) REFERENCES stores(store_id)
);

-- Create main_entry table
CREATE TABLE main_entry (
    main_entry_id INT NOT NULL AUTO_INCREMENT,
    product_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    total_quantity DECIMAL(10,2) NOT NULL,
    quantity_description VARCHAR(255),
    store_id INT,
    PRIMARY KEY (main_entry_id),
    FOREIGN KEY (store_id) REFERENCES stores(store_id)
);

-- Create inventory_orders table
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
    FOREIGN KEY (main_store_id) REFERENCES stores(store_id),
    FOREIGN KEY (destination_store_id) REFERENCES stores(store_id),
    FOREIGN KEY (main_entry_id) REFERENCES main_entry(main_entry_id)
);

-- Create inventory table
CREATE TABLE inventory (
    entry_id INT NOT NULL AUTO_INCREMENT,
    main_entry_id INT,
    quantity DECIMAL(10,2) NOT NULL,
    quantity_description VARCHAR(255),
    price DECIMAL(10,2),
    record_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sale_id INT,
    store_id INT,
    PRIMARY KEY (entry_id),
    FOREIGN KEY (main_entry_id) REFERENCES main_entry(main_entry_id),
    FOREIGN KEY (sale_id) REFERENCES sales(sale_id),
    FOREIGN KEY (store_id) REFERENCES stores(store_id)
);

-- Create commissions table
CREATE TABLE commissions (
    commission_id INT NOT NULL AUTO_INCREMENT,
    user_id INT,
    sales_id INT,
    commission_amount DECIMAL(10,2) NOT NULL,
    commission_date DATE NOT NULL,
    PRIMARY KEY (commission_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (sales_id) REFERENCES sales(sale_id)
);

DELIMITER //

CREATE TRIGGER update_main_entry_quantity
AFTER INSERT ON inventory_orders
FOR EACH ROW
BEGIN
    DECLARE main_entry_quantity DECIMAL(10,2);
    
    -- Get the current quantity of the main entry in the main store
    SELECT total_quantity INTO main_entry_quantity
    FROM main_entry
    WHERE main_entry_id = NEW.main_entry_id
    AND store_id = NEW.main_store_id;
    
    -- Subtract the quantity from the main store's main entry
    UPDATE main_entry
    SET total_quantity = main_entry_quantity - NEW.quantity
    WHERE main_entry_id = NEW.main_entry_id
    AND store_id = NEW.main_store_id;
END;
//

DELIMITER ;

