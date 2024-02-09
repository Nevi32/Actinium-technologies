<?php
require_once 'config.php';

// Check if user is logged in and get the store name and location
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    exit("User not logged in");
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

    // Fetch the latest inventory entry for each product
    $stmt = $pdo->prepare("SELECT m.product_name, m.category, i.quantity, i.price, (i.price / i.quantity) AS unit_price
                           FROM main_entry m
                           JOIN (
                               SELECT main_entry_id, MAX(record_date) AS latest_date
                               FROM inventory
                               WHERE store_id = ?
                               GROUP BY main_entry_id
                           ) latest_inventory ON m.main_entry_id = latest_inventory.main_entry_id
                           JOIN inventory i ON latest_inventory.main_entry_id = i.main_entry_id AND latest_inventory.latest_date = i.record_date
                           WHERE i.store_id = ?");
    $stmt->execute([$storeId, $storeId]);
    $inventoryEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return inventory data as JSON response
    header('Content-Type: application/json');
    echo json_encode($inventoryEntries);
    exit();
} catch (PDOException $e) {
    // Log the error details
    error_log("Database Error: " . $e->getMessage(), 0);
    http_response_code(500);
    exit("Internal Server Error");
}
?>

