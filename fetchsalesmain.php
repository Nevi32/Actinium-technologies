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

    // Fetch sales data for the main store
    $mainSalesStmt = $pdo->prepare("SELECT e.product_name, s.quantity_sold, s.total_price, s.record_date
                                    FROM sales s
                                    INNER JOIN main_entry e ON s.main_entry_id = e.main_entry_id
                                    WHERE s.store_id IN (SELECT store_id FROM stores WHERE location_type = 'main_store')");
    $mainSalesStmt->execute();
    $mainSalesData = $mainSalesStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch sales data for satellite stores
    $satelliteSalesStmt = $pdo->prepare("SELECT e.product_name, s.quantity_sold, s.total_price, s.record_date, st.location_name
                                        FROM sales s
                                        INNER JOIN main_entry e ON s.main_entry_id = e.main_entry_id
                                        INNER JOIN stores st ON s.store_id = st.store_id
                                        WHERE st.location_type = 'satellite'");
    $satelliteSalesStmt->execute();
    $satelliteSalesData = $satelliteSalesStmt->fetchAll(PDO::FETCH_ASSOC);

    // Group satellite sales data by location name
    $groupedSatelliteSalesData = [];
    foreach ($satelliteSalesData as $sale) {
        $locationName = $sale['location_name'];
        unset($sale['location_name']); // Remove location name from the sale data
        $groupedSatelliteSalesData[$locationName][] = $sale;
    }

    // Sessionize the data
    $_SESSION['storeType'] = 'main_store'; // Matching session variable naming
    $_SESSION['mainstore_sales_data'] = $mainSalesData; // Matching session variable naming
    $_SESSION['satellite_sales_data'] = $groupedSatelliteSalesData; // Matching session variable naming

    // Redirect to viewsales2.php
    header("Location: viewsales2.php");
    exit();
} catch (PDOException $e) {
    // Log the error details
    error_log("Database Error: " . $e->getMessage(), 0);
    echo "An error occurred. Please try again later.";
    exit();
}
?>

