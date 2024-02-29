<?php

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to login page or handle unauthorized access
    header("Location: login.php");
    exit();
}

// Get store name from session
$store_name = isset($_SESSION['store_name']) ? $_SESSION['store_name'] : '';

// Include database configuration
require_once 'config.php';

// Fetch period from client side
$period = isset($_POST['period']) ? $_POST['period'] : '';

// Define period start and end dates
$startDate = '';
$endDate = date('Y-m-d H:i:s'); // Current date and time as used on the server

// Adjust start date based on the period
if ($period == 'Daily') {
    $startDate = date('Y-m-d 00:00:00'); // Start of current day
} elseif ($period == 'Weekly') {
    $startDate = date('Y-m-d H:i:s', strtotime('-7 days')); // 7 days ago from current date
} elseif ($period == 'Monthly') {
    $startDate = date('Y-m-d H:i:s', strtotime('-30 days')); // 30 days ago from current date
} else {
    // Invalid period provided, handle error or set default period
    // For now, let's set the default period to Daily
    $startDate = date('Y-m-d 00:00:00'); // Start of current day
}

// Database connection
try {
    $conn = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}",
                    $databaseConfig['user'], $databaseConfig['password']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Handle database connection error
    $response = [
        'success' => false,
        'error' => 'Database connection failed: ' . $e->getMessage()
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Fetch profit statistics based on the period
try {
    // Fetch data from sales table
    $sql = "SELECT s.main_entry_id, m.product_name, m.category, SUM(s.quantity_sold) AS total_quantity_sold, 
                   SUM(s.total_price) AS total_price, MAX(s.record_date) AS record_date
            FROM sales s
            JOIN main_entry m ON s.main_entry_id = m.main_entry_id
            WHERE s.record_date BETWEEN :startDate AND :endDate
            GROUP BY s.main_entry_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':startDate', $startDate);
    $stmt->bindParam(':endDate', $endDate);
    $stmt->execute();
    $sales_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch profit for each product
    $profit_data = [];
    foreach ($sales_data as $sale) {
        $product_name = $sale['product_name'];
        $category = $sale['category'];
        $total_quantity_sold = $sale['total_quantity_sold'];
        $total_price = $sale['total_price'];

        // Calculate selling price per unit
        $selling_price_per_unit = $total_price / $total_quantity_sold;

        // Query prices table to get profit
        $sql = "SELECT profit FROM prices 
                WHERE product_name = :product_name AND category = :category 
                AND selling_price = :selling_price_per_unit
                ORDER BY set_date DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':product_name', $product_name);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':selling_price_per_unit', $selling_price_per_unit);
        $stmt->execute();
        $profit_row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($profit_row) {
            $profit = $profit_row['profit'] * $total_quantity_sold;
            $profit_data[] = [
                'product_name' => $product_name,
                'category' => $category,
                'total_profit' => $profit
            ];
        }
    }

    // Prepare JSON response
    $response = [
        'success' => true,
        'data' => [
            'profit_data' => $profit_data
        ]
    ];

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);

} catch(PDOException $e) {
    // Handle database query error
    $response = [
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
}

?>

