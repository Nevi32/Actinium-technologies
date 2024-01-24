<?php
// Include the configuration file
require_once 'config.php';

// Start the session
session_start();

// Function to send JSON response
function sendResponse($status, $message) {
    header('Content-Type: application/json');
    echo json_encode(['status' => $status, 'message' => $message]);
    exit();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    sendResponse('error', 'User not logged in');
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $quantity = $_POST['quantity'];
    $productName = $_POST['product-name'];
    $category = $_POST['category'];
    $destinationLocation = $_POST['destination-location'];

    try {
        // Create a new PDO instance
        $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Get main_store_id from the stores table
        $storeStmt = $pdo->prepare("SELECT store_id FROM stores WHERE location_name = ? AND location_type = 'main_store'");
        $storeStmt->execute([$destinationLocation]);
        $storeRow = $storeStmt->fetch(PDO::FETCH_ASSOC);

        if ($storeRow) {
            $destinationStoreId = $storeRow['store_id'];

            // Check if the product exists in the main_store inventory
            $mainEntryStmt = $pdo->prepare("SELECT main_entry_id FROM main_entry WHERE product_name = ? AND category = ? AND store_id = ?");
            $mainEntryStmt->execute([$productName, $category, $destinationStoreId]);
            $mainEntryRow = $mainEntryStmt->fetch(PDO::FETCH_ASSOC);

            $mainEntryId = ($mainEntryRow) ? $mainEntryRow['main_entry_id'] : null;

            // Insert the record into inventory_orders table
            $insertStmt = $pdo->prepare("INSERT INTO inventory_orders (main_store_id, destination_store_id, main_entry_id, quantity, order_date) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)");
            $insertStmt->execute([$destinationStoreId, $destinationStoreId, $mainEntryId, $quantity]);

            // Send success message in JSON format
            sendResponse('success', 'Restock order recorded successfully');
        } else {
            // Send error message in JSON format
            sendResponse('error', 'Invalid destination location');
        }
    } catch (PDOException $e) {
        // Log the error details
        error_log("Database Error: " . $e->getMessage(), 0);
        // Send error message in JSON format
        sendResponse('error', 'Database error');
    }
} else {
    // Send error message in JSON format
    sendResponse('error', 'Form not submitted');
}
?>

