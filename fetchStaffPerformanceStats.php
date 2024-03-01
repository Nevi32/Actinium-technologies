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

// Initialize an array to store the staff performance report
$staffPerformanceReport = array();

// Connect to the database
try {
    $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Define the start and end dates based on the period
    $startDate = '';
    $endDate = date('Y-m-d H:i:s'); // Current date and time

    switch ($period) {
        case 'Daily':
            $startDate = date('Y-m-d 00:00:00'); // Start of today
            break;
        case 'Weekly':
            $startDate = date('Y-m-d 00:00:00', strtotime('-7 days')); // 7 days ago
            break;
        case 'Monthly':
            $startDate = date('Y-m-d 00:00:00', strtotime('-1 month')); // 1 month ago
            break;
        default:
            // Handle invalid period
            break;
    }

    // Fetch staff performance data
$stmtStaffPerformance = $pdo->prepare("SELECT users.username, SUM(sales.total_price) AS total_sales, 
                                            stores.location_name
                                        FROM sales 
                                        JOIN users ON sales.user_id = users.user_id 
                                        JOIN stores ON sales.store_id = stores.store_id 
                                        WHERE DATE(sales.record_date) >= ? AND DATE(sales.record_date) <= ? 
                                        GROUP BY users.username, stores.location_name 
                                        ORDER BY total_sales DESC");
$stmtStaffPerformance->execute([$startDate, $endDate]);
$staffPerformanceData = $stmtStaffPerformance->fetchAll(PDO::FETCH_ASSOC);


    // Add staff performance data to the report
    $staffPerformanceReport["staff_performance"] = $staffPerformanceData;

    // Prepare the response
    $response = array(
        'success' => true,
        'message' => "{$storeName} {$period} staff performance report",
        'data' => $staffPerformanceReport
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

