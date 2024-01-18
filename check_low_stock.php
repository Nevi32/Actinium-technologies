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

// Query to find products with quantity below 10
$query = "SELECT main_entry_id, product_name, category, total_quantity FROM main_entry WHERE total_quantity < 10";
$stmt = $pdo->prepare($query);
$stmt->execute();
$lowStockProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if there are products with low stock
if (!empty($lowStockProducts)) {
    // Create low stock notification message
    $notificationMessage = "Low Stock Products:\n";

    foreach ($lowStockProducts as $product) {
        $notificationMessage .= "Product: {$product['product_name']}, Category: {$product['category']}, Current Stock: {$product['total_quantity']}\n";
    }

    // Insert notification into the notifications table
    $insertQuery = "INSERT INTO notifications (subject, message) VALUES (:subject, :message)";
    $insertStmt = $pdo->prepare($insertQuery);
    $insertStmt->bindParam(':subject', $subject);
    $insertStmt->bindParam(':message', $notificationMessage);

    $subject = "Low Stock Notification";

    try {
        $pdo->beginTransaction();

        $insertStmt->execute();

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die('Error inserting low stock notification: ' . $e->getMessage());
    }
}

// Close the database connection
$pdo = null;
?>

