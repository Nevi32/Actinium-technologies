<?php
// Include the database configuration file
require_once('config.php');

try {
    // Create a PDO connection using the provided database configuration
    $dsn = "mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}";
    $pdo = new PDO($dsn, $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare and execute the query to reset commission_accumulated for staff members
    $stmt = $pdo->prepare("UPDATE users SET commission_accumulated = 0 WHERE role = 'staff'");
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Commission reset successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error resetting commission: ' . $e->getMessage()]);
}
?>

