<?php
// Include the database configuration file
require_once('config.php');

// Create a PDO connection using the provided database configuration
try {
    $dsn = "mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}";
    $pdo = new PDO($dsn, $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch staff information from the database
try {
    // Query to fetch staff information including user_id
    $query = "SELECT u.user_id, u.full_name AS name, s.location_name AS location, u.commission_accumulated AS commission
              FROM users u
              INNER JOIN stores s ON u.store_id = s.store_id
              WHERE u.role = 'staff'";

    $stmt = $pdo->prepare($query);
    $stmt->execute();

    // Fetch the results as associative array
    $staffData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the staff data as JSON
    header('Content-Type: application/json');
    echo json_encode($staffData);
} catch (PDOException $e) {
    // Handle database errors
    echo "Error fetching staff information: " . $e->getMessage();
}
?>

