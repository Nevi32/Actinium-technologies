<?php
session_start(); // Start the session

require_once 'config.php'; // Include the database configuration

// Check if the user is logged in and has a store name in the session
if (!isset($_SESSION['store_name'])) {
    // If the store name is not set, return an error response
    echo json_encode(array('error' => 'User not logged in or store name not set in session'));
    exit();
}

// Get the store name from the session
$storeName = $_SESSION['store_name'];

try {
    $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query the stores table to get the store IDs related to the store name
    $stmtStoreIds = $pdo->prepare("SELECT store_id FROM stores WHERE store_name = ?");
    $stmtStoreIds->execute([$storeName]);
    $storeIds = $stmtStoreIds->fetchAll(PDO::FETCH_COLUMN);

    // Define the start and end dates based on the period
    $startDate = '';
    $endDate = date('Y-m-d H:i:s'); // Current date and time

    // Set the date filter based on the period (client side)
    switch ($_POST['period']) {
        case 'Daily':
            $startDate = date('Y-m-d 00:00:00'); // Start of today
            break;
        case 'Weekly':
            $startDate = date('Y-m-d 00:00:00', strtotime('-7 days')); // 7 days ago
            break;
        case 'Monthly':
            $startDate = date('Y-m-d 00:00:00', strtotime('-1 month')); // 1 month ago
            break;
        default:
            // Handle invalid period
            break;
    }

    // Initialize sales report
    $salesReport = array(
        'total_sales' => 0,
        'total_quantity' => 0,
        'store_sales' => array(),
        'product_sales' => array()
    );

    // Fetch total sales made by Lucys Duka store within the period
    foreach ($storeIds as $storeId) {
        $stmtTotalSales = $pdo->prepare("SELECT SUM(total_price) AS total_sales, SUM(quantity_sold) AS total_quantity FROM sales WHERE store_id = ? AND DATE(record_date) >= ? AND DATE(record_date) <= ?");
        $stmtTotalSales->execute([$storeId, $startDate, $endDate]);
        $totalSales = $stmtTotalSales->fetch(PDO::FETCH_ASSOC);
        
        // Add total sales and total quantity to the report
        $salesReport["total_sales"] += (float)$totalSales['total_sales'];
        $salesReport["total_quantity"] += (float)$totalSales['total_quantity'];

        // Fetch total sales for each store related to Lucys Duka
        $stmtStoreSales = $pdo->prepare("SELECT location_name, SUM(total_price) AS total_sales, SUM(quantity_sold) AS total_quantity FROM sales JOIN stores ON sales.store_id = stores.store_id WHERE stores.store_id = ? AND DATE(sales.record_date) >= ? AND DATE(sales.record_date) <= ? GROUP BY stores.location_name");
        $stmtStoreSales->execute([$storeId, $startDate, $endDate]);
        $storeSales = $stmtStoreSales->fetchAll(PDO::FETCH_ASSOC);

        // Add total sales for each store to the report
        $salesReport["store_sales"] = array_merge($salesReport["store_sales"], $storeSales);

        // Fetch total sales for each product related to Lucys Duka
        $stmtProductSales = $pdo->prepare("SELECT main_entry.product_name, main_entry.category, SUM(sales.total_price) AS total_price, SUM(sales.quantity_sold) AS total_quantity, stores.location_name FROM sales JOIN main_entry ON sales.main_entry_id = main_entry.main_entry_id JOIN stores ON sales.store_id = stores.store_id WHERE main_entry.store_id = ? AND DATE(sales.record_date) >= ? AND DATE(sales.record_date) <= ? GROUP BY main_entry.product_name, main_entry.category, stores.location_name");
        $stmtProductSales->execute([$storeId, $startDate, $endDate]);
        $productSales = $stmtProductSales->fetchAll(PDO::FETCH_ASSOC);

        // Add total sales for each product to the report
        $salesReport["product_sales"] = array_merge($salesReport["product_sales"], $productSales);
    }

    // Prepare the response
    $response = array(
        'success' => true,
        'message' => "{$storeName} {$_POST['period']} sales report",
        'data' => $salesReport
    );

    // Encode the response as JSON and return it
    echo json_encode($response, JSON_PRETTY_PRINT);
} catch (PDOException $e) {
    // Handle database errors
    $response = array(
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    );

    // Encode the error response as JSON and return it
    echo json_encode($response);
}
?>
