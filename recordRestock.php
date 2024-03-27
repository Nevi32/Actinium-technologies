<?php
// Include database credentials
include 'config.php';

// Start session
session_start();

// Establish database connection
try {
    $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Return error message if database connection fails
    $response = ['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()];
    echo json_encode($response);
    exit(); // Exit script
}

// Retrieve form data
$quantity = $_POST['quantity'];
$price = $_POST['price'];
$product_name = $_POST['product-name'];
$category = $_POST['category'];

// Retrieve store name from session
if (!isset($_SESSION['store_name'])) {
    $response = ['success' => false, 'message' => 'Store name not set in session'];
    echo json_encode($response);
    exit();
}
$store_name = $_SESSION['store_name'];

// Retrieve destination location from form
$destination_location = $_POST['destination-location'];

try {
    // Retrieve main store ID
    $stmt = $pdo->prepare("SELECT store_id FROM stores WHERE store_name = :store_name AND location_type = 'main_store'");
    $stmt->execute(['store_name' => $store_name]);
    $main_store_id = $stmt->fetchColumn();

    // Retrieve destination store ID based on store name and location
    $stmt = $pdo->prepare("SELECT store_id FROM stores WHERE store_name = :store_name AND location_name = :destination_location AND location_type = 'satellite'");
    $stmt->execute(['store_name' => $store_name, 'destination_location' => $destination_location]);
    $destination_store_id = $stmt->fetchColumn();

    // Check if main store and destination store IDs are valid
    if ($main_store_id && $destination_store_id) {
        // Retrieve main entry ID from main entry table
        $stmt = $pdo->prepare("SELECT main_entry_id FROM main_entry WHERE product_name = :product_name AND category = :category AND store_id = :main_store_id");
        $stmt->execute(['product_name' => $product_name, 'category' => $category, 'main_store_id' => $main_store_id]);
        $main_entry_id = $stmt->fetchColumn();

        // Check if main entry exists
        if ($main_entry_id) {
            // Insert restock order into inventory_orders table
            $stmt = $pdo->prepare("INSERT INTO inventory_orders (main_store_id, destination_store_id, main_entry_id, quantity, price) VALUES (:main_store_id, :destination_store_id, :main_entry_id, :quantity, :price)");
            $stmt->execute(['main_store_id' => $main_store_id, 'destination_store_id' => $destination_store_id, 'main_entry_id' => $main_entry_id, 'quantity' => $quantity, 'price' => $price]);

            // Return success message
            $response = ['success' => true, 'message' => 'Restock order successfully recorded.'];
            echo json_encode($response);
        } else {
            // Return error message if main entry does not exist
            $response = ['success' => false, 'message' => 'Main entry not found for the provided product and category in the main store inventory.'];
            echo json_encode($response);
        }
    } else {
        // Return error message if main store or destination store not found
        $response = ['success' => false, 'message' => 'Main store or destination store not found.'];
        echo json_encode($response);
    }
} catch (PDOException $e) {
    // Return error message for any database query errors
    $response = ['success' => false, 'message' => 'Error recording restock order: ' . $e->getMessage()];
    echo json_encode($response);
}
?>

