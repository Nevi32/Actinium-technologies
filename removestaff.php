<?php
// Include the database configuration file
require_once('config.php');

// Check if user ID is provided
if(isset($_GET['id']) && !empty($_GET['id'])){
    $userId = $_GET['id'];

    // Create a PDO connection using the provided database configuration
    try {
        $dsn = "mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}";
        $pdo = new PDO($dsn, $databaseConfig['user'], $databaseConfig['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }

    // Query to delete user from the users table
    $query = "DELETE FROM users WHERE user_id = :user_id";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        // Check if any rows were affected
        $rowCount = $stmt->rowCount();
        if($rowCount > 0){
            // User deleted successfully
            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        } else {
            // User not found or already deleted
            echo json_encode(['success' => false, 'message' => 'User not found or already deleted']);
        }
    } catch (PDOException $e) {
        // Handle database errors
        echo json_encode(['success' => false, 'message' => 'Error deleting user: ' . $e->getMessage()]);
    }
} else {
    // No user ID provided
    echo json_encode(['success' => false, 'message' => 'No user ID provided']);
}
?>

