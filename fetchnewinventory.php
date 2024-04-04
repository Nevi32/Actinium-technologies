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

// Function to fetch new inventory entries within the past 24 hours
function fetchNewInventory() {
    $pdo = connectDatabase();
    $currentTime = date('Y-m-d H:i:s');
    $pastTime = date('Y-m-d H:i:s', strtotime('-24 hours', strtotime($currentTime)));
    
    $query = "SELECT inventory.entry_id, main_entry.product_name, main_entry.category, inventory.quantity, inventory.quantity_description, stores.store_name, inventory.store_id
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

// Function to store notification in the database
function storeNotification($subject, $message, $timestamp, $store_id, $store_name) {
    $pdo = connectDatabase();
    
    $query = "INSERT INTO notifications (subject, message, timestamp, store_id, store_name) VALUES (:subject, :message, :timestamp, :store_id, :store_name)";
    $statement = $pdo->prepare($query);
    $statement->bindParam(':subject', $subject);
    $statement->bindParam(':message', $message);
    $statement->bindParam(':timestamp', $timestamp);
    $statement->bindParam(':store_id', $store_id);
    $statement->bindParam(':store_name', $store_name);
    return $statement->execute();
}

// Function to create notification message in JSON format
function createNotificationMessage($inventoryData) {
    $notification = [];
    
    foreach ($inventoryData as $item) {
        $notification[] = [
            'product_name' => $item['product_name'],
            'category' => $item['category'],
            'quantity' => $item['quantity'],
            'quantity_description' => $item['quantity_description'],
            'store_name' => $item['store_name'] // Use store_name instead of location_name
        ];
    }

    return json_encode($notification, JSON_PRETTY_PRINT); // Ensure pretty print for easier readability
}

// Fetch new inventory data
$inventoryData = fetchNewInventory();

// Check if there are new inventory entries
if (!empty($inventoryData)) {
    // Store notification for each store ID
    foreach ($inventoryData as $item) {
        $storeName = $item['store_name']; // Use store_name instead of location_name
        $storeId = $item['store_id'];
        $message = createNotificationMessage([$item]); // Create a separate message for each inventory item
        $currentTime = date('Y-m-d H:i:s');
        $result = storeNotification("Inventory Update", $message, $currentTime, $storeId, $storeName);
        echo $result ? "Notification stored successfully for store ID: $storeId ($storeName)<br>" : "Failed to store notification for store ID: $storeId ($storeName)<br>";
    }
} else {
    echo "No new inventory entries to create notifications.";
}
?>

