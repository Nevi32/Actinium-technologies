<?php
// Include the database configuration
include('config.php');

// Function to establish a database connection using PDO
function connectToDatabase()
{
    global $databaseConfig;

    try {
        $conn = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Function to find the main store ID based on store name and location
function getMainStoreId($storeName, $locationName)
{
    $conn = connectToDatabase();

    $query = "SELECT store_id FROM stores WHERE store_name = ? AND location_name = ? AND location_type = 'main_store'";
    
    try {
        $stmt = $conn->prepare($query);
        $stmt->execute([$storeName, $locationName]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return $result['store_id'];
        }

        return null;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

// Function to find the satellite store ID based on location
function getSatelliteStoreId($locationName)
{
    $conn = connectToDatabase();

    $query = "SELECT store_id FROM stores WHERE location_name = ? AND location_type = 'satellite'";
    
    try {
        $stmt = $conn->prepare($query);
        $stmt->execute([$locationName]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return $result['store_id'];
        }

        return null;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

// Main logic to handle the restock process
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();

    // Get data from the form
    $storeName = $_POST['store-name'];
    $locationName = $_POST['location-name'];
    $productName = $_POST['product-name'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price']; // Add this line to get the price from the form

    // Get main store and satellite store IDs
    $mainStoreId = getMainStoreId($storeName, $locationName);
    $satelliteStoreId = getSatelliteStoreId($_POST['destination-location']);

    // Display the collected data
    echo "Main Store ID: $mainStoreId<br>";
    echo "Satellite Store ID: $satelliteStoreId<br>";
    echo "Product Name: $productName<br>";
    echo "Quantity: $quantity<br>";
    echo "Price: $price<br>";

    // You can add additional checks or data processing here

} else {
    // If the request method is not POST, return an error
    header("HTTP/1.1 405 Method Not Allowed");
    echo "Method Not Allowed";
}
?>

