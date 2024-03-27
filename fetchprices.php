<?php
// Include the database credentials
include 'config.php';

// Start the session
session_start();

// Check if the user is logged in and has store information in the session
if (!isset($_SESSION['store_name'])) {
    echo json_encode(['error' => 'User not logged in or store name not set in session']);
    exit();
}

// Retrieve store name from the session
$storeName = $_SESSION['store_name'];

try {
    // Create a database connection
    $dsn = 'mysql:host=' . $databaseConfig['host'] . ';dbname=' . $databaseConfig['dbname'];
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $databaseConfig['user'], $databaseConfig['password'], $options);

    // Query the stores table to get the store ID of the main store based on store name
    $stmtStore = $pdo->prepare("SELECT store_id FROM stores WHERE store_name = ? AND location_type = 'main_store'");
    $stmtStore->execute([$storeName]);
    $store = $stmtStore->fetch();

    // Check if store ID is found
    if (!$store) {
        echo json_encode(['error' => 'Main store ID not found for the given store name']);
        exit();
    }

    $mainStoreId = $store['store_id'];

    // Fetch latest prices for each product and category combination specific to the main store
    $stmt = $pdo->prepare("SELECT * FROM prices p WHERE store_id = ? AND (p.product_name, p.category, p.set_date) IN (SELECT product_name, category, MAX(set_date) FROM prices WHERE store_id = ? GROUP BY product_name, category)");
    $stmt->execute([$mainStoreId, $mainStoreId]);
    $prices = $stmt->fetchAll();

    // Fetch dynamic prices for each price ID
    $dynamicPrices = [];
    foreach ($prices as $price) {
        $stmt = $pdo->prepare("SELECT * FROM dynamicprices WHERE price_id = ?");
        $stmt->execute([$price['price_id']]);
        $dynamicPrices[$price['price_id']] = $stmt->fetchAll();
    }

    // Prepare data for JSON response
    $response = [
        'mainprices' => $prices,
        'dynamicprices' => $dynamicPrices
    ];

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);

    // Close the database connection
    $pdo = null;
} catch (PDOException $e) {
    // Handle database errors
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>

