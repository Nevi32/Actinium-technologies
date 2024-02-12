<?php
// Include the database configuration
include('config.php');
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

// Retrieve expenses information from the form
$expenseTypes = $_POST['expenseType'];
$amounts = $_POST['amount'];

// Get the store name and location from the session
$storeName = $_SESSION['store_name'];
$locationName = $_SESSION['location_name'];

// Create a PDO connection to the database
try {
    $dsn = 'mysql:host=' . $databaseConfig['host'] . ';dbname=' . $databaseConfig['dbname'];
    $pdo = new PDO($dsn, $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle connection error
    echo json_encode(['status' => 'error', 'message' => 'Connection failed: ' . $e->getMessage()]);
    exit();
}

// Get the store ID from the stores table
try {
    $stmt = $pdo->prepare('SELECT store_id FROM stores WHERE store_name = :storeName AND location_name = :locationName');
    $stmt->execute(['storeName' => $storeName, 'locationName' => $locationName]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $storeId = $row['store_id'];
} catch (PDOException $e) {
    // Handle error
    echo json_encode(['status' => 'error', 'message' => 'Error fetching store ID: ' . $e->getMessage()]);
    exit();
}

// Prepare and execute the SQL query to insert expense information
try {
    $stmt = $pdo->prepare('INSERT INTO expenses (expense_type, amount, store_id) VALUES (?, ?, ?)');
    foreach ($expenseTypes as $key => $expenseType) {
        $stmt->execute([$expenseType, $amounts[$key], $storeId]);
    }

    // Display success notification
    echo json_encode(['status' => 'success', 'message' => 'Expenses recorded successfully']);
    exit();
} catch (PDOException $e) {
    // Display error notification
    echo json_encode(['status' => 'error', 'message' => 'Error recording expenses: ' . $e->getMessage()]);
    exit();
}
?>

