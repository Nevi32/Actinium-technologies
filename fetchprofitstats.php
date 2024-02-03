<?php
// Include configuration file
require_once 'config.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Database connection
try {
    $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch main store ID and satellite store IDs associated with the user's store
    $stmt = $pdo->prepare("SELECT store_id FROM stores WHERE location_type = 'main_store' AND store_name = ?");
    $stmt->execute([$_SESSION['store_name']]);
    $mainStoreId = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT store_id, location_name FROM stores WHERE location_type = 'satellite' AND main_store_id = ?");
    $stmt->execute([$mainStoreId]);
    $satelliteStores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate profits for main store
    $mainStoreProfit = calculateProfit($mainStoreId, $pdo);

    // Calculate profits for satellite stores
    $satelliteStoreProfits = [];
    foreach ($satelliteStores as $satelliteStore) {
        $satelliteStoreProfits[$satelliteStore['location_name']] = calculateProfit($satelliteStore['store_id'], $pdo);
    }

    // Assemble profit stats data
    $profitStats = [
        'main_store_profit' => $mainStoreProfit,
        'satellite_store_profits' => $satelliteStoreProfits
    ];

    // Return profit stats data in JSON format
    echo json_encode($profitStats);
} catch (PDOException $e) {
    // Log the error details
    error_log("Database Error: " . $e->getMessage(), 0);
    echo json_encode(['error' => 'An error occurred. Please try again later.']);
}

// Function to calculate profit
function calculateProfit($storeId, $pdo) {
    $totalProfit = 0;

    // Fetch all sales associated with the store
    $stmt = $pdo->prepare("SELECT sales.quantity_sold, sales.total_price, inventory.price
                           FROM sales
                           INNER JOIN inventory ON sales.main_entry_id = inventory.main_entry_id
                           WHERE sales.store_id = ?");
    $stmt->execute([$storeId]);
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total profit for all sales
    foreach ($sales as $sale) {
        $profit = $sale['total_price'] - $sale['quantity_sold'] * $sale['price'];
        $totalProfit += $profit;
    }

    return $totalProfit;
}
?>

