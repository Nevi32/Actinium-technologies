CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('owner','staff') NOT NULL,
    comp_staff TINYINT(1) NOT NULL DEFAULT 0,
    store_id INT,
    remember_token VARCHAR(255),
    token_expiry INT,
    full_name VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    gender ENUM('Male','Female','Other') NOT NULL,
    commission_accumulated DECIMAL(10,2) NOT NULL DEFAULT 0.00
);

CREATE TABLE stores (
    store_id INT AUTO_INCREMENT PRIMARY KEY,
    store_name VARCHAR(100) NOT NULL,
    location_name VARCHAR(255) NOT NULL,
    location_type ENUM('main_store','satellite') NOT NULL
);

CREATE TABLE suppliers (
    supplier_id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name VARCHAR(100) NOT NULL UNIQUE,
    phone_number VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    address VARCHAR(255)
);

CREATE TABLE sales (
    sale_id INT AUTO_INCREMENT PRIMARY KEY,
    main_entry_id INT,
    quantity_sold DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    store_id INT,
    user_id INT,
    record_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (main_entry_id) REFERENCES main_entry(main_entry_id),
    FOREIGN KEY (store_id) REFERENCES stores(store_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    store_id INT,
    FOREIGN KEY (store_id) REFERENCES stores(store_id)
);

CREATE TABLE main_entry (
    main_entry_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    total_quantity DECIMAL(10,2) NOT NULL,
    quantity_description VARCHAR(255),
    store_id INT,
    FOREIGN KEY (store_id) REFERENCES stores(store_id)
);

CREATE TABLE inventory_orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    main_store_id INT,
    destination_store_id INT,
    main_entry_id INT,
    quantity DECIMAL(10,2) NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    cleared TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (main_store_id) REFERENCES stores(store_id),
    FOREIGN KEY (destination_store_id) REFERENCES stores(store_id),
    FOREIGN KEY (main_entry_id) REFERENCES main_entry(main_entry_id)
);

CREATE TABLE inventory (
    entry_id INT AUTO_INCREMENT PRIMARY KEY,
    main_entry_id INT,
    quantity DECIMAL(10,2) NOT NULL,
    quantity_description VARCHAR(255),
    price DECIMAL(10,2),
    record_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sale_id INT,
    store_id INT,
    FOREIGN KEY (main_entry_id) REFERENCES main_entry(main_entry_id),
    FOREIGN KEY (sale_id) REFERENCES sales(sale_id),
    FOREIGN KEY (store_id) REFERENCES stores(store_id)
);

CREATE TABLE commissions (
    commission_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    sales_id INT,
    commission_amount DECIMAL(10,2) NOT NULL,
    commission_date DATE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (sales_id) REFERENCES sales(sale_id)
);

CREATE TABLE prices (
    price_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    selling_price DECIMAL(10,2) NOT NULL,
    buying_price DECIMAL(10,2) NOT NULL,
    profit DECIMAL(10,2) NOT NULL,
    percentage_profit DECIMAL(10,2),
    set_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    product_name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL
);
CREATE TABLE dynamicprices (
    dynamic_price_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    price_id INT NOT NULL,
    selling_price DECIMAL(10,2) NOT NULL,
    profit DECIMAL(10,2) NOT NULL,
    percentage_profit DECIMAL(10,2),
    FOREIGN KEY (price_id) REFERENCES prices(price_id)
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

DELIMITER //
CREATE TRIGGER subtract_quantity_sold
AFTER INSERT ON sales
FOR EACH ROW
BEGIN
    DECLARE main_entry_quantity DECIMAL(10, 2);

    -- Get the current quantity of the product in the main_entry table
    SELECT total_quantity INTO main_entry_quantity
    FROM main_entry
    WHERE main_entry_id = NEW.main_entry_id
    AND store_id = NEW.store_id;

    -- Subtract the quantity sold from the main_entry table
    UPDATE main_entry
    SET total_quantity = main_entry_quantity - NEW.quantity_sold
    WHERE main_entry_id = NEW.main_entry_id
    AND store_id = NEW.store_id;
END //
DELIMITER ;

