<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Start the session
    session_start();

    // Check if the user is logged in and has store information in the session
    if (!isset($_SESSION['store_name']) || !isset($_SESSION['location_name'])) {
        echo json_encode(['status' => 'error', 'message' => 'User not logged in or store information not set in session']);
        exit();
    }

    // Retrieve store name and location from the session
    $storeName = $_SESSION['store_name'];
    $locationName = $_SESSION['location_name'];

    try {
        $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Query the stores table to get the store ID based on store name and location
        $stmtStore = $pdo->prepare("SELECT store_id FROM stores WHERE store_name = ? AND location_name = ?");
        $stmtStore->execute([$storeName, $locationName]);
        $store = $stmtStore->fetch(PDO::FETCH_ASSOC);

        if (!$store) {
            echo json_encode(['status' => 'error', 'message' => 'Store not found']);
            exit();
        }

        $storeId = $store['store_id'];

        // Decode the JSON data sent from the client
        $requestData = json_decode(file_get_contents('php://input'), true);

        // Check if data is present and is an array
        if (!empty($requestData) && is_array($requestData)) {
            // Iterate over each product data
            foreach ($requestData as $productData) {
                // Extract product data
                $productName = $productData['productName'];
                $category = $productData['category'];
                $buyingPrice = $productData['buyingPrice'];
                $sellingPrice = $productData['sellingPrice'];
                $profit = $productData['profit'];
                $percentageProfit = $productData['percentageProfit'];
                $dynamicPrices = $productData['dynamicPrices'];

                // Check if dynamic pricing is enabled
                if ($dynamicPrices == 0) {
                    // Insert data into the prices table with store ID
                    $stmt = $pdo->prepare("INSERT INTO prices (store_id, product_name, category, selling_price, buying_price, profit, percentage_profit) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$storeId, $productName, $category, $sellingPrice, $buyingPrice, $profit, $percentageProfit]);
                } elseif ($dynamicPrices == 1) {
                    // Fetch latest price ID from the prices table for the specific store, product, and category
                    $stmt = $pdo->prepare("SELECT price_id FROM prices WHERE product_name = ? AND category = ? AND store_id = ? ORDER BY price_id DESC LIMIT 1");
                    $stmt->execute([$productName, $category, $storeId]);
                    $priceId = $stmt->fetchColumn();

                    // Insert data into the dynamicprices table
                    $stmt = $pdo->prepare("INSERT INTO dynamicprices (price_id, selling_price, profit, percentage_profit) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$priceId, $sellingPrice, $profit, $percentageProfit]);
                } else {
                    // Invalid dynamicPrices value
                    echo json_encode(['status' => 'error', 'message' => 'Invalid dynamicPrices value']);
                    exit; // Stop further processing
                }
            }

            // All products processed successfully
            echo json_encode(['status' => 'success', 'message' => 'Prices recorded successfully']);
        } else {
            // No data sent or invalid data format
            echo json_encode(['status' => 'error', 'message' => 'No valid data received']);
        }
    } catch (PDOException $e) {
        // Handle database errors
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    // Handle invalid request method
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>

