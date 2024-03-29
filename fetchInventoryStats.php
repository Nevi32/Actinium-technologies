<?php
session_start(); // Start the session

require_once 'config.php'; // Include the database configuration

// Check if the user is logged in and has a store name in the session
if (!isset($_SESSION['store_name'])) {
    // If the store name is not set, return an error response
    echo json_encode(array('error' => 'User not logged in or store name not set in session'));
    exit();
}

// Get the store name from the session
$storeName = $_SESSION['store_name'];

// Get the period from the POST request
$period = $_POST['period'];

// Initialize an array to store the inventory report
$inventoryReport = array();

// Connect to the database
try {
    $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch inventory data for the main store
    $stmtMain = $pdo->prepare("SELECT product_name, category, total_quantity, quantity_description FROM main_entry WHERE store_id IN (SELECT store_id FROM stores WHERE store_name = ? AND location_type = 'main_store')");
    $stmtMain->execute([$storeName]);
    $mainStoreInventory = $stmtMain->fetchAll(PDO::FETCH_ASSOC);

    // Add main store inventory to the report
    $inventoryReport = $mainStoreInventory;

    // Set the date filter based on the period
    $dateFilter = "";
    if ($period === "daily") {
        $dateFilter = "AND DATE(record_date) = CURDATE()";
    } elseif ($period === "weekly") {
        $dateFilter = "AND record_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    } elseif ($period === "monthly") {
        $dateFilter = "AND record_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
    }

    // Fetch new entries from the inventory table based on the period
    $stmtNewEntries = $pdo->prepare("SELECT main_entry.product_name, main_entry.category, inventory.quantity, main_entry.quantity_description FROM inventory JOIN main_entry ON inventory.main_entry_id = main_entry.main_entry_id JOIN stores ON main_entry.store_id = stores.store_id WHERE main_entry.store_id IN (SELECT store_id FROM stores WHERE store_name = ?) $dateFilter");
    $stmtNewEntries->execute([$storeName]);
    $newEntries = $stmtNewEntries->fetchAll(PDO::FETCH_ASSOC);

    // Merge new entries into the main store inventory
    $inventoryReport = array_merge($inventoryReport, $newEntries);

    // Prepare the response
    $response = array(
        'success' => true,
        'message' => "{$storeName} {$period} inventory report",
        'data' => $inventoryReport
    );

    // Encode the response as JSON and return it
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    // Handle database errors
    $response = array(
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    );

    // Encode the error response as JSON and return it
    echo json_encode($response);
}
?>

