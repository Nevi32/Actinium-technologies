<?php
require_once('config.php');

// Establish a database connection
try {
    $dsn = 'mysql:host=' . $databaseConfig['host'] . ';dbname=' . $databaseConfig['dbname'];
    $pdo = new PDO($dsn, $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Query to find products with zero quantity
$query = "SELECT main_entry_id, product_name, category FROM main_entry WHERE total_quantity = 0";
$stmt = $pdo->prepare($query);
$stmt->execute();
$emptyProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if there are empty products
if (!empty($emptyProducts)) {
    // Create notification message
    $notificationMessage = "Products need restocking:\n";
    
    foreach ($emptyProducts as $product) {
        $notificationMessage .= "Product: {$product['product_name']}, Category: {$product['category']}\n";
    }

    // Insert notification into the notifications table
    $insertQuery = "INSERT INTO notifications (subject, message) VALUES (:subject, :message)";
    $insertStmt = $pdo->prepare($insertQuery);
    $insertStmt->bindParam(':subject', $subject);
    $insertStmt->bindParam(':message', $notificationMessage);

    $subject = "Restock Notification";
    
    try {
        $pdo->beginTransaction();

        $insertStmt->execute();

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die('Error inserting notification: ' . $e->getMessage());
    }
}

// Close the database connection
$pdo = null;
?>

