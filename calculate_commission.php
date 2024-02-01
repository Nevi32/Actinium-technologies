<?php
// Include the database configuration file
require_once 'config.php';

try {
    // Create a PDO connection using the provided database configuration
    $dsn = "mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}";
    $pdo = new PDO($dsn, $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get today's date
    $today = date('Y-m-d');
    
    // Fetch sales data for today from the database
    $sql = "SELECT user_id, SUM(total_price) AS total_sales FROM sales WHERE DATE(record_date) = :today GROUP BY user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':today', $today, PDO::PARAM_STR);
    $stmt->execute();
    $salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($salesData) {
        // Loop through each staff member's sales data
        foreach ($salesData as $row) {
            $user_id = $row['user_id'];
            $total_sales = $row['total_sales'];
    
            // Calculate commission based on sales criteria
            $commission = 0;
            if ($total_sales >= 1000 && $total_sales < 12000) {
                $commission = 400;
            } elseif ($total_sales >= 12000 && $total_sales < 18000) {
                $commission = 500;
            } elseif ($total_sales >= 18000 && $total_sales < 23000) {
                $commission = 600;
            } elseif ($total_sales >= 23000 && $total_sales < 28000) {
                $commission = 700;
            } elseif ($total_sales >= 28000 && $total_sales < 33000) {
                $commission = 900;
            } elseif ($total_sales >= 33000 && $total_sales < 48000) {
                $commission = 1000;
            }
    
            // Update commission_accumulated field in users table
            $update_sql = "UPDATE users SET commission_accumulated = commission_accumulated + :commission WHERE user_id = :user_id";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->bindParam(':commission', $commission, PDO::PARAM_INT);
            $update_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $update_stmt->execute();
        }
    
        echo "Commission calculation completed successfully for today's sales.";
    } else {
        echo "No sales data found for today.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the database connection
$pdo = null;
?>

