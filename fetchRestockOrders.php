<?php
// Include database credentials
include 'config.php';

// Start session
session_start();

// Establish database connection
try {
    $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Return error message if database connection fails
    $response = ['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()];
    echo json_encode($response);
    exit(); // Exit script
}

try {
    // Retrieve store name from session
    if (!isset($_SESSION['store_name'])) {
        $response = ['success' => false, 'message' => 'Store name not set in session'];
        echo json_encode($response);
        exit();
    }
    $store_name = $_SESSION['store_name'];

    // Retrieve main store ID
    $stmt = $pdo->prepare("SELECT store_id FROM stores WHERE store_name = :store_name AND location_type = 'main_store'");
    $stmt->execute(['store_name' => $store_name]);
    $main_store_id = $stmt->fetchColumn();

    // Retrieve satellite locations associated with the store name
    $stmt = $pdo->prepare("SELECT store_id FROM stores WHERE store_name = :store_name AND location_type = 'satellite'");
    $stmt->execute(['store_name' => $store_name]);
    $satellite_store_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Fetch restock orders from the database for main store and satellite locations
    $stmt = $pdo->prepare("SELECT main_entry.product_name, main_entry.category, inventory_orders.price, inventory_orders.order_date, stores.location_name AS satellite_location
                          FROM inventory_orders
                          INNER JOIN main_entry ON inventory_orders.main_entry_id = main_entry.main_entry_id
                          INNER JOIN stores ON inventory_orders.destination_store_id = stores.store_id
                          WHERE inventory_orders.main_store_id = :main_store_id OR inventory_orders.destination_store_id IN (" . implode(',', $satellite_store_ids) . ")
                          AND inventory_orders.order_date >= DATE_SUB(NOW(), INTERVAL 1 DAY)");
    $stmt->execute(['main_store_id' => $main_store_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return orders data as JSON
    $response = ['success' => true, 'orders' => $orders];
    echo json_encode($response);
} catch (PDOException $e) {
    // Return error message for any database query errors
    $response = ['success' => false, 'message' => 'Error fetching restock orders: ' . $e->getMessage()];
    echo json_encode($response);
}
?>

