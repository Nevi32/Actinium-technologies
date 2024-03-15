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

// Fetch latest prices for each product and category combination
$stmt = $pdo->query("SELECT * FROM prices p WHERE (p.product_name, p.category, p.set_date) IN (SELECT product_name, category, MAX(set_date) FROM prices GROUP BY product_name, category)");
$prices = $stmt->fetchAll();

// Fetch dynamic prices for each price ID
$dynamicPrices = [];
foreach ($prices as $price) {
    $stmt = $pdo->prepare("SELECT * FROM dynamicprices WHERE price_id = ?");
    $stmt->execute([$price['price_id']]);
    $dynamicPrices[$price['price_id']] = $stmt->fetchAll();
}

// Close the database connection
$pdo = null;

// Prepare data for JSON response
$response = [
    'mainprices' => $prices,
    'dynamicprices' => $dynamicPrices
];

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>

