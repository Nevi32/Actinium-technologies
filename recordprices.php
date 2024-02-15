<?php
include('config.php');
include('pbo.php');

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
        // Check if the product name and category are set
        if (!isset($priceData['productName'], $priceData['category'])) {
            // Handle the case where product name or category is not set
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Product name or category not provided']);
            exit();
        }

        // Fetch product information from the database based on product name and category
        $productQuery = "SELECT product_id FROM products WHERE product_name = :productName AND category = :category";
        $productParams = [':productName' => $priceData['productName'], ':category' => $priceData['category']];
        $productResult = $db->query($productQuery, $productParams);

        if (!$productResult) {
            // Handle the case where the product does not exist
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Product not found in the database']);
            exit();
        }

        $productRow = $productResult->fetch(PDO::FETCH_ASSOC);
        $productId = $productRow['product_id'];

        // Check if dynamic pricing is enabled
        if ($priceData['dynamicPricing']) {
            // Update multiple selling prices, profits, and percentage profits for the product
            updateDynamicPrices($productId, $priceData['sellingPrices']);
        } else {
            // Update a single selling price, profit, and percentage profit for the product
            updateSinglePrice($productId, $priceData['sellingPrices'][0]);
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

