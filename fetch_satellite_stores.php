<?php
// fetch_satellite_stores.php

// Include the configuration file
require_once 'config.php';

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header("Location: login.html");
    exit();
}

// Initialize an empty array to store satellite stores
$satelliteStores = [];

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get the store name from the user's session
    $storeName = $_SESSION['store_name'];

    // Prepare and execute a query to fetch satellite stores for the given main store
    $stmt = $pdo->prepare("SELECT store_name, location_name FROM stores WHERE location_type = 'satellite' AND store_name = ?");
    $stmt->execute([$storeName]);

    // Fetch satellite store information and store it in the array
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $satelliteStores[] = $row;
    }
} catch (PDOException $e) {
    // Log the error details
    error_log("Database Error: " . $e->getMessage(), 0);
    // Redirect to an error page or display an error message
    header("Location: error.html");
    exit();
}

// Return the satellite store information as JSON
echo json_encode($satelliteStores);
?>

