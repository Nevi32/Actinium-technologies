<?php
require_once 'config.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the period passed from the client side
    $period = $_POST['period'];

    try {
        // Create a PDO connection
        $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check the period and construct the query accordingly
        switch ($period) {
            case 'daily':
                // Query to fetch the total value of inventory currently for the main store
                $query = "SELECT SUM(i.quantity * i.price) AS total_value 
                          FROM main_entry me 
                          INNER JOIN inventory i ON me.main_entry_id = i.main_entry_id 
                          WHERE DATE(me.record_date) = CURDATE() AND me.store_id = (SELECT store_id FROM stores WHERE store_name = :store_name)";
                break;
            case 'weekly':
                // Query to fetch the total value of inventory for the past 7 days for the main store
                $query = "SELECT SUM(i.quantity * i.price) AS total_value 
                          FROM main_entry me 
                          INNER JOIN inventory i ON me.main_entry_id = i.main_entry_id 
                          WHERE me.record_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND me.store_id = (SELECT store_id FROM stores WHERE store_name = :store_name)";
                break;
            case 'monthly':
                // Query to fetch the total value of inventory for the past month for the main store
                $query = "SELECT SUM(i.quantity * i.price) AS total_value 
                          FROM main_entry me 
                          INNER JOIN inventory i ON me.main_entry_id = i.main_entry_id 
                          WHERE MONTH(me.record_date) = MONTH(CURDATE()) AND YEAR(me.record_date) = YEAR(CURDATE()) AND me.store_id = (SELECT store_id FROM stores WHERE store_name = :store_name)";
                break;
            case 'halfannually':
                // Query to fetch the total value of inventory for the past 6 months for the main store
                $query = "SELECT SUM(i.quantity * i.price) AS total_value 
                          FROM main_entry me 
                          INNER JOIN inventory i ON me.main_entry_id = i.main_entry_id 
                          WHERE me.record_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND me.store_id = (SELECT store_id FROM stores WHERE store_name = :store_name)";
                break;
            case 'annually':
                // Query to fetch the total value of inventory for the past 12 months for the main store
                $query = "SELECT SUM(i.quantity * i.price) AS total_value 
                          FROM main_entry me 
                          INNER JOIN inventory i ON me.main_entry_id = i.main_entry_id 
                          WHERE me.record_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) AND me.store_id = (SELECT store_id FROM stores WHERE store_name = :store_name)";
                break;
            default:
                // Invalid period
                echo json_encode(["error" => "Invalid period selected"]);
                exit();
        }

        // Prepare and execute the query
        $stmt = $pdo->prepare($query);
        $stmt->execute(['store_name' => $_SESSION['store_name']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Return the total value of inventory
            echo json_encode(["total_value" => $result['total_value']]);
        } else {
            // No inventory data found
            echo json_encode(["error" => "No inventory data found"]);
        }
    } catch (PDOException $e) {
        // Log the error
        error_log("Database Error: " . $e->getMessage(), 0);
        // Return an error response
        echo json_encode(["error" => "An error occurred while fetching inventory stats. Please try again later."]);
    }
} else {
    // Method not allowed
    http_response_code(405);
}
?>

