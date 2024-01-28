<?php
require_once 'config.php';

session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

// Retrieve store name and location from session
$store_name = isset($_SESSION['store_name']) ? $_SESSION['store_name'] : "";
$location_name = isset($_SESSION['location_name']) ? $_SESSION['location_name'] : "";

try {
    // Establish a database connection
    $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to fetch store ID using store name and location
    $stmt_store = $pdo->prepare("SELECT store_id FROM stores WHERE store_name = ? AND location_name = ?");
    $stmt_store->execute([$store_name, $location_name]);
    $store_id = $stmt_store->fetchColumn();

    if ($store_id) {
        // Query to fetch staff names associated with the store
        $stmt_staff = $pdo->prepare("SELECT full_name FROM users WHERE store_id = ? AND comp_staff = 0");
        $stmt_staff->execute([$store_id]);
        $staff_names = $stmt_staff->fetchAll(PDO::FETCH_COLUMN);

        // Return staff names as JSON response
        header('Content-Type: application/json');
        echo json_encode($staff_names);
    } else {
        // Store not found
        header("HTTP/1.1 404 Not Found");
        echo "Store not found.";
    }
} catch (PDOException $e) {
    // Handle database errors
    header("HTTP/1.1 500 Internal Server Error");
    echo "Database Error: " . $e->getMessage();
}
?>

