<?php
require_once 'config.php';

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

try {
    // Connect to the database
    $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch store name and location from the session
    $storeName = $_SESSION['store_name'];
    $locationName = $_SESSION['location_name'];

    // Fetch main store ID
    $mainStoreStmt = $pdo->prepare("SELECT store_id FROM stores WHERE store_name = ? AND location_name = ? AND location_type = 'main_store'");
    $mainStoreStmt->execute([$storeName, $locationName]);
    $mainStoreData = $mainStoreStmt->fetch(PDO::FETCH_ASSOC);

    if ($mainStoreData) {
        $mainStoreId = $mainStoreData['store_id'];

        // Fetch main store sales data
        $mainSalesStmt = $pdo->prepare("SELECT e.product_name, e.category, e.quantity_description, s.quantity_sold, s.total_price, s.record_date
                                        FROM sales s
                                        INNER JOIN main_entry e ON s.main_entry_id = e.main_entry_id
                                        WHERE s.store_id = ?");
        $mainSalesStmt->execute([$mainStoreId]);
        $mainSalesData = $mainSalesStmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch satellite stores associated with the main store
        $satelliteStoresStmt = $pdo->prepare("SELECT store_id, location_name FROM stores WHERE store_name = ? AND location_type = 'satellite'");
        $satelliteStoresStmt->execute([$storeName]);
        $satelliteStoresData = $satelliteStoresStmt->fetchAll(PDO::FETCH_ASSOC);

        $satelliteSalesData = [];
        foreach ($satelliteStoresData as $satelliteStore) {
            $satelliteStoreId = $satelliteStore['store_id'];
            $satelliteLocationName = $satelliteStore['location_name'];

            // Fetch sales data for satellite stores associated with the main store
            $satelliteSalesStmt = $pdo->prepare("SELECT e.product_name, e.category, e.quantity_description, s.quantity_sold, s.total_price, s.record_date
                                                FROM sales s
                                                INNER JOIN main_entry e ON s.main_entry_id = e.main_entry_id
                                                WHERE s.store_id = ?");
            $satelliteSalesStmt->execute([$satelliteStoreId]);
            $satelliteSales = $satelliteSalesStmt->fetchAll(PDO::FETCH_ASSOC);

            $satelliteSalesData[$satelliteLocationName] = $satelliteSales;
        }

        // Sessionize the data
        $_SESSION['storeType'] = 'main_store'; // Matching session variable naming
        $_SESSION['mainstore_sales_data'] = $mainSalesData; // Matching session variable naming
        $_SESSION['satellite_sales_data'] = $satelliteSalesData; // Matching session variable naming

        // Redirect to viewsales2.php
        header("Location: viewsales2.php");
        exit();
    } else {
        echo "Main store not found.";
    }
} catch (PDOException $e) {
    // Log the error details
    error_log("Database Error: " . $e->getMessage(), 0);
    echo "An error occurred. Please try again later.";
    exit();
}
?>

