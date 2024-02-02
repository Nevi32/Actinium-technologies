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

    // Fetch all sales made by the staff member on the current day
    $user_id = $_GET['id']; // Assuming the staff member's ID is passed via GET parameter
    $sales_sql = "SELECT sale_id, total_price FROM sales WHERE DATE(record_date) = :today AND user_id = :user_id";
    $sales_stmt = $pdo->prepare($sales_sql);
    $sales_stmt->bindParam(':today', $today, PDO::PARAM_STR);
    $sales_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $sales_stmt->execute();
    $sales = $sales_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total sales amount
    $total_sales = array_sum(array_column($sales, 'total_price'));

    // Calculate commission based on total sales amount
    $commission = calculateCommission($total_sales);

    // Update commission_accumulated field for the staff member in users table
    updateCommissionAccumulated($pdo, $user_id, $commission);

    // Record each sale in the commissions table with the commission amount based on the total sales amount
    foreach ($sales as $sale) {
        $sale_id = $sale['sale_id'];
        recordCommission($pdo, $user_id, $sale_id, $commission, $today);
    }

    echo "Commission calculation completed successfully for today's sales.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the database connection
$pdo = null;

// Function to calculate commission based on total sales amount
function calculateCommission($total_sales) {
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
    return $commission;
}

// Function to record commission for a sale in commissions table
function recordCommission($pdo, $user_id, $sale_id, $commission_amount, $commission_date) {
    $insert_sql = "INSERT INTO commissions (user_id, sales_id, commission_amount, commission_date) VALUES (:user_id, :sale_id, :commission_amount, :commission_date)";
    $insert_stmt = $pdo->prepare($insert_sql);
    $insert_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $insert_stmt->bindParam(':sale_id', $sale_id, PDO::PARAM_INT);
    $insert_stmt->bindParam(':commission_amount', $commission_amount, PDO::PARAM_INT);
    $insert_stmt->bindParam(':commission_date', $commission_date, PDO::PARAM_STR);
    $insert_stmt->execute();
}

// Function to update commission_accumulated field for the staff member
function updateCommissionAccumulated($pdo, $user_id, $commission) {
    $update_sql = "UPDATE users SET commission_accumulated = commission_accumulated + :commission WHERE user_id = :user_id";
    $update_stmt = $pdo->prepare($update_sql);
    $update_stmt->bindParam(':commission', $commission, PDO::PARAM_INT);
    $update_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $update_stmt->execute();
}
?>

