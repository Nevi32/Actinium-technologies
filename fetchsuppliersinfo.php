<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

// Include the database configuration
require_once 'config.php';

// Retrieve store information from the session
$storeName = $_SESSION['store_name'];
$locationName = $_SESSION['location_name'];

try {
    // Create a PDO connection to the database
    $dsn = 'mysql:host=' . $databaseConfig['host'] . ';dbname=' . $databaseConfig['dbname'];
    $pdo = new PDO($dsn, $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Retrieve the store_id from the stores table based on store name and location
    $stmt_store = $pdo->prepare("SELECT store_id FROM stores WHERE store_name = ? AND location_name = ?");
    $stmt_store->execute([$storeName, $locationName]);
    $store = $stmt_store->fetch(PDO::FETCH_ASSOC);

    if (!$store) {
        echo json_encode(['status' => 'error', 'message' => 'Store information not found']);
        exit();
    }

    $store_id = $store['store_id'];

    // Retrieve supplier information from the database based on store_id
    $sql = 'SELECT * FROM suppliers WHERE store_id = :store_id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':store_id', $store_id, PDO::PARAM_INT);
    $stmt->execute();
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the JSON data
    header('Content-Type: application/json');
    echo json_encode($suppliers);
} catch (PDOException $e) {
    // Handle error
    echo json_encode(['error' => 'Error fetching supplier information: ' . $e->getMessage()]);
}
?>

