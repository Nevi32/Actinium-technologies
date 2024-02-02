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

// Function to fetch and format sales data for the current day
function fetchSalesData($pdo, $storeName, $locationName) {
    try {
        // Fetch main store ID
        $stmt = $pdo->prepare("SELECT store_id FROM stores WHERE store_name = ? AND location_name = ?");
        $stmt->execute([$storeName, $locationName]);
        $mainStoreId = $stmt->fetchColumn();

        if ($mainStoreId === false) {
            return ['error' => 'Main store not found.'];
        }

        // Get current date
        $currentDate = date("Y-m-d");

        // Fetch total sales for main store on the current day
        $stmt = $pdo->prepare("SELECT SUM(total_price) AS total_sales FROM sales WHERE store_id = ? AND DATE(record_date) = ?");
        $stmt->execute([$mainStoreId, $currentDate]);
        $mainStoreTotalSales = $stmt->fetchColumn();

        // Fetch total sales for satellite stores on the current day
        $stmt = $pdo->prepare("SELECT st.location_name, SUM(s.total_price) AS total_sales FROM sales s JOIN stores st ON s.store_id = st.store_id WHERE st.location_type = 'satellite' AND DATE(record_date) = ? GROUP BY s.store_id");
        $stmt->execute([$currentDate]);
        $satelliteStoreSales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Construct and return data array
        $data = [
            'store_name' => $storeName,
            'location_name' => $locationName,
            'main_store_total_sales' => $mainStoreTotalSales,
            'satellite_store_sales' => $satelliteStoreSales
        ];

        return $data;
    } catch (PDOException $e) {
        // Log the error details
        error_log("Database Error: " . $e->getMessage(), 0);
        return ['error' => 'An error occurred while fetching sales data.'];
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
    $salesData = fetchSalesData($pdo, $storeName, $locationName);

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($salesData);
} else {
    // Send error response if store name or location is not set in session
    echo json_encode(['error' => 'Store name or location is not set in session.']);
}
?>

