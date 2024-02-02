<?php
require_once 'config.php';

// Establish database connection
try {
    $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle connection error
    error_log("Database Connection Error: " . $e->getMessage(), 0);
    // Output error response
    echo json_encode(['error' => 'Database connection error.']);
    exit; // Terminate script execution
}

// Function to fetch and format store data
function fetchStoreData($pdo, $storeName, $locationName) {
    try {
        // Fetch main store ID
        $stmt = $pdo->prepare("SELECT store_id FROM stores WHERE store_name = ? AND location_name = ?");
        $stmt->execute([$storeName, $locationName]);
        $mainStoreId = $stmt->fetchColumn();

        if ($mainStoreId === false) {
            return ['error' => 'Main store not found.'];
        }

        // Fetch main store inventory
        $stmt = $pdo->prepare("SELECT product_name, category, total_quantity, quantity_description FROM main_entry WHERE store_id = ?");
        $stmt->execute([$mainStoreId]);
        $mainStoreInventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch satellite stores
        $stmt = $pdo->prepare("SELECT store_id, location_name FROM stores WHERE location_type = 'satellite' AND store_id <> ?");
        $stmt->execute([$mainStoreId]);
        $satelliteStores = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch satellite stores inventory
        foreach ($satelliteStores as &$store) {
            $stmt = $pdo->prepare("SELECT product_name, category, total_quantity, quantity_description FROM main_entry WHERE store_id = ?");
            $stmt->execute([$store['store_id']]);
            $store['inventory'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Construct and return data array
        $data = [
            'store_name' => $storeName,
            'location_name' => $locationName,
            'main_store_inventory' => $mainStoreInventory,
            'satellite_stores' => $satelliteStores
        ];

        return $data;
    } catch (PDOException $e) {
        // Log the error details
        error_log("Database Error: " . $e->getMessage(), 0);
        return ['error' => 'An error occurred while fetching store data.'];
    }
}

// Check if session is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if store name and location are set in session
if (isset($_SESSION['store_name']) && isset($_SESSION['location_name'])) {
    $storeName = $_SESSION['store_name'];
    $locationName = $_SESSION['location_name'];

    // Fetch data for the store
    $storeData = fetchStoreData($pdo, $storeName, $locationName);

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($storeData);
} else {
    // Send error response if store name or location is not set in session
    echo json_encode(['error' => 'Store name or location is not set in session.']);
}
?>

