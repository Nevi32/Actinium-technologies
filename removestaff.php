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

    // Start a transaction to ensure data integrity
    $pdo->beginTransaction();

    try {
        // Delete related records from the commissions table
        $stmt_commissions = $pdo->prepare("DELETE FROM commissions WHERE user_id = :user_id");
        $stmt_commissions->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt_commissions->execute();

        // Delete related records from the sales table
        $stmt_sales = $pdo->prepare("DELETE FROM sales WHERE user_id = :user_id");
        $stmt_sales->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt_sales->execute();

        // Delete the user from the users table
        $stmt_users = $pdo->prepare("DELETE FROM users WHERE user_id = :user_id");
        $stmt_users->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt_users->execute();

        // Commit the transaction
        $pdo->commit();

        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    } catch (PDOException $e) {
        // Roll back the transaction if an error occurs
        $pdo->rollback();

        echo json_encode(['success' => false, 'message' => 'Error deleting user: ' . $e->getMessage()]);
    }
} else {
    // No user ID provided
    echo json_encode(['success' => false, 'message' => 'No user ID provided']);
}
?>

