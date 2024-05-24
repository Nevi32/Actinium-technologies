<?php
include 'config.php';
session_start();

try {
    $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Retrieve session data
    $storeName = $_SESSION['store_name'] ?? null;
    $locationName = $_SESSION['location_name'] ?? null;

    if (!$storeName || !$locationName) {
        echo "Store name or location not found in session.";
        exit();
    }

    // Fetch store information based on store name and location
    $storeStmt = $pdo->prepare("SELECT * FROM stores WHERE store_name = :storeName AND location_name = :locationName");
    $storeStmt->bindParam(':storeName', $storeName);
    $storeStmt->bindParam(':locationName', $locationName);
    $storeStmt->execute();
    $store = $storeStmt->fetch(PDO::FETCH_ASSOC);

    if (!$store) {
        echo "Store not found.";
        exit();
    }

    // Determine if the store is a main_store or satellite
    $storeType = $store['location_type'];

    // Construct the SQL query based on store type
    if ($storeType === 'main_store') {
        // Fetch sales data for the main store and its satellites
        $salesQuery = "SELECT s.sale_id, s.main_entry_id, s.quantity_sold, s.total_price, s.record_date,
                              m.product_name, m.total_quantity, m.quantity_description
                       FROM sales s
                       LEFT JOIN main_entry m ON s.main_entry_id = m.main_entry_id
                       WHERE s.store_id = :storeId OR s.store_id IN 
                             (SELECT store_id FROM stores WHERE location_type = 'satellite' AND location_name = :storeName)";

        // Prepare and execute the sales query
        $salesStmt = $pdo->prepare($salesQuery);
        $salesStmt->bindParam(':storeId', $store['store_id']);
        $salesStmt->bindParam(':storeName', $storeName);
        $salesStmt->execute();
        
        // Fetching sales data along with sale date
        $salesData = $salesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Iterating over sales data to format date
        foreach ($salesData as &$sale) {
            // Formatting the sale date
            $sale['record_date'] = date('Y-m-d H:i:s', strtotime($sale['record_date']));
        }

        // Storing sales data in session
        $_SESSION['sales_data'] = $salesData;

        // Redirect to viewsales2.php for main stores
        header('Location: viewsales2.php');
        exit();
    } elseif ($storeType === 'satellite') {
        // Fetch sales data for the satellite store
        $salesQuery = "SELECT s.sale_id, s.main_entry_id, s.quantity_sold, s.total_price, s.record_date, m.category, 
                              m.product_name, m.total_quantity, m.quantity_description
                       FROM sales s
                       LEFT JOIN main_entry m ON s.main_entry_id = m.main_entry_id
                       WHERE s.store_id = :storeId OR s.store_id = 
                             (SELECT store_id FROM stores WHERE location_type = 'main_store' AND location_name = :mainStoreName)";

        // Prepare and execute the sales query
        $salesStmt = $pdo->prepare($salesQuery);
        $salesStmt->bindParam(':storeId', $store['store_id']);
        $salesStmt->bindParam(':mainStoreName', $storeName);
        $salesStmt->execute();
        
        // Fetching sales data along with sale date
        $salesData = $salesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Iterating over sales data to format date
        foreach ($salesData as &$sale) {
            // Formatting the sale date
            $sale['record_date'] = date('Y-m-d H:i:s', strtotime($sale['record_date']));
        }

        // Storing sales data in session
        $_SESSION['sales_data'] = $salesData;

        // Redirect to viewsales.php for satellite stores
        header('Location: viewsales.php');
        exit();
    } else {
        echo "Invalid store type.";
        exit();
    }
} catch (PDOException $e) {
    echo "Error fetching sales data: " . $e->getMessage();
}
?>

