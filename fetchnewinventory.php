<?php
require_once('config.php');

// Function to connect to the database
function connectDatabase() {
    global $databaseConfig;
    $dsn = 'mysql:host=' . $databaseConfig['host'] . ';dbname=' . $databaseConfig['dbname'];
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    try {
        $pdo = new PDO($dsn, $databaseConfig['user'], $databaseConfig['password'], $options);
        return $pdo;
    } catch (PDOException $e) {
        exit("Database connection failed: " . $e->getMessage());
    }
}

// Function to fetch new inventory entries within the past 2 hours
function fetchNewInventory() {
    $pdo = connectDatabase();
    $currentTime = date('Y-m-d H:i:s');
    $pastTime = date('Y-m-d H:i:s', strtotime('-2 hours', strtotime($currentTime)));
    
    $query = "SELECT inventory.entry_id, main_entry.product_name, main_entry.category, inventory.quantity, inventory.quantity_description, stores.location_name
              FROM inventory
              INNER JOIN main_entry ON inventory.main_entry_id = main_entry.main_entry_id
              INNER JOIN stores ON inventory.store_id = stores.store_id
              WHERE inventory.record_date BETWEEN :pastTime AND :currentTime";
    
    $statement = $pdo->prepare($query);
    $statement->bindParam(':pastTime', $pastTime);
    $statement->bindParam(':currentTime', $currentTime);
    $statement->execute();
    
    $inventoryData = $statement->fetchAll(PDO::FETCH_ASSOC);
    return $inventoryData;
}

// Function to fetch cleared orders within the past 2 hours
function fetchClearedOrders() {
    $pdo = connectDatabase();
    $currentTime = date('Y-m-d H:i:s');
    $pastTime = date('Y-m-d H:i:s', strtotime('-2 hours', strtotime($currentTime)));
    
    $query = "SELECT inventory_orders.order_id, main_entry.product_name, main_entry.category, stores.location_name AS destination_location
              FROM inventory_orders
              INNER JOIN main_entry ON inventory_orders.main_entry_id = main_entry.main_entry_id
              INNER JOIN stores ON inventory_orders.destination_store_id = stores.store_id
              WHERE inventory_orders.order_date BETWEEN :pastTime AND :currentTime
              AND inventory_orders.cleared = 1";
    
    $statement = $pdo->prepare($query);
    $statement->bindParam(':pastTime', $pastTime);
    $statement->bindParam(':currentTime', $currentTime);
    $statement->execute();
    
    $clearedOrdersData = $statement->fetchAll(PDO::FETCH_ASSOC);
    return $clearedOrdersData;
}

// Function to store notification in the database
function storeNotification($subject, $message, $timestamp, $store_id) {
    $pdo = connectDatabase();
    
    $query = "INSERT INTO notifications (subject, message, timestamp, store_id) VALUES (:subject, :message, :timestamp, :store_id)";
    $statement = $pdo->prepare($query);
    $statement->bindParam(':subject', $subject);
    $statement->bindParam(':message', $message);
    $statement->bindParam(':timestamp', $timestamp);
    $statement->bindParam(':store_id', $store_id);
    return $statement->execute();
}

// Function to create notification message in JSON format
function createNotificationMessage($inventoryData, $clearedOrdersData) {
    $notification = [];
    
    // Add new inventory entries to the notification message
    foreach ($inventoryData as $item) {
        $notification[] = [
            'type' => 'inventory',
            'product_name' => $item['product_name'],
            'category' => $item['category'],
            'quantity' => $item['quantity'],
            'quantity_description' => $item['quantity_description'],
            'location_name' => $item['location_name']
        ];
    }

    // Add cleared orders to the notification message
    foreach ($clearedOrdersData as $order) {
        $notification[] = [
            'type' => 'cleared_orders',
            'product_name' => $order['product_name'],
            'category' => $order['category'],
            'destination_location' => $order['destination_location']
        ];
    }

    return json_encode($notification, JSON_PRETTY_PRINT); // Ensure pretty print for easier readability
}

// Fetch new inventory data
$inventoryData = fetchNewInventory();

// Fetch cleared orders data
$clearedOrdersData = fetchClearedOrders();

// Create notification message
$message = createNotificationMessage($inventoryData, $clearedOrdersData);

// Store notification in the database
$currentTime = date('Y-m-d H:i:s');
$store_id = null; // Replace with the actual store ID
$result = storeNotification("Inventory Update", $message, $currentTime, $store_id);

echo $result ? "Notification stored successfully." : "Failed to store notification.";
?>

