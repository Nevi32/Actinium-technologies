<?php
// Include the database credentials
include 'config.php';

// Create a database connection
$dsn = 'mysql:host=' . $databaseConfig['host'] . ';dbname=' . $databaseConfig['dbname'];
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
try {
    $pdo = new PDO($dsn, $databaseConfig['user'], $databaseConfig['password'], $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Fetch latest prices for each product
$stmt = $pdo->query("SELECT * FROM prices p WHERE p.set_date = (SELECT MAX(set_date) FROM prices WHERE product_name = p.product_name)");
$mainPrices = $stmt->fetchAll();

// Fetch dynamic prices for each product
$dynamicPrices = [];
foreach ($mainPrices as $mainPrice) {
    $stmt = $pdo->prepare("SELECT * FROM dynamicprices WHERE price_id = ?");
    $stmt->execute([$mainPrice['price_id']]);
    $dynamicPrices[$mainPrice['price_id']] = $stmt->fetchAll();
}

// Store the fetched data in sessions
session_start();
$_SESSION['mainprices'] = $mainPrices;
$_SESSION['dynamicprices'] = $dynamicPrices;

// Close the database connection
$pdo = null;

// Prepare data for JSON response
$response = [
    'mainprices' => $mainPrices,
    'dynamicprices' => $dynamicPrices
];

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>

