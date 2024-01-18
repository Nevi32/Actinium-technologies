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

// Query to find products with negative quantity
$query = "SELECT main_entry_id, product_name, category, total_quantity FROM main_entry WHERE total_quantity < 0";
$stmt = $pdo->prepare($query);
$stmt->execute();
$negativeQuantityProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if there are products with negative quantity
if (!empty($negativeQuantityProducts)) {
    // Create loan notification message
    $notificationMessage = "Loaned Products:\n";

    foreach ($negativeQuantityProducts as $product) {
        $notificationMessage .= "Product: {$product['product_name']}, Category: {$product['category']}, Loaned Quantity: {$product['total_quantity']}\n";
    }

    // Insert notification into the notifications table
    $insertQuery = "INSERT INTO notifications (subject, message) VALUES (:subject, :message)";
    $insertStmt = $pdo->prepare($insertQuery);
    $insertStmt->bindParam(':subject', $subject);
    $insertStmt->bindParam(':message', $notificationMessage);

    $subject = "Loan Notification";

    try {
        $pdo->beginTransaction();

        $insertStmt->execute();

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die('Error inserting loan notification: ' . $e->getMessage());
    }
}

// Close the database connection
$pdo = null;
?>

