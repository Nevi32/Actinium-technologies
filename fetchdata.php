<?php
// Include your database configuration
require_once('config.php');

try {
    // Establish a database connection
    $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch data from the database (adjust the query as needed)
    $inventoryQuery = $pdo->query("SELECT * FROM inventory");
    $salesQuery = $pdo->query("SELECT * FROM sales");
    $inventoryData = $inventoryQuery->fetchAll(PDO::FETCH_ASSOC);
    $salesData = $salesQuery->fetchAll(PDO::FETCH_ASSOC);

    // Combine the data into an array
    $data = [
        'inventory' => $inventoryData,
        'sales' => $salesData,
        // Add more data arrays as needed
    ];

    // Start the session
    session_start();

    // Store the data in the session
    $_SESSION['app_data'] = $data;

    // Close the database connection
    $pdo = null;

    // Redirect to stats.html
    header('Location: stats.html');
    exit();
} catch (PDOException $e) {  // Handle database connection errors
    echo "Error: " . $e->getMessage();
    exit();
}
?>

