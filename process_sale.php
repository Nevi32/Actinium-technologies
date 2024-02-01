<?php
require_once 'config.php';

// Check if the user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Establish database connection
try {
    $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Log the error details and redirect if connection fails
    error_log("Database Connection Error: " . $e->getMessage(), 0);
    $_SESSION['error_message'] = "Database Connection Error: " . $e->getMessage();
    header("Location: sale.php");
    exit();
}

// Get store id from session
$store_id = $_SESSION['store_id'];

// Get store name and location from session
$store_name = $_SESSION['store_name'];
$location_name = $_SESSION['location_name'];

// Process the sales form data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $staff_name = $_POST['staff'];
    $quantity_sold = $_POST['quantity_sold'];
    $total_price = $_POST['total_price'];

    try {
        // Get store id using store name and location
        $stmt_store = $pdo->prepare("SELECT store_id FROM stores WHERE store_name = ? AND location_name = ?");
        $stmt_store->execute([$store_name, $location_name]);
        $store_row = $stmt_store->fetch(PDO::FETCH_ASSOC);
        $store_id = $store_row['store_id'];

        // Get main entry id using product name, category, and store id
        $stmt_main_entry = $pdo->prepare("SELECT main_entry_id, total_quantity FROM main_entry WHERE product_name = ? AND category = ? AND store_id = ?");
        $stmt_main_entry->execute([$product_name, $category, $store_id]);
        $main_entry_row = $stmt_main_entry->fetch(PDO::FETCH_ASSOC);
        $main_entry_id = $main_entry_row['main_entry_id'];
        $total_quantity = $main_entry_row['total_quantity'];

        // If product not available in inventory, return an error message
        if (!$main_entry_id) {
            $_SESSION['error_message'] = "Product not in inventory";
            header("Location: sale.php");
            exit();
        }

        // Get user id using staff name and store id
        $stmt_user = $pdo->prepare("SELECT user_id FROM users WHERE full_name = ? AND store_id = ?");
        $stmt_user->execute([$staff_name, $store_id]);
        $user_row = $stmt_user->fetch(PDO::FETCH_ASSOC);
        $user_id = $user_row['user_id'];

        // Insert sales data into the sales table
        $stmt_sales = $pdo->prepare("INSERT INTO sales (main_entry_id, quantity_sold, total_price, store_id, user_id) VALUES (?, ?, ?, ?, ?)");
        $stmt_sales->execute([$main_entry_id, $quantity_sold, $total_price, $store_id, $user_id]);

        // Set success message
        $_SESSION['success_message'] = "Sale processed successfully!";
        header("Location: sale.php");
        exit();
    } catch (PDOException $e) {
        // Log the error details and set error message
        error_log("Error Processing Sale: " . $e->getMessage(), 0);
        $_SESSION['error_message'] = "Error Processing Sale: " . $e->getMessage();
        header("Location: sale.php");
        exit();
    }
} else {
    // Redirect if the request method is not POST
    header("Location: error.html");
    exit();
}
?>

