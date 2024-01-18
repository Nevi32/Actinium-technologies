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
function recordSale($productName, $category, $quantity, $totalPrice)
{
    // Include the database configuration
    include 'config.php';

    try {
        // Connect to the database
        $pdo = connectDatabase($databaseConfig);

        // Check if the product already exists in main_entry table
        $mainEntryID = getProductID($productName, $category, $pdo);

        // If the product doesn't exist, insert into main_entry table
        if ($mainEntryID === null) {
            $mainEntryStatement = $pdo->prepare(
                "INSERT INTO main_entry (product_name, category, total_quantity) 
                VALUES (:productName, :category, :quantity)"
            );

            $mainEntryStatement->bindParam(':productName', $productName);
            $mainEntryStatement->bindParam(':category', $category);
            $mainEntryStatement->bindParam(':quantity', $quantity);
            $mainEntryStatement->execute();

            // Get the last inserted ID from 'main_entry' table
            $mainEntryID = $pdo->lastInsertId();
        }

        // Insert into the sales table
        $salesStatement = $pdo->prepare(
            "INSERT INTO sales (main_entry_id, quantity_sold, total_price) 
            VALUES (:mainEntryID, :quantity, :totalPrice)"
        );

        $salesStatement->bindParam(':mainEntryID', $mainEntryID);
        $salesStatement->bindParam(':quantity', $quantity);
        $salesStatement->bindParam(':totalPrice', $totalPrice);
        $salesStatement->execute();

        // Close the database connection
        $pdo = null;

        return true;
    } catch (PDOException $e) {
        // Handle database errors here
        return false;
    }
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $productName = $_POST['productName'];
    $category = $_POST['category'];
    $quantity = $_POST['quantity'];
    $totalPrice = $_POST['totalPrice'];

    // Record sale in the database
    if (recordSale($productName, $category, $quantity, $totalPrice)) {
        echo 'Sale recorded successfully.';
    } else {
        echo 'Error recording sale.';
    }
}
?>

