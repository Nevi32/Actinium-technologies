<?php
require_once 'config.php';

// Check if user is logged in and get the store name and location
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
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

    // Fetch all main_entry data related to the store ID
    $stmt = $pdo->prepare("SELECT * FROM main_entry WHERE store_id = ?");
    $stmt->execute([$storeId]);
    $mainEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch individual entries from the inventory table
    $stmt = $pdo->prepare("SELECT * FROM inventory WHERE store_id = ?");
    $stmt->execute([$storeId]);
    $inventoryEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Store data in session
    $_SESSION['main_entries'] = $mainEntries;
    $_SESSION['inventory_entries'] = $inventoryEntries;

    // Redirect to viewinventorysat.php
    header("Location: viewinventorysat.php");
    exit();
} catch (PDOException $e) {
    // Log the error details
    error_log("Database Error: " . $e->getMessage(), 0);
    http_response_code(500);
    exit("Internal Server Error");
}
?>

