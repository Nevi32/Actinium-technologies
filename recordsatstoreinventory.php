<?php
require_once 'config.php';

// Check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit("Unauthorized");
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Method Not Allowed");
}

// Check if 'yes' button was clicked and order_id is provided
if (!isset($_POST['order_id']) || !isset($_POST['confirm']) || $_POST['confirm'] !== 'yes') {
    http_response_code(400);
    exit("Invalid request");
}

// Get the order_id from POST data
$order_id = $_POST['order_id'];

try {
    // Connect to the database
    $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Update the cleared status to 1 in inventory_orders table
    $stmt = $pdo->prepare("UPDATE inventory_orders SET cleared = 1 WHERE order_id = ?");
    $stmt->execute([$order_id]);

    // Get destination_store_id and quantity details from inventory_orders table
    $stmt = $pdo->prepare("SELECT destination_store_id, main_entry_id, quantity FROM inventory_orders WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $orderDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$orderDetails) {
        http_response_code(404);
        exit("Order not found");
    }

    $destination_store_id = $orderDetails['destination_store_id'];
    $main_entry_id = $orderDetails['main_entry_id'];
    $quantity = $orderDetails['quantity'];

    // Fetch product details from main_entry table
    $stmt = $pdo->prepare("SELECT product_name, category, quantity_description FROM main_entry WHERE main_entry_id = ?");
    $stmt->execute([$main_entry_id]);
    $productDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$productDetails) {
        http_response_code(404);
        exit("Product not found");
    }

    $product_name = $productDetails['product_name'];
    $category = $productDetails['category'];
    $quantity_description = $productDetails['quantity_description'];

    // Check if the product already exists in main_entry table for the destination_store_id
    $stmt = $pdo->prepare("SELECT main_entry_id FROM main_entry WHERE product_name = ? AND category = ? AND store_id = ?");
    $stmt->execute([$product_name, $category, $destination_store_id]);
    $existingMainEntry = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingMainEntry) {
        // Update total_quantity in main_entry table
        $stmt = $pdo->prepare("UPDATE main_entry SET total_quantity = total_quantity + ? WHERE main_entry_id = ?");
        $stmt->execute([$quantity, $existingMainEntry['main_entry_id']]);
        $main_entry_id = $existingMainEntry['main_entry_id'];
    } else {
        // Insert new record into main_entry table
        $stmt = $pdo->prepare("INSERT INTO main_entry (product_name, category, total_quantity, store_id, quantity_description) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$product_name, $category, $quantity, $destination_store_id, $quantity_description]);
        $main_entry_id = $pdo->lastInsertId();
    }

    // Insert record into inventory table
    $stmt = $pdo->prepare("INSERT INTO inventory (main_entry_id, quantity, quantity_description, store_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$main_entry_id, $quantity, $quantity_description, $destination_store_id]);

    // Return success response
    http_response_code(200);
    exit("Product inventory recorded successfully");
} catch (PDOException $e) {
    // Log the error details
    error_log("Database Error: " . $e->getMessage(), 0);
    http_response_code(500);
    exit("Internal Server Error");
}
?>

