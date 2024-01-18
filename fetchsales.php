<?php
include 'config.php';

session_start();

try {
    $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $salesQuery = "SELECT s.sale_id, s.main_entry_id, s.quantity_sold, s.total_price, s.sale_date,
                          m.product_name, m.total_quantity, m.quantity_description
                   FROM sales s
                   LEFT JOIN main_entry m ON s.main_entry_id = m.main_entry_id";

    $salesStmt = $pdo->prepare($salesQuery);
    $salesStmt->execute();
    $_SESSION['sales_data'] = $salesStmt->fetchAll(PDO::FETCH_ASSOC);

    // Redirect to viewsales.html
    header('Location: viewsales.php');
    exit();
} catch (PDOException $e) {
    echo "Error fetching sales data: " . $e->getMessage();
}
?>

