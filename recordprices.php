<?php
// Include the database connection configuration
require_once 'config.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

            try {
                // Connect to the database
                $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Check if dynamic pricing is enabled
                if ($dynamicPrices == 0) {
                    // Insert data into the prices table
                    $stmt = $pdo->prepare("INSERT INTO prices (product_name, category, selling_price, buying_price, profit, percentage_profit) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$productName, $category, $sellingPrice, $buyingPrice, $profit, $percentageProfit]);
                } else {
                    // Fetch price ID from the prices table using product name and category
                    $stmt = $pdo->prepare("SELECT price_id FROM prices WHERE product_name = ? AND category = ?");
                    $stmt->execute([$productName, $category]);
                    $priceId = $stmt->fetchColumn();

                    // Check if price ID was found
                    if ($priceId) {
                        // Insert data into the dynamicprices table
                        $stmt = $pdo->prepare("INSERT INTO dynamicprices (price_id, selling_price, profit, percentage_profit) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$priceId, $sellingPrice, $profit, $percentageProfit]);
                    } else {
                        // Product not found in the prices table
                        echo json_encode(['status' => 'error', 'message' => 'Product not found in the prices table']);
                    }
                }

                // Close the database connection
                $pdo = null;
            } catch (PDOException $e) {
                // Handle database errors
                echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
            }
        }

        // All products processed successfully
        echo json_encode(['status' => 'success', 'message' => 'Prices recorded successfully']);
    } else {
        // No data sent or invalid data format
        echo json_encode(['status' => 'error', 'message' => 'No valid data received']);
    }
} else {
    // Handle invalid request method
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>

