<?php
// Include the database configuration
include('config.php');

// Retrieve supplier information from the form
$supplierName = $_POST['supplierName'];
$phoneNumber = $_POST['phoneNumber'];
$email = $_POST['email'];
$address = $_POST['address'];

// Create a PDO connection to the database
try {
    $dsn = 'mysql:host=' . $databaseConfig['host'] . ';dbname=' . $databaseConfig['dbname'];
    $pdo = new PDO($dsn, $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle connection error
    die('Connection failed: ' . $e->getMessage());
}

// Prepare and execute the SQL query to insert supplier information
try {
    $sql = 'INSERT INTO suppliers (supplier_name, phone_number, email, address) VALUES (:supplierName, :phoneNumber, :email, :address)';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':supplierName', $supplierName, PDO::PARAM_STR);
    $stmt->bindParam(':phoneNumber', $phoneNumber, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':address', $address, PDO::PARAM_STR);
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

