<?php
// Include the database configuration file
require_once 'config.php';

// Initialize response array
$response = array();

try {
    // Check if user ID is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception('User ID is missing.');
    }

    // Create a PDO connection using the provided database configuration
    $dsn = "mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}";
    $pdo = new PDO($dsn, $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get today's date
    $today = date('Y-m-d');

    // Fetch all sales made by the staff member on the current day
    $user_id = $_GET['id']; // Assuming the staff member's ID is passed via GET parameter
    $commission_calculated = commissionAlreadyCalculated($pdo, $user_id, $today);

    // Check if commission has already been calculated for the staff on the current day
    if ($commission_calculated) {
        throw new Exception('Commission already calculated for the staff today.');
    }

    // Fetch sales made by the staff member on the current day
    $sales = fetchSales($pdo, $user_id, $today);

    // Check if any sales were made today
    if (empty($sales)) {
        throw new Exception('No sales made by the staff member today.');
    }

    // Calculate total sales amount
    $total_sales = array_sum(array_column($sales, 'total_price'));

    // Calculate commission based on total sales amount
    $commission = calculateCommission($total_sales);

    // Record commission for the staff member
    recordCommission($pdo, $user_id, $commission, $today);

    // Update commission_accumulated field for the staff member in users table
    updateCommissionAccumulated($pdo, $user_id, $commission);

    // Set success response
    $response['success'] = true;
    $response['message'] = 'Commission calculation completed successfully for today\'s sales.';

} catch (Exception $e) {
    // Set error response
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Send response as JSON
header('Content-Type: application/json');
echo json_encode($response);

// Close the database connection
$pdo = null;

// Function to check if commission has already been calculated for the staff on the current day
function commissionAlreadyCalculated($pdo, $user_id, $today) {
    $check_sql = "SELECT COUNT(*) FROM commissions WHERE user_id = :user_id AND commission_date = :today";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $check_stmt->bindParam(':today', $today, PDO::PARAM_STR);
    $check_stmt->execute();
    $count = $check_stmt->fetchColumn();
    return $count > 0;
}

// Function to fetch sales made by the staff member on the current day
function fetchSales($pdo, $user_id, $today) {
    $sales_sql = "SELECT sale_id, total_price FROM sales WHERE DATE(record_date) = :today AND user_id = :user_id";
    $sales_stmt = $pdo->prepare($sales_sql);
    $sales_stmt->bindParam(':today', $today, PDO::PARAM_STR);
    $sales_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $sales_stmt->execute();
    return $sales_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to calculate commission based on total sales amount
function calculateCommission($total_sales) {
    // Define commission rates and corresponding sales thresholds
    $commission_rates = array(
        1000 => 400,
        12000 => 500,
        18000 => 600,
        23000 => 700,
        28000 => 900,
        33000 => 1000
    );

    // Iterate through commission rates to find applicable commission
    $commission = 0;
    foreach ($commission_rates as $threshold => $rate) {
        if ($total_sales >= $threshold) {
            $commission = $rate;
        } else {
            break;
        }
    }

    return $commission;
}

// Function to record commission for a staff member
function recordCommission($pdo, $user_id, $commission_amount, $commission_date) {
    $insert_sql = "INSERT INTO commissions (user_id, commission_amount, commission_date) VALUES (:user_id, :commission_amount, :commission_date)";
    $insert_stmt = $pdo->prepare($insert_sql);
    $insert_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
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

