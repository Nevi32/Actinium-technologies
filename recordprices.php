<?php
// Include database configuration
include('config.php');

// Create a PDO connection using the provided configuration
try {
    $dsn = "mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}";
    $db = new PDO($dsn, $databaseConfig['user'], $databaseConfig['password']);
    // Set PDO to throw exceptions on error
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle connection errors
    echo "Connection failed: " . $e->getMessage();
    exit();
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve data from the POST request
    $formData = $_POST;

    // Initialize an array to store the product prices
    $productPrices = [];

    // Iterate over the form data to extract product prices
    foreach ($formData as $key => $value) {
        // Check if the key contains 'sellingPrice'
        if (strpos($key, 'sellingPrice') !== false) {
            // Extract product name and index from the key
            $keyParts = explode('_', $key);
            $productName = $keyParts[0];
            $index = $keyParts[count($keyParts) - 1];

            // Check if the product name is not already in the productPrices array
            if (!isset($productPrices[$productName])) {
                $productPrices[$productName] = [
                    'sellingPrices' => [],
                    'dynamicPricing' => false, // Default to false
                    'category' => $formData[$productName . '_category'], // Retrieve category
                    'buyingPrice' => $formData[$productName . '_buyingPrice'] // Retrieve buying price
                ];
            }

            // Add the selling price to the productPrices array
            $productPrices[$productName]['sellingPrices'][$index] = $value;
        }

        // Check if the key contains 'dynamicPrices'
        if (strpos($key, 'dynamicPrices') !== false) {
            // Extract product name from the key
            $productName = explode('_', $key)[0];

            // Update the dynamicPricing value in the productPrices array
            $productPrices[$productName]['dynamicPricing'] = ($value === 'true');
        }
    }

    // Process the product prices array and update the prices in the database
    foreach ($productPrices as $productName => $priceData) {
        // Check if dynamic pricing is enabled
        if ($priceData['dynamicPricing']) {
            // Fetch product information from the database based on product name and category
            $productQuery = "SELECT price_id FROM prices WHERE product_name = :productName AND category = :category";
            $productParams = [':productName' => $productName, ':category' => $priceData['category']];
            $productResult = $db->prepare($productQuery);
            $productResult->execute($productParams);

            $productRow = $productResult->fetch(PDO::FETCH_ASSOC);

            if (!$productRow) {
                // Handle the case where the product does not exist
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Product not found in the database']);
                exit();
            }

            $priceId = $productRow['price_id'];

            // Update dynamic selling prices, profits, and percentage profits for the product
            // Here you need to implement the logic to update the dynamic prices in the database
            foreach ($priceData['sellingPrices'] as $sellingPrice) {
                // Perform necessary calculations and database updates
                // For example:
                // $dynamicSellingPrice = calculateDynamicSellingPrice($sellingPrice);
                // $dynamicProfit = calculateDynamicProfit($dynamicSellingPrice, $priceData['buyingPrice']);
                // $dynamicPercentageProfit = calculatePercentageProfit($dynamicProfit, $priceData['buyingPrice']);
                // updateDynamicPrices($priceId, $dynamicSellingPrice, $dynamicProfit, $dynamicPercentageProfit);
                $dynamicPricing = 1; // Set dynamic pricing status to 1
                $insertQuery = "INSERT INTO prices (dynamic_selling_price, dynamic_profit, dynamic_percentage_profit, product_name, category, dynamic_pricing) VALUES (?, ?, ?, ?, ?, ?)";
                $insertStatement = $db->prepare($insertQuery);
                $buyingPrice = $priceData['buyingPrice']; // Retrieve buying price
                $profit = $sellingPrice - $buyingPrice;
                $percentageProfit = ($profit / $buyingPrice) * 100;
                $insertStatement->execute([$sellingPrice, $profit, $percentageProfit, $productName, $priceData['category'], $dynamicPricing]);
            }
        } else {
            // Update a single selling price, profit, and percentage profit for the product
            // You need to implement updateSinglePrice function
            // updateSinglePrice($productId, $priceData['sellingPrices'][0]);
            // In this case, we'll simply log the new selling prices for each product
            $insertQuery = "INSERT INTO prices (selling_price, buying_price, profit, percentage_profit, product_name, category, dynamic_pricing) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $insertStatement = $db->prepare($insertQuery);

            foreach ($priceData['sellingPrices'] as $sellingPrice) {
                $buyingPrice = $priceData['buyingPrice']; // Retrieve buying price
                $profit = $sellingPrice - $buyingPrice;
                $percentageProfit = ($profit / $buyingPrice) * 100;
                $dynamicPricing = 0; // Dynamic pricing not enabled
                $insertStatement->execute([$sellingPrice, $buyingPrice, $profit, $percentageProfit, $productName, $priceData['category'], $dynamicPricing]);
            }
        }
    }

    // Respond with success message
    echo json_encode(['status' => 'success', 'message' => 'Prices updated successfully']);
} else {
    // Handle the case where the request method is not POST
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
}
?>

