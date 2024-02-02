<?php
// Include the database configuration
include('config.php');

// Create a PDO connection to the database
try {
    $dsn = 'mysql:host=' . $databaseConfig['host'] . ';dbname=' . $databaseConfig['dbname'];
    $pdo = new PDO($dsn, $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Retrieve supplier information from the database
    $sql = 'SELECT * FROM suppliers';
    $stmt = $pdo->query($sql);
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the JSON data
    header('Content-Type: application/json');
    echo json_encode($suppliers);
} catch (PDOException $e) {
    // Handle error
    echo json_encode(['error' => 'Error fetching supplier information: ' . $e->getMessage()]);
}
?>

