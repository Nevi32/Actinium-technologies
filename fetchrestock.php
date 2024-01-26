<?php
require_once 'config.php';

// Check if user is logged in and get the store name and location
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit("Unauthorized");
}

$storeName = $_SESSION['store_name'];
$locationName = $_SESSION['location_name'];

try {
    // Connect to the database
    $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get the store ID using the store name and location
    $stmt = $pdo->prepare("SELECT store_id FROM stores WHERE store_name = ? AND location_name = ?");
    $stmt->execute([$storeName, $locationName]);
    $store = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$store) {
        http_response_code(404);
        exit("Store not found");
    }

    $storeId = $store['store_id'];

    // Fetch restock orders that are not cleared
    $stmt = $pdo->prepare("SELECT o.order_id, e.product_name, e.category, o.quantity
                            FROM inventory_orders o
                            INNER JOIN main_entry e ON o.main_entry_id = e.main_entry_id
                            WHERE o.destination_store_id = ? AND o.cleared = 0"); // Only select orders that are not cleared
    $stmt->execute([$storeId]);
    $restockOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($restockOrders);
} catch (PDOException $e) {
    // Log the error details
    error_log("Database Error: " . $e->getMessage(), 0);
    http_response_code(500);
    exit("Internal Server Error");
}
?>

