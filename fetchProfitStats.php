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

        // Fetch main store ID and store type using store name
        $mainStoreStmt = $pdo->prepare("SELECT store_id, location_type FROM stores WHERE store_name = ?");
        $mainStoreStmt->execute([$storeName]);
        $mainStoreData = $mainStoreStmt->fetch(PDO::FETCH_ASSOC);

        // Check if main store information is retrieved
        if (!$mainStoreData) {
            throw new Exception("Main store information not found.");
        }

        // Get main store ID and store type
        $mainStoreId = $mainStoreData['store_id'];
        $mainStoreType = $mainStoreData['location_type'];

        // Fetch store locations associated with the store name
        $storeLocationsStmt = $pdo->prepare("SELECT store_id, location_name FROM stores WHERE store_name = ?");
        $storeLocationsStmt->execute([$storeName]);
        $storeLocationsData = $storeLocationsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Initialize array to store profit data for main store and store locations
        $profitData = array();

        // Function to calculate profit
        function calculateProfit($storeName, $storeId, $locationName, $storeType, $pdo, $dateCondition) {
            // Fetch sales data for the specified period
            $salesStmt = $pdo->prepare("SELECT sales.*, main_entry.product_name, main_entry.category, prices.buying_price FROM sales JOIN main_entry ON sales.main_entry_id = main_entry.main_entry_id JOIN prices ON main_entry.product_name = prices.product_name AND main_entry.category = prices.category WHERE sales.store_id = ? AND $dateCondition");
            $salesStmt->execute([$storeId]);
            $salesData = $salesStmt->fetchAll(PDO::FETCH_ASSOC);

            // Initialize total selling price and buying price
            $totalSellingPrice = 0;
            $totalBuyingPrice = 0;

            // Construct the detailed report
            $report = array();
            foreach ($salesData as $sale) {
                // Retrieve product information
                $quantitySold = $sale['quantity_sold'];
                $totalPrice = $sale['total_price']; // Total price for the product, already calculated correctly
                $productName = $sale['product_name'];
                $category = $sale['category'];
                $buyingPrice = $sale['buying_price'];

                // Calculate buying price
                $totalBuyingPrice += $buyingPrice * $quantitySold;

                // Add product details to the report
                $report[] = array(
                    'Product Name' => $productName,
                    'Category' => $category,
                    'Quantity Sold' => $quantitySold,
                    'Total Price' => $totalPrice, // Use the total price retrieved from the database
                    'Buying Price' => $buyingPrice
                );

                // Add total selling price
                $totalSellingPrice += $totalPrice; // Add the total price of this product to the total selling price
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

        // Define date condition based on the period
        switch ($_POST['period']) {
            case 'Daily':
                $dateCondition = "DATE(record_date) = CURDATE()";
                break;
            case 'Weekly':
                $dateCondition = "DATE(record_date) >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND DATE(record_date) <= CURDATE()";
                break;
            case 'Monthly':
                $dateCondition = "DATE(record_date) >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) AND DATE(record_date) <= CURDATE()";
                break;
            case 'Halfannually':
                $dateCondition = "DATE(record_date) >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND DATE(record_date) <= CURDATE()";
                break;
            case 'Annually':
                $dateCondition = "DATE(record_date) >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR) AND DATE(record_date) <= CURDATE()";
                break;
            default:
                throw new Exception("Invalid period specified.");
                break;
        }

        // Calculate profit for main store and store locations
        foreach ($storeLocationsData as $location) {
            $profitData[] = calculateProfit($storeName, $location['store_id'], $location['location_name'], $mainStoreType, $pdo, $dateCondition);
        }

        // Encode the response as JSON and return it
        echo json_encode($profitData);
    } catch (Exception $e) {
        // Log error and return error message
        echo json_encode(array('error' => $e->getMessage()));
    }
}
?>

