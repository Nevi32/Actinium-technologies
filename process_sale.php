<?php

// Include database configuration
include 'config.php';

// Function to establish database connection
function connectToDatabase($config) {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']}";
    try {
        $pdo = new PDO($dsn, $config['user'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

// Function to get store ID from store name and location
function getStoreId($pdo, $storeName, $locationName) {
    $query = "SELECT store_id FROM stores WHERE store_name = ? AND location_name = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$storeName, $locationName]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['store_id'];
}

// Function to get main entry ID from product name and category
function getMainEntryId($pdo, $productName, $category, $storeId) {
    $query = "SELECT main_entry_id FROM main_entry WHERE product_name = ? AND category = ? AND store_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$productName, $category, $storeId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['main_entry_id'];
}

// Function to get user ID from staff name
function getUserId($pdo, $staffName) {
    $query = "SELECT user_id FROM users WHERE username = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$staffName]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['user_id'];
}

// Function to generate the receipt content
function generateReceipt($salesData) {
    $receiptContent = "<h2>Sales Receipt</h2>";
    $receiptContent .= "<table>";
    $receiptContent .= "<tr><th>Product Name</th><th>Category</th><th>Quantity Sold</th><th>Total Price</th></tr>";
    foreach ($salesData as $sale) {
        $receiptContent .= "<tr><td>{$sale['product_name']}</td><td>{$sale['category']}</td><td>{$sale['quantity_sold']}</td><td>{$sale['total_price']}</td></tr>";
    }
    $receiptContent .= "</table>";
    return $receiptContent;
}

// Establish database connection
$pdo = connectToDatabase($databaseConfig);

// Retrieve data from existing session
session_start();
$storeName = $_SESSION['store_name'];
$locationName = $_SESSION['location_name'];
$staffName = $_SESSION['username'];

// Get store ID
$storeId = getStoreId($pdo, $storeName, $locationName);

// Process sales entries
$salesData = array(); // Array to store sales data for receipt
foreach ($_POST['product_name'] as $key => $productName) {
    $category = $_POST['category'][$key];
    $quantitySold = $_POST['quantity_sold'][$key];
    $totalPrice = $_POST['total_price'][$key];

    // Get main entry ID
    $mainEntryId = getMainEntryId($pdo, $productName, $category, $storeId);

    // Get user ID
    $userId = getUserId($pdo, $staffName);

    // Insert sales record into sales table
    $query = "INSERT INTO sales (main_entry_id, quantity_sold, total_price, store_id, user_id) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$mainEntryId, $quantitySold, $totalPrice, $storeId, $userId]);

    // Store sales data for receipt
    $salesData[] = array(
        'product_name' => $productName,
        'category' => $category,
        'quantity_sold' => $quantitySold,
        'total_price' => $totalPrice
    );
}

// Generate receipt content
$receiptContent = generateReceipt($salesData);

// Save receipt content to session for printing
$_SESSION['receipt_content'] = $receiptContent;

// Redirect back to sales page with success message and to display receipt
header("Location: sale.php?show_receipt=1");
exit();
?>

