<?php
session_start();
require_once 'config.php';

// Check if the store name and location are set in the session
if (isset($_SESSION['store_name']) && isset($_SESSION['location_name'])) {
    try {
        $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Prepare and execute the SQL query to fetch the store ID based on store name and location
        $stmt = $pdo->prepare("SELECT store_id FROM stores WHERE store_name = ? AND location_name = ?");
        $stmt->execute([$_SESSION['store_name'], $_SESSION['location_name']]);
        $store = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($store) {
            // If store ID is found, fetch staff names associated with the store ID
            $stmt = $pdo->prepare("SELECT username FROM users WHERE store_id = ?");
            $stmt->execute([$store['store_id']]);
            $staffNames = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Output the staff names as JSON
            echo json_encode($staffNames);
        } else {
            // Return an error message if store ID is not found
            echo json_encode(array('error' => 'Store ID not found.'));
        }
    } catch (PDOException $e) {
        // Log the error details
        error_log("Database Error: " . $e->getMessage(), 0);
        // Return an error message
        echo json_encode(array('error' => 'An error occurred while fetching staff names.'));
    }
} else {
    // Return an error message if store name or location is not set in the session
    echo json_encode(array('error' => 'Store name or location not set in the session.'));
}
?>

