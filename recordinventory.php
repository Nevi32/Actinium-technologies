<?php
// Include the configuration file
require_once 'config.php';

try {
    // Use the database configuration from the included file
    $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // If there is an error connecting to the database, terminate the script and display an error message
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $productNames = $_POST['product_name'];
    $categories = $_POST['category'];
    $quantities = $_POST['quantity'];
    $quantityDescriptions = $_POST['quantity_description'] ?? []; // Check if set, otherwise use empty array
    $prices = $_POST['price'];

    // Retrieve user information from session
    session_start();
    $storeName = $_SESSION['store_name'];
    $locationName = $_SESSION['location_name'];

    // Get store ID based on store name and location
    $storeQuery = $pdo->prepare("SELECT store_id FROM stores WHERE store_name = ? AND location_name = ?");
    $storeQuery->execute([$storeName, $locationName]);
    $storeData = $storeQuery->fetch(PDO::FETCH_ASSOC);

    if (!$storeData) {
        echo "Error: Store not found.";
        exit();
    }

    $storeId = $storeData['store_id'];

    try {
        // Begin a transaction
        $pdo->beginTransaction();

        // Loop through each entry and insert into the database
        for ($i = 0; $i < count($productNames); $i++) {
            $productName = $productNames[$i];
            $category = $categories[$i];
            $quantity = $quantities[$i];
            $quantityDescription = isset($quantityDescriptions[$i]) ? $quantityDescriptions[$i] : ''; // Check if set, otherwise use empty string
            $price = $prices[$i];

            // Check if any required fields are empty
            if (empty($productName) || empty($quantity)) {
                echo "Product Name and Quantity are required fields.";
                exit();
            }

            // Check if the product already exists in the main_entry table for the specified store
            $existingRecord = $pdo->prepare("SELECT * FROM main_entry WHERE product_name = ? AND store_id = ?");
            $existingRecord->execute([$productName, $storeId]);
            $existingData = $existingRecord->fetch(PDO::FETCH_ASSOC);

            if ($existingData !== false) {
                // Product already exists for this store

                // Check if the category is different
                if ($existingData['category'] !== $category) {
                    // Product has a different category, insert a new main entry
                    $insertMainStmt = $pdo->prepare("INSERT INTO main_entry (product_name, category, total_quantity, quantity_description, store_id) VALUES (?, ?, ?, ?, ?)");
                    $insertMainStmt->execute([$productName, $category, $quantity, $quantityDescription, $storeId]);

                    // Retrieve the main entry_id for the newly inserted main entry
                    $mainEntryId = $pdo->lastInsertId();
                } else {
                    // Product has the same category, update the existing main entry
                    $mainEntryId = $existingData['main_entry_id'];

                    // Update the total quantity in the existing main entry
                    $updateTotalStmt = $pdo->prepare("UPDATE main_entry SET total_quantity = total_quantity + ? WHERE main_entry_id = ?");
                    $updateTotalStmt->execute([$quantity, $mainEntryId]);
                }
            } else {
                // Product does not exist for this store, insert a new main entry
                $insertMainStmt = $pdo->prepare("INSERT INTO main_entry (product_name, category, total_quantity, quantity_description, store_id) VALUES (?, ?, ?, ?, ?)");
                $insertMainStmt->execute([$productName, $category, $quantity, $quantityDescription, $storeId]);

                // Retrieve the main entry_id for the newly inserted main entry
                $mainEntryId = $pdo->lastInsertId();
            }

            // Insert the individual entry
            $insertStmt = $pdo->prepare("INSERT INTO inventory (main_entry_id, quantity, quantity_description, price, record_date, store_id) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, ?)");
            $insertStmt->execute([$mainEntryId, $quantity, $quantityDescription, $price, $storeId]);
        }

        // Commit the transaction
        $pdo->commit();

        echo "Entries added successfully!";
    } catch (PDOException $e) {
        // Rollback the transaction if an error occurs
        $pdo->rollBack();

        // Handle any PDO exceptions and display an error message
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request method.";
}
?>

