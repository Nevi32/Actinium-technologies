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

    // Fetch store IDs associated with the store name
    $storeIdsStmt = $pdo->prepare("SELECT store_id FROM stores WHERE store_name = ?");
    $storeIdsStmt->execute([$storeName]);
    $storeIds = $storeIdsStmt->fetchAll(PDO::FETCH_COLUMN);

    // Fetch user IDs of staff working in the stores associated with the store name
    $userIds = array();
    foreach ($storeIds as $storeId) {
        $staffStmt = $pdo->prepare("SELECT user_id FROM users WHERE store_id = ?");
        $staffStmt->execute([$storeId]);
        $userIds = array_merge($userIds, $staffStmt->fetchAll(PDO::FETCH_COLUMN));
    }

    // Define the date condition based on the period
    switch ($period) {
        case 'Daily':
            $dateCondition = "DATE(record_date) = CURDATE()";
            break;
        case 'Weekly':
            $dateCondition = "DATE(record_date) >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND DATE(record_date) <= CURDATE()";
            break;
        case 'Monthly':
            $dateCondition = "DATE(record_date) >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) AND DATE(record_date) <= CURDATE()";
            break;
        case 'Halfannually':
            $dateCondition = "DATE(record_date) >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND DATE(record_date) <= CURDATE()";
            break;
        case 'Annually':
            $dateCondition = "DATE(record_date) >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR) AND DATE(record_date) <= CURDATE()";
            break;
        default:
            throw new Exception("Invalid period specified.");
            break;
    }

    // Fetch staff performance data
    $staffPerformanceStmt = $pdo->prepare("SELECT users.username, SUM(sales.total_price) AS total_sales
                                            FROM sales 
                                            JOIN users ON sales.user_id = users.user_id 
                                            WHERE sales.user_id IN (" . implode(',', $userIds) . ") AND $dateCondition
                                            GROUP BY users.user_id 
                                            ORDER BY total_sales DESC");
    $staffPerformanceStmt->execute();
    $staffPerformanceData = $staffPerformanceStmt->fetchAll(PDO::FETCH_ASSOC);

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

