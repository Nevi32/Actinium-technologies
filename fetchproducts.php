<?php
// Include database credentials
include 'config.php';

// Start the session
session_start();

// Check if the store name and location are set in the session
if (!isset($_SESSION['store_name'], $_SESSION['location_name'])) {
    $response = ['success' => false, 'message' => 'Store name or location not found in session.'];
    echo json_encode($response);
    exit();
}

// Get the store name and location from the session
$storeName = $_SESSION['store_name'];
$locationName = $_SESSION['location_name'];

try {
    $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query the stores table to get the store ID based on store name and location
    $stmt = $pdo->prepare("SELECT store_id FROM stores WHERE store_name = ? AND location_name = ?");
    $stmt->execute([$storeName, $locationName]);
    $store = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$store) {
        $response = ['success' => false, 'message' => 'Store not found.'];
        echo json_encode($response);
        exit();
    }

    // Get the store ID
    $storeId = $store['store_id'];

    // Fetch distinct product names from main_entry table for the specified store
    $stmt = $pdo->prepare("SELECT DISTINCT product_name FROM main_entry WHERE store_id = ?");
    $stmt->execute([$storeId]);
    $products = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Fetch distinct categories from main_entry table for the specified store
    $stmt = $pdo->prepare("SELECT DISTINCT category FROM main_entry WHERE store_id = ?");
    $stmt->execute([$storeId]);
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Return product names and categories as JSON
    $response = ['success' => true, 'products' => $products, 'categories' => $categories];
    echo json_encode($response);
} catch (PDOException $e) {
    // Return error message for any database query errors
    $response = ['success' => false, 'message' => 'Error fetching products: ' . $e->getMessage()];
    echo json_encode($response);
}
?>

