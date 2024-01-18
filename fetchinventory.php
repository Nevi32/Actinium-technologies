<?php
// Include the configuration file
include 'config.php';

session_start();

try {
    // Create a PDO connection using the configuration from config.php
    $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Retrieve store information from query parameters
    $storeName = $_GET['storeName'] ?? '';
    $locationName = $_GET['locationName'] ?? '';

    if (empty($storeName) || empty($locationName)) {
        // If store name or location is not set, redirect to an error page or handle accordingly
        echo "Error: Store name or location not set.";
        exit();
    }

    // Check if the store has satellite stores
    $satelliteCheckQuery = "SELECT COUNT(*) AS count FROM stores WHERE store_name = ? AND location_name = ?";
    $satelliteCheckStmt = $pdo->prepare($satelliteCheckQuery);
    $satelliteCheckStmt->execute([$storeName, $locationName]);
    $satelliteCount = $satelliteCheckStmt->fetch(PDO::FETCH_ASSOC)['count'];

    if ($satelliteCount > 0) {
        // Fetch inventory for the main store and its satellite stores
        $inventoryQuery = "SELECT main_entry_id, product_name, category, total_quantity, quantity_description, store_id
                           FROM main_entry
                           WHERE store_name = ? AND location_name = ?";
    } else {
        // Fetch inventory only for the main store
        $inventoryQuery = "SELECT main_entry_id, product_name, category, total_quantity, quantity_description, store_id
                           FROM main_entry
                           WHERE store_name = ? AND location_name = ?";
    }

    $inventoryStmt = $pdo->prepare($inventoryQuery);
    $inventoryStmt->execute([$storeName, $locationName]);
    $inventoryData = $inventoryStmt->fetchAll(PDO::FETCH_ASSOC);

    // Set session variables
    $_SESSION['storeName'] = $storeName;
    $_SESSION['locationName'] = $locationName;
    $_SESSION['inventory_data'] = $inventoryData;

    // Redirect to viewinventory.php
    header('Location: viewinventory.php');
    exit();

} catch (PDOException $e) {
    // Handle database errors
    echo "Error fetching inventory data: " . $e->getMessage();
}
?>

