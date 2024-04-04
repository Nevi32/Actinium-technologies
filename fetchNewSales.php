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

// Function to fetch new sales entries within the past 1 hour including store name
function fetchNewSales() {
    $pdo = connectDatabase();
    $currentTime = date('Y-m-d H:i:s');
    $pastTime = date('Y-m-d H:i:s', strtotime('-1 hour', strtotime($currentTime)));
    
    $query = "SELECT sales.sale_id, main_entry.product_name, main_entry.category, sales.quantity_sold, users.full_name AS staff_name, sales.record_date, stores.location_name, sales.store_id, stores.store_name
              FROM sales
              INNER JOIN users ON sales.user_id = users.user_id
              INNER JOIN main_entry ON sales.main_entry_id = main_entry.main_entry_id
              INNER JOIN stores ON sales.store_id = stores.store_id
              WHERE sales.record_date BETWEEN :pastTime AND :currentTime";
    
    $statement = $pdo->prepare($query);
    $statement->bindParam(':pastTime', $pastTime);
    $statement->bindParam(':currentTime', $currentTime);
    $statement->execute();
    
    $salesData = $statement->fetchAll(PDO::FETCH_ASSOC);
    return $salesData;
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
function createNotificationMessage($salesData) {
    $notification = [];
    foreach ($salesData as $sale) {
        $notification[] = [
            'product_name' => $sale['product_name'],
            'category' => $sale['category'],
            'quantity_sold' => $sale['quantity_sold'],
            'staff_name' => $sale['staff_name'],
            'record_date' => $sale['record_date'],
            'location_name' => $sale['location_name'], // Include store location in the notification message
            'store_name' => $sale['store_name'], // Include store name in the notification message
            'store_id' => $sale['store_id'] // Include store ID in the notification message
        ];
    }
    return json_encode($notification, JSON_PRETTY_PRINT); // Ensure pretty print for easier readability
}

// Fetch new sales data
$salesData = fetchNewSales();

// Check if there are new sales
if (!empty($salesData)) {
    // Store notification for each store ID
    foreach ($salesData as $sale) {
        $storeName = $sale['store_name'];
        $storeId = $sale['store_id'];
        $message = createNotificationMessage([$sale]); // Create a separate message for each sale
        $currentTime = date('Y-m-d H:i:s');
        $result = storeNotification("Sales Status Update", $message, $currentTime, $storeId, $storeName);
        echo $result ? "Notification stored successfully for store ID: $storeId ($storeName)<br>" : "Failed to store notification for store ID: $storeId ($storeName)<br>";
    }
} else {
    echo "No new sales to create notifications.";
}
?>

