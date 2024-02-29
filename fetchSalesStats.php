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

// Get the period from the POST request
$period = $_POST['period'];

// Echo the period fetched from the client side
echo "Period fetched from client side: $period";

// Initialize an array to store the sales report
$salesReport = array();

// Connect to the database
try {
    $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Define the start and end dates based on the period
    $startDate = '';
    $endDate = date('Y-m-d H:i:s'); // Current date and time

    switch ($period) {
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

    // Fetch total sales made by this store within the period
    $stmtTotalSales = $pdo->prepare("SELECT SUM(total_price) AS total_sales FROM sales WHERE store_id IN (SELECT store_id FROM stores WHERE store_name = ?) AND DATE(record_date) >= ? AND DATE(record_date) <= ?");
    $stmtTotalSales->execute([$storeName, $startDate, $endDate]);
    $totalSales = $stmtTotalSales->fetch(PDO::FETCH_ASSOC);

    // Add total sales to the report
    $salesReport["total_sales"] = $totalSales['total_sales'];

    // Fetch total sales for each store (main store and satellite stores)
    $stmtStoreSales = $pdo->prepare("SELECT stores.location_name, SUM(total_price) AS total_sales FROM sales JOIN stores ON sales.store_id = stores.store_id WHERE DATE(record_date) >= ? AND DATE(record_date) <= ? GROUP BY stores.location_name");
    $stmtStoreSales->execute([$startDate, $endDate]);
    $storeSales = $stmtStoreSales->fetchAll(PDO::FETCH_ASSOC);

    // Add total sales for each store to the report
    $salesReport["store_sales"] = $storeSales;

    // Fetch total sales for each product on the main_entry table of this store
    $stmtProductSales = $pdo->prepare("SELECT main_entry.product_name, main_entry.category, SUM(sales.total_price) AS total_price, stores.location_name FROM sales JOIN main_entry ON sales.main_entry_id = main_entry.main_entry_id JOIN stores ON sales.store_id = stores.store_id WHERE main_entry.store_id IN (SELECT store_id FROM stores WHERE store_name = ?) AND DATE(sales.record_date) >= ? AND DATE(sales.record_date) <= ? GROUP BY main_entry.product_name, main_entry.category, stores.location_name");
    $stmtProductSales->execute([$storeName, $startDate, $endDate]);
    $productSales = $stmtProductSales->fetchAll(PDO::FETCH_ASSOC);

    // Add total sales for each product to the report
    $salesReport["product_sales"] = $productSales;

    // Prepare the response
    $response = array(
        'success' => true,
        'message' => "{$storeName} {$period} sales report",
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

