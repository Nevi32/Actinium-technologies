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

// Function to fetch new sales entries within the past 5 minutes
function fetchNewSales() {
    $pdo = connectDatabase();
    $currentTime = date('Y-m-d H:i:s');
    $pastTime = date('Y-m-d H:i:s', strtotime('-5 minutes', strtotime($currentTime)));
    
    $query = "SELECT sales.sale_id, main_entry.product_name, main_entry.category, sales.quantity_sold, users.full_name AS staff_name, sales.record_date, stores.location_name
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
function createNotificationMessage($salesData) {
    $notification = [];
    foreach ($salesData as $sale) {
        $notification[] = [
            'product_name' => $sale['product_name'],
            'category' => $sale['category'],
            'quantity_sold' => $sale['quantity_sold'],
            'staff_name' => $sale['staff_name'],
            'record_date' => $sale['record_date'],
            'location_name' => $sale['location_name'] // Include store location in the notification message
        ];
    }
    return json_encode($notification);
}

// Fetch new sales data
$salesData = fetchNewSales();

// Check if there are new sales
if (!empty($salesData)) {
    // Create notification message
    $message = createNotificationMessage($salesData);

    // Store notification in the database
    $currentTime = date('Y-m-d H:i:s');
    $store_id = null; // Replace with the actual store ID
    $result = storeNotification("Sales Status Update", $message, $currentTime, $store_id);

    echo $result ? "Notification stored successfully." : "Failed to store notification.";
} else {
    echo "No new sales to create notifications.";
}
?>

