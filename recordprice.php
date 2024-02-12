<?php
// Include the configuration file
require_once 'config.php';

// Include the PBO libraries
// Assuming you have PBO libraries included here

// Start session to access session variables set in StoreResouces.php
session_start();

// Check if user is logged in and session variables are set
if (!isset($_SESSION['user_id'])) {
    // Redirect user to login page if not logged in
    header("Location: login.html");
    exit();
}

// Get store name and location from session
$storeName = $_SESSION['store_name'];
$storeLocation = $_SESSION['store_location'];

// Connect to the database
try {
    $db = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get store_id from stores table
try {
    $stmt = $db->prepare("SELECT store_id FROM stores WHERE store_name = :storeName AND location_name = :storeLocation");
    $stmt->bindParam(':storeName', $storeName);
    $stmt->bindParam(':storeLocation', $storeLocation);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $storeId = $row['store_id'];
} catch (PDOException $e) {
    die("Error retrieving store information: " . $e->getMessage());
}

// Check if dynamic pricing is selected
$dynamicPricing = isset($_POST['dynamicPrices']) && $_POST['dynamicPrices'] == 'on';

// Prepare SQL statement to insert price records
try {
    $stmt = $db->prepare("INSERT INTO prices (store_id, product_name, category, buying_price, selling_price, profit, percentage_profit, dynamic_pricing)
                          VALUES (:store_id, :product_name, :category, :buying_price, :selling_price, :profit, :percentage_profit, :dynamic_pricing)");
    $stmt->bindParam(':store_id', $storeId);
    $stmt->bindParam(':product_name', $productName);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':buying_price', $buyingPrice);
    $stmt->bindParam(':selling_price', $sellingPrice);
    $stmt->bindParam(':profit', $profit);
    $stmt->bindParam(':percentage_profit', $percentageProfit);
    $stmt->bindParam(':dynamic_pricing', $dynamicPricing);

    // Loop through posted data and insert records
    foreach ($_POST['product'] as $product) {
        $productName = $product['name'];
        $category = $product['category'];
        $buyingPrice = $product['buyingPrice'];
        $sellingPrice = $dynamicPricing ? $product['sellingPrice'] : $_POST['sellingPrice'];
        $profit = $dynamicPricing ? $product['profit'] : $_POST['profit'];
        $percentageProfit = $dynamicPricing ? $product['percentageProfit'] : $_POST['percentageProfit'];
        
        // Execute the prepared statement
        $stmt->execute();
    }

    // Send success response back to StoreResources.php
    $response = [
        'status' => 'success',
        'message' => 'Prices recorded successfully'
    ];
    echo json_encode($response);
} catch (PDOException $e) {
    // Send error response back to StoreResources.php in case of failure
    $response = [
        'status' => 'error',
        'message' => 'Error recording prices: ' . $e->getMessage()
    ];
    echo json_encode($response);
}
?>

