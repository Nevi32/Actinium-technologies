<?php
// Include the database configuration
include 'config.php';

// Function to establish a database connection using PDO
function connectDatabase($config)
{
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']}";
    $pdo = new PDO($dsn, $config['user'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

// Function to check if a product already exists in main_entry table
function getProductID($productName, $category, $pdo)
{
    $stmt = $pdo->prepare("SELECT main_entry_id FROM main_entry WHERE product_name = :productName AND category = :category");
    $stmt->bindParam(':productName', $productName);
    $stmt->bindParam(':category', $category);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result ? $result['main_entry_id'] : null;
}

// Function to record sales in the database
function recordSale($productName, $category, $quantity, $totalPrice, $storeName, $locationName, $staffName)
{
    // Include the database configuration
    include 'config.php';

    try {
        // Connect to the database
        $pdo = connectDatabase($databaseConfig);

        // Get store ID
        $storeID = getStoreID($storeName, $locationName, $pdo);

        // Get user ID based on staff name
        $userID = getUserID($staffName, $pdo);

        // Check if the product already exists in main_entry table
        $mainEntryID = getProductID($productName, $category, $pdo);

        // If the product doesn't exist, return an error message
        if ($mainEntryID === null) {
            return 'Error: The product is not in the main_entry of the store.';
        }

        // Insert into the sales table
        $salesStatement = $pdo->prepare(
            "INSERT INTO sales (main_entry_id, quantity_sold, total_price, store_id, user_id) 
            VALUES (:mainEntryID, :quantity, :totalPrice, :storeID, :userID)"
        );

        $salesStatement->bindParam(':mainEntryID', $mainEntryID);
        $salesStatement->bindParam(':quantity', $quantity);
        $salesStatement->bindParam(':totalPrice', $totalPrice);
        $salesStatement->bindParam(':storeID', $storeID);
        $salesStatement->bindParam(':userID', $userID);
        $salesStatement->execute();

        // Close the database connection
        $pdo = null;

        return 'Sale recorded successfully.';
    } catch (PDOException $e) {
        // Handle database errors here
        return 'Error recording sale: ' . $e->getMessage();
    }
}

// Function to get store ID based on store name and location
function getStoreID($storeName, $locationName, $pdo)
{
    $stmt = $pdo->prepare("SELECT store_id FROM stores WHERE store_name = :storeName AND location_name = :locationName");
    $stmt->bindParam(':storeName', $storeName);
    $stmt->bindParam(':locationName', $locationName);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result ? $result['store_id'] : null;
}

// Function to get user ID based on staff name
function getUserID($staffName, $pdo)
{
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE full_name = :staffName");
    $stmt->bindParam(':staffName', $staffName);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result ? $result['user_id'] : null;
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $productName = $_POST['productName'];
    $category = $_POST['category'];
    $quantity = $_POST['quantity'];
    $totalPrice = $_POST['totalPrice'];
    $storeName = $_POST['storeName']; // Get store name from hidden field
    $locationName = $_POST['locationName']; // Get location name from hidden field
    $staffName = $_POST['staffName']; // Get staff name from form

    // Record sale in the database
    $result = recordSale($productName, $category, $quantity, $totalPrice, $storeName, $locationName, $staffName);
    echo $result;
}
?>

