<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

// Include the database configuration
require_once 'config.php';

// Retrieve supplier information from the form
$supplierName = $_POST['supplierName'];
$phoneNumber = $_POST['phoneNumber'];
$email = $_POST['email'];
$address = $_POST['address'];

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

    // Prepare and execute the SQL query to insert supplier information along with store_id
    $sql = 'INSERT INTO suppliers (supplier_name, phone_number, email, address, store_id) VALUES (:supplierName, :phoneNumber, :email, :address, :store_id)';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':supplierName', $supplierName, PDO::PARAM_STR);
    $stmt->bindParam(':phoneNumber', $phoneNumber, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':address', $address, PDO::PARAM_STR);
    $stmt->bindParam(':store_id', $store_id, PDO::PARAM_INT);
    $stmt->execute();

    // Display success notification
    echo json_encode(['status' => 'success', 'message' => 'Supplier information added successfully']);
    exit();
} catch (PDOException $e) {
    // Display error notification
    echo json_encode(['status' => 'error', 'message' => 'Error adding supplier information: ' . $e->getMessage()]);
    exit();
}
?>

