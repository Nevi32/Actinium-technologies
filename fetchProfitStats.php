<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start session
        session_start();

        // Check if user is logged in and get store information from session
        if (!isset($_SESSION['store_name'])) {
            // Redirect to login page if user is not logged in
            header("Location: login.html?message=Session%20expired.%20Please%20log%20in%20again.&type=error");
            exit();
        }

        // Get store name from session
        $storeName = $_SESSION['store_name'];

        // Create a new PDO instance
        $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fetch main store information
        $mainStoreStmt = $pdo->prepare("SELECT store_id, location_name, location_type FROM stores WHERE store_name = ?");
        $mainStoreStmt->execute([$storeName]);
        $mainStoreData = $mainStoreStmt->fetch(PDO::FETCH_ASSOC);

        // Check if main store information is retrieved
        if (!$mainStoreData) {
            throw new Exception("Main store information not found.");
        }

        // Get main store ID, location, and store type
        $mainStoreId = $mainStoreData['store_id'];
        $mainLocationName = $mainStoreData['location_name'];
        $mainStoreType = $mainStoreData['location_type'];

        // Fetch satellite store information
        $satelliteStoresStmt = $pdo->prepare("SELECT store_id, location_name, location_type FROM stores WHERE location_type = 'satellite'");
        $satelliteStoresStmt->execute();
        $satelliteStoresData = $satelliteStoresStmt->fetchAll(PDO::FETCH_ASSOC);

        // Initialize array to store profit data for main store and satellite stores
        $profitData = array();

        // Function to calculate total selling price, buying price, and profit
        function calculateProfit($storeName, $storeId, $locationName, $storeType, $pdo) {
            // Fetch sales data for the day
            $salesStmt = $pdo->prepare("SELECT * FROM sales WHERE store_id = ? AND DATE(record_date) = CURDATE()");
            $salesStmt->execute([$storeId]);
            $salesData = $salesStmt->fetchAll(PDO::FETCH_ASSOC);

            // Initialize total selling price and buying price
            $totalSellingPrice = 0;
            $totalBuyingPrice = 0;

            // Construct the detailed report
            $report = array();
            foreach ($salesData as $sale) {
                // Retrieve product information
                $productId = $sale['main_entry_id'];
                $quantitySold = $sale['quantity_sold'];
                $totalPrice = $sale['total_price'];

                // Fetch product details
                $productStmt = $pdo->prepare("SELECT product_name, category FROM main_entry WHERE main_entry_id = ?");
                $productStmt->execute([$productId]);
                $productData = $productStmt->fetch(PDO::FETCH_ASSOC);

                if ($productData) {
                    // Retrieve product name and category
                    $productName = $productData['product_name'];
                    $category = $productData['category'];

                    // Fetch buying price for the latest price ID
                    $priceStmt = $pdo->prepare("SELECT buying_price FROM prices WHERE product_name = ? AND category = ? ORDER BY price_id DESC LIMIT 1");
                    $priceStmt->execute([$productName, $category]);
                    $priceData = $priceStmt->fetch(PDO::FETCH_ASSOC);

                    if ($priceData) {
                        // Calculate buying price
                        $buyingPrice = $priceData['buying_price'];
                        $totalBuyingPrice += $buyingPrice * $quantitySold;

                        // Add product details to the report
                        $report[] = array(
                            'Product Name' => $productName,
                            'Category' => $category,
                            'Quantity Sold' => $quantitySold,
                            'Total Price' => $totalPrice,
                            'Buying Price' => $buyingPrice
                        );
                    }

                    // Add total selling price
                    $totalSellingPrice += $totalPrice;
                }
            }

            // Calculate profit
            $profit = $totalSellingPrice - $totalBuyingPrice;

            return array(
                'Store Name' => $storeName,
                'Location' => $locationName,
                'Store Type' => $storeType,
                'Total Selling Price' => $totalSellingPrice,
                'Total Buying Price' => $totalBuyingPrice,
                'Profit' => $profit,
                'Sales Details' => $report
            );
        }

        // Calculate profit for main store
        $profitData[] = calculateProfit($storeName, $mainStoreId, $mainLocationName, $mainStoreType, $pdo);

        // Calculate profit for satellite stores
        foreach ($satelliteStoresData as $satelliteStore) {
            $profitData[] = calculateProfit($satelliteStore['store_name'], $satelliteStore['store_id'], $satelliteStore['location_name'], $satelliteStore['location_type'], $pdo);
        }

        // Encode the response as JSON and return it
        echo json_encode($profitData);
    } catch (Exception $e) {
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

