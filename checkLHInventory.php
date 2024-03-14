<?php
require_once 'config.php';

// Connect to the database
$dsn = "mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}";
try {
    $pdo = new PDO($dsn, $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Query for low inventory products (quantity below 5)
$lowInventoryQuery = $pdo->prepare("
    SELECT me.product_name, me.category, me.total_quantity, me.quantity_description, 
           s.location_name AS store_location
    FROM main_entry me
    INNER JOIN stores s ON me.store_id = s.store_id
    WHERE me.total_quantity < 5
");
$lowInventoryQuery->execute();
$lowInventoryProducts = $lowInventoryQuery->fetchAll(PDO::FETCH_ASSOC);

// Query for high inventory products (quantity above 25)
$highInventoryQuery = $pdo->prepare("
    SELECT me.product_name, me.category, me.total_quantity, me.quantity_description, 
           s.location_name AS store_location
    FROM main_entry me
    INNER JOIN stores s ON me.store_id = s.store_id
    WHERE me.total_quantity > 25
");
$highInventoryQuery->execute();
$highInventoryProducts = $highInventoryQuery->fetchAll(PDO::FETCH_ASSOC);

// Prepare and execute notification insertion for low inventory products
foreach ($lowInventoryProducts as $product) {
    $message = "Low inventory status:\nProduct: {$product['product_name']}\nCategory: {$product['category']}\nQuantity: {$product['total_quantity']}\nQuantity Description: {$product['quantity_description']}\nStore Location: {$product['store_location']}";
    insertNotification($pdo, $message);
}

// Prepare and execute notification insertion for high inventory products
foreach ($highInventoryProducts as $product) {
    $message = "High inventory status:\nProduct: {$product['product_name']}\nCategory: {$product['category']}\nQuantity: {$product['total_quantity']}\nQuantity Description: {$product['quantity_description']}\nStore Location: {$product['store_location']}";
    insertNotification($pdo, $message);
}

// If there are no low or high inventory products, log a moderate inventory status
if (empty($lowInventoryProducts) && empty($highInventoryProducts)) {
    $message = "Moderate inventory status";
    insertNotification($pdo, $message);
}

// Function to insert notification into the notifications table
function insertNotification($pdo, $message) {
    $subject = "Inventory Status Update";
    try {
        $stmt = $pdo->prepare("
            INSERT INTO notifications (subject, message, store_id) 
            VALUES (:subject, :message, NULL)
        ");
        $stmt->bindParam(':subject', $subject);
        $stmt->bindParam(':message', $message);
        $stmt->execute();
    } catch (PDOException $e) {
        // Handle any errors in notification insertion
        echo "Error: " . $e->getMessage();
    }
}
?>

