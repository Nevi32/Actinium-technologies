<?php
// Include database configuration
require_once 'config.php';

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve period from client side
    $period = $_POST['period'];

    // Define period interval and start date based on the period selected
    switch ($period) {
        case 'Daily':
            $interval = '1 DAY';
            $startDate = date('Y-m-d 00:00:00'); // Start of today
            break;
        case 'Weekly':
            $interval = '7 DAY';
            $startDate = date('Y-m-d 00:00:00', strtotime('-7 days')); // 7 days ago
            break;
        case 'Monthly':
            $interval = '30 DAY';
            $startDate = date('Y-m-d 00:00:00', strtotime('-1 month')); // 1 month ago
            break;
        case 'Halfannually':
            $interval = '6 MONTH';
            $startDate = date('Y-m-d 00:00:00', strtotime('-6 months')); // 6 months ago
            break;
        case 'Annually':
            $interval = '1 YEAR';
            $startDate = date('Y-m-d 00:00:00', strtotime('-1 year')); // 1 year ago
            break;
        default:
            // Invalid period
            $response = array(
                'success' => false,
                'error' => 'Invalid period.'
            );
            // Return error response
            header("HTTP/1.1 400 Bad Request");
            echo json_encode($response);
            exit();
    }

    try {
        // Create a new PDO instance
        $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Get current date from the database
        $currentDateStmt = $pdo->query("SELECT CURRENT_DATE()");
        $currentDate = $currentDateStmt->fetchColumn();

        // Initialize an array to store total profits for each store
        $totalProfits = array();

        // Start session
        session_start();

        // Check if user is logged in and get store information from session
        if (!isset($_SESSION['store_name'])) {
            // Redirect to login page if user is not logged in
            $response = array(
                'success' => false,
                'error' => 'Session expired. Please log in again.'
            );
            // Return error response
            header("Location: login.html?message=Session%20expired.%20Please%20log%20in%20again.&type=error");
            exit();
        }

        // Fetch store name from session
        $storeName = $_SESSION['store_name'];

        // Query stores table to check if the store is a main store or satellite store
        $storeStmt = $pdo->prepare("SELECT * FROM stores WHERE store_name = :store_name");
        $storeStmt->execute([':store_name' => $storeName]);
        $storeData = $storeStmt->fetch(PDO::FETCH_ASSOC);

        if (!$storeData) {
            // Store not found
            $response = array(
                'success' => false,
                'error' => 'Store not found.'
            );
            // Return error response
            header("HTTP/1.1 404 Not Found");
            echo json_encode($response);
            exit();
        }

        // Check if the store is a main store or satellite store
        if ($storeData['location_type'] === 'main_store') {
            // If main store, fetch satellite store IDs and locations
            $satelliteStoresStmt = $pdo->prepare("SELECT store_id, location_name FROM stores WHERE location_type = 'satellite'");
            $satelliteStoresStmt->execute();
            $satelliteStoresData = $satelliteStoresStmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // If satellite store, set satellite store ID and location
            $satelliteStoresData = array(
                array(
                    'store_id' => $storeData['store_id'],
                    'location_name' => $storeData['location_name']
                )
            );
        }

        // Iterate over each satellite store
        foreach ($satelliteStoresData as $satelliteStore) {
            // Fetch sales data for the period
            $salesStmt = $pdo->prepare("SELECT main_entry_id, quantity_sold FROM sales WHERE store_id = :store_id AND record_date >= DATE_SUB(:current_date, INTERVAL $interval)");
            $salesStmt->execute([
                ':store_id' => $satelliteStore['store_id'],
                ':current_date' => $currentDate
            ]);
            $salesData = $salesStmt->fetchAll(PDO::FETCH_ASSOC);

            // Initialize total profit for the current store
            $totalProfit = 0;

            // Iterate over each sale
            foreach ($salesData as $sale) {
                // Retrieve product information from main_entry table
                $productId = $sale['main_entry_id'];
                $quantitySold = $sale['quantity_sold'];

                // Query main_entry table to get product details
                $productStmt = $pdo->prepare("SELECT product_name, category FROM main_entry WHERE main_entry_id = :main_entry_id");
                $productStmt->execute([':main_entry_id' => $productId]);
                $productData = $productStmt->fetch(PDO::FETCH_ASSOC);

                if ($productData) {
                    $productName = $productData['product_name'];
                    $category = $productData['category'];

                    // Query prices table to get selling price and profit
                    $priceStmt = $pdo->prepare("SELECT profit FROM prices WHERE product_name = :product_name AND category = :category");
                    $priceStmt->execute([
                        ':product_name' => $productName,
                        ':category' => $category
                    ]);
                    $priceData = $priceStmt->fetch(PDO::FETCH_ASSOC);

                    if ($priceData) {
                        $profit = $priceData['profit'];

                        // Calculate profit for this sale
                        $saleProfit = $quantitySold * $profit;

                        // Add sale profit to total profit
                        $totalProfit += $saleProfit;
                    }
                }
            }

            // Add total profit for the current store to the array
            $totalProfits[$satelliteStore['location_name']] = $totalProfit;
        }

        // Sort total profits array by value (descending order)
        arsort($totalProfits);

        // Construct the report data
        $reportData = array(
            'period' => $period,
            'current_date' => $currentDate,
            'start_date' => $startDate,
            'store_name' => $storeName,
            'total_profits' => $totalProfits
        );

        // Encode the response as JSON and return it
        echo json_encode($reportData);
    } catch (PDOException $e) {
        // Log error and return error message
        error_log("Database Error: " . $e->getMessage(), 0);
        $response = array(
            'success' => false,
            'error' => 'An error occurred while fetching profit statistics. Please try again later.'
        );
        // Encode the error response as JSON and return it
        echo json_encode($response);
    }
} else {
    // Return error if request method is not POST
    $response = array(
        'success' => false,
        'error' => 'Invalid request method.'
    );
    // Encode the error response as JSON and return it
    echo json_encode($response);
}
?>

