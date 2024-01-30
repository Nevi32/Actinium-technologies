<?php
require_once 'config.php';

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

// Retrieve store information from session
$storeName = $_SESSION['store_name'] ?? null;
$locationName = $_SESSION['location_name'] ?? null;

// Check if store information is available
if (!$storeName || !$locationName) {
    header("Location: login.html?message=Store%20information%20not%20found.&type=error");
    exit();
}

try {
    // Connect to the database
    $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query the stores table to determine store type
    $stmt = $pdo->prepare("SELECT location_type FROM stores WHERE store_name = ? AND location_name = ?");
    $stmt->execute([$storeName, $locationName]);
    $storeInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the store is a main store or satellite and redirect accordingly
    if ($storeInfo['location_type'] === 'main_store') {
        header("Location: fetchsalesmain.php");
        exit();
    } elseif ($storeInfo['location_type'] === 'satellite') {
        header("Location: fetchsalessat.php");
        exit();
    } else {
        header("Location: login.html?message=Invalid%20store%20type.&type=error");
        exit();
    }
} catch (PDOException $e) {
    // Log the error details
    error_log("Database Error: " . $e->getMessage(), 0);
    header("Location: login.html?message=An%20error%20occurred.%20Please%20try%20again%20later.&type=error");
    exit();
}
?>

