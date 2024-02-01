<?php
// Include config.php to establish a database connection
require_once 'config.php';

// Get today's date
$today = date('Y-m-d');

// Fetch sales data for today from the database
$sql = "SELECT user_id, SUM(total_price) AS total_sales FROM sales WHERE DATE(record_date) = '$today' GROUP BY user_id";
$result = mysqli_query($conn, $sql);

if ($result) {
    // Loop through each staff member's sales data
    while ($row = mysqli_fetch_assoc($result)) {
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
        $update_sql = "UPDATE users SET commission_accumulated = commission_accumulated + $commission WHERE user_id = $user_id";
        mysqli_query($conn, $update_sql);
    }

    echo "Commission calculation completed successfully for today's sales.";
} else {
    echo "Error fetching today's sales data: " . mysqli_error($conn);
}

// Close database connection
mysqli_close($conn);
?>

