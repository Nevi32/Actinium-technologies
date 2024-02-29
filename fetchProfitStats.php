<?php
// Include database configuration
require_once 'config.php';

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve period from client side
    $period = $_POST['period'];

    // Define period interval based on the period selected
    switch ($period) {
        case 'Daily':
            $interval = '1 DAY';
            break;
        case 'Weekly':
            $interval = '7 DAY';
            break;
        case 'Monthly':
            $interval = '30 DAY';
            break;
        case 'Halfannually':
            $interval = '6 MONTH';
            break;
        case 'Annually':
            $interval = '1 YEAR';
            break;
        default:
            // Invalid period
            header("HTTP/1.1 400 Bad Request");
            echo json_encode(array('error' => 'Invalid period.'));
            exit();
    }

    try {
        // Create a new PDO instance
        $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Start session
        session_start();

        // Check if user is logged in and get store information from session
        if (!isset($_SESSION['store_name'])) {
            // Redirect to login page if user is not logged in
            header("Location: login.html?message=Session%20expired.%20Please%20log%20in%20again.&type=error");
            exit();
        }

        // Fetch store name from session
        $storeName = $_SESSION['store_name'];

        // Query stores table to check if there are satellite stores
        $storeStmt = $pdo->prepare("SELECT * FROM stores WHERE store_name = :store_name");
        $storeStmt->execute([':store_name' => $storeName]);
        $storeData = $storeStmt->fetch(PDO::FETCH_ASSOC);

        // Check if store has satellite stores
        if ($storeData && $storeData['location_type'] === 'main_store') {
            // If main store, fetch satellite store IDs and locations
            $satelliteStoresStmt = $pdo->prepare("SELECT store_id, location_name FROM stores WHERE main_store_id = :main_store_id");
            $satelliteStoresStmt->execute([':main_store_id' => $storeData['store_id']]);
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

        // Get current date from the database
        $currentDateStmt = $pdo->query("SELECT CURRENT_DATE()");
        $currentDate = $currentDateStmt->fetchColumn();

        // Initialize total profit
        $totalProfit = 0;

        // Iterate over each satellite store
        foreach ($satelliteStoresData as $satelliteStore) {
            // Fetch sales data for the period
            $salesStmt = $pdo->prepare("SELECT main_entry_id, quantity_sold FROM sales WHERE store_id = :store_id AND record_date >= DATE_SUB(:current_date, INTERVAL $interval)");
            $salesStmt->execute([
                ':store_id' => $satelliteStore['store_id'],
                ':current_date' => $currentDate
            ]);
            $salesData = $salesStmt->fetchAll(PDO::FETCH_ASSOC);

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
                    $priceStmt = $pdo->prepare("SELECT price_id, selling_price, profit FROM prices WHERE product_name = :product_name AND category = :category");
                    $priceStmt->execute([
                        ':product_name' => $productName,
                        ':category' => $category
                    ]);
                    $priceData = $priceStmt->fetch(PDO::FETCH_ASSOC);

                    if ($priceData) {
                        $priceId = $priceData['price_id'];
                        $sellingPrice = $priceData['selling_price'];
                        $profit = $priceData['profit'];

                        // Calculate profit for this sale
                        $saleProfit = $quantitySold * $profit;

                        // Add sale profit to total profit
                        $totalProfit += $saleProfit;

                        // Query dynamicprices table for alternative prices (if any)
                        $dynamicPriceStmt = $pdo->prepare("SELECT selling_price, profit FROM dynamicprices WHERE price_id = :price_id");
                        $dynamicPriceStmt->execute([':price_id' => $priceId]);
                        $dynamicPriceData = $dynamicPriceStmt->fetch(PDO::FETCH_ASSOC);

                        if ($dynamicPriceData) {
                            $dynamicSellingPrice = $dynamicPriceData['selling_price'];
                            $dynamicProfit = $dynamicPriceData['profit'];

                            // Calculate profit for this sale using dynamic price
                            $dynamicSaleProfit = $quantitySold * $dynamicProfit;

                            // Add dynamic sale profit to total profit
                            $totalProfit += $dynamicSaleProfit;
                        }
                    }
                }
            }
        }

        // Construct the report data
        $reportData = array(
            'store_name' => $storeName,
            'total_profit' => $totalProfit
        );

        // Return report data in JSON format
        header('Content-Type: application/json');
        echo json_encode($reportData);
    } catch (PDOException $e) {
        // Log error and return error message
        error_log("Database Error: " . $e->getMessage(), 0);
        header("HTTP/1.1 500 Internal Server Error");
        echo json_encode(array('error' => 'An error occurred while fetching profit statistics. Please try again later.'));
    }
} else {
    // Return error if request method is not POST
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(array('error' => 'Invalid request method.'));
}
?>

