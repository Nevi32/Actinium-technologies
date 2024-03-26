<?php
// Include the database configuration file
require_once('config.php');

// Start the session
session_start();

// Create a PDO connection using the provided database configuration
try {
    $dsn = "mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}";
    $pdo = new PDO($dsn, $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch store name and location from the session
$storeName = $_SESSION['store_name'];
$locationName = $_SESSION['location_name'];

// Fetch main store ID and satellite store IDs
try {
    // Fetch main store ID
    $mainStoreStmt = $pdo->prepare("SELECT store_id FROM stores WHERE store_name = ? AND location_name = ? AND location_type = 'main_store'");
    $mainStoreStmt->execute([$storeName, $locationName]);
    $mainStoreData = $mainStoreStmt->fetch(PDO::FETCH_ASSOC);

    if ($mainStoreData) {
        $mainStoreId = $mainStoreData['store_id'];

        // Fetch satellite store IDs
        $satelliteStoreStmt = $pdo->prepare("SELECT store_id FROM stores WHERE store_name = ? AND location_type = 'satellite'");
        $satelliteStoreStmt->execute([$storeName]);
        $satelliteStoreData = $satelliteStoreStmt->fetchAll(PDO::FETCH_ASSOC);

        $storeIds = array_column($satelliteStoreData, 'store_id');
        $storeIds[] = $mainStoreId; // Add main store ID to the array
    } else {
        // Main store not found
        echo "Main store not found.";
        exit();
    }
} catch (PDOException $e) {
    // Handle database errors
    echo "Error fetching store information: " . $e->getMessage();
    exit();
}

// Fetch staff information from the database
try {
    // Query to fetch staff information including user_id for the main store and satellites
    $query = "SELECT u.user_id, u.full_name AS name, s.location_name AS location, u.commission_accumulated AS commission
              FROM users u
              INNER JOIN stores s ON u.store_id = s.store_id
              WHERE u.role = 'staff' AND u.store_id IN (" . implode(',', $storeIds) . ")";

    $stmt = $pdo->prepare($query);
    $stmt->execute();

    // Fetch the results as associative array
    $staffData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the staff data as JSON
    header('Content-Type: application/json');
    echo json_encode($staffData);
} catch (PDOException $e) {
    // Handle database errors
    echo "Error fetching staff information: " . $e->getMessage();
}
?>

