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

    // Check if the store is a main store or satellite
    $storeTypeQuery = "SELECT location_type FROM stores WHERE store_name = ? AND location_name = ?";
    $storeTypeStmt = $pdo->prepare($storeTypeQuery);
    $storeTypeStmt->execute([$storeName, $locationName]);
    $storeType = $storeTypeStmt->fetchColumn();

    // Fetch inventory based on store_id
    $inventoryQuery = "SELECT main_entry_id, product_name, category, total_quantity, quantity_description, store_id
                       FROM main_entry
                       WHERE store_id IN (SELECT store_id FROM stores WHERE store_name = ? AND location_name = ?)";

    $inventoryStmt = $pdo->prepare($inventoryQuery);
    $inventoryStmt->execute([$storeName, $locationName]);
    $inventoryData = $inventoryStmt->fetchAll(PDO::FETCH_ASSOC);

    if ($storeType === 'main_store') {
        // If it's a main store, fetch individual inventory data
        $individualInventoryQuery = "SELECT entry_id, quantity, quantity_description, price, record_date, sale_id, store_id
                                     FROM inventory
                                     WHERE store_id = (SELECT store_id FROM stores WHERE store_name = ? AND location_name = ?)";

        $individualInventoryStmt = $pdo->prepare($individualInventoryQuery);
        $individualInventoryStmt->execute([$storeName, $locationName]);
        $individualInventoryData = $individualInventoryStmt->fetchAll(PDO::FETCH_ASSOC);

        // Set session variables for main store data
        $_SESSION['main_store_inventory_data'] = $inventoryData;
        $_SESSION['main_store_individual_inventory_data'] = $individualInventoryData;
    } else {
        // If it's a satellite store, set session variables
        $_SESSION['satellite_inventory_data'] = $inventoryData;
    }

    // Set common session variables
    $_SESSION['storeName'] = $storeName;
    $_SESSION['locationName'] = $locationName;

    // Redirect to viewinventory.php
    header('Location: viewinventory.php');
    exit();

} catch (PDOException $e) {
    // Handle database errors
    echo "Error fetching inventory data: " . $e->getMessage();
}
?>

