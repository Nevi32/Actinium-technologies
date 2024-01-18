<?php
// Include the database configuration
include('config.php');

// Retrieve stock order information from the form
$supplierId = $_POST['supplier'];
$amount = $_POST['amount'];

// Fetch supplier information from the session (previously stored by fetchsuppliersinfo.php)
session_start();
$suppliersInfo = json_decode($_SESSION['suppliers_info'], true);

// Find the selected supplier in the fetched information
$selectedSupplier = array_filter($suppliersInfo, function ($supplier) use ($supplierId) {
    return $supplier['phone_number'] == $phone_number;
});

// Assuming $selectedSupplier is an array with a single supplier
$supplierName = reset($selectedSupplier)['supplier_name'];

// M-Pesa API integration
$ch = curl_init('https://sandbox.safaricom.co.ke/mpesa/b2c/v3/paymentrequest');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer SY1e4FtA15hv0B7sGVkEgm1vUWdU',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "OriginatorConversationID" => "0bbd15c3-cf75-45a5-a1a0-72da5bbc60bc",
    "InitiatorName" => "testapi",
    "SecurityCredential" => "GPE1zC5ntUL2KedSwFKa193L5egAHsJyX72wKR8fEuxj7pKvWjtpkW06LhWknIL46K50MssYFROrdGy+YPi/Sk0/BDpqdI7ke1Fr+rpalZ9OGll/7HiW22AMWjGhl3ilU8rdi+immg/QN1uuCYnUWcd25NKhJq/tuV8R/7aTlJ2zRENg1uBcrFhC2bxs81ntqLYwffS/PVzKVWEJbvX/JOqAhUlbWbZpkTHzd8IP+8NJc0o8ESzEDJ7TCBaa/ac0rz0hsDYMKX6exEbFLjc1QhITVOXrrMwMMVSWgW3RO420oiiekCwgQvjpOfVssdTQ6yKgrZaXea7Vv9J46HjKlw==",
    "CommandID" => "BusinessPayment",
    "Amount" => $amount,
    "PartyA" => 600984, // Your till number or account number
    "PartyB" => $phone_number, // Supplier's phone number or account number
    "Remarks" => "Payment to $supplierName for stock order",
    "QueueTimeOutURL" => "https://mydomain.com/b2c/queue",
    "ResultURL" => "https://mydomain.com/b2c/result",
    "Occasion" => "",
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
curl_close($ch);

// Decode the response to get the confirmation message
$responseData = json_decode($response, true);

// Store the confirmation message in the database
try {
    $dsn = 'mysql:host=' . $databaseConfig['host'] . ';dbname=' . $databaseConfig['dbname'];
    $pdo = new PDO($dsn, $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare and execute the SQL query to insert confirmation message
    $sql = 'INSERT INTO stock_orders (supplier_id, amount, confirmation_message) VALUES (:supplierId, :amount, :confirmationMessage)';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':supplierId', $supplierId, PDO::PARAM_INT);
    $stmt->bindParam(':amount', $amount, PDO::PARAM_INT);
    $stmt->bindParam(':confirmationMessage', $responseData['ResponseDescription'], PDO::PARAM_STR);
    $stmt->execute();

    // Display success notification
    echo json_encode(['status' => 'success', 'message' => 'Stock order placed successfully']);
    exit();
} catch (PDOException $e) {
    // Display error notification
    echo json_encode(['status' => 'error', 'message' => 'Error placing stock order: ' . $e->getMessage()]);
    exit();
}
?>
<?php
// Include the database configuration
include('config.php');

// Retrieve stock order information from the form
$supplierId = $_POST['supplier'];
$amount = $_POST['amount'];

// Fetch supplier information from the session (previously stored by fetchsuppliersinfo.php)
session_start();
$suppliersInfo = json_decode($_SESSION['suppliers_info'], true);

// Find the selected supplier in the fetched information
$selectedSupplier = array_filter($suppliersInfo, function ($supplier) use ($supplierId) {
    return $supplier['supplier_id'] == $supplierId;
});

// Assuming $selectedSupplier is an array with a single supplier
$supplierName = reset($selectedSupplier)['supplier_name'];

// M-Pesa API integration
$ch = curl_init('https://sandbox.safaricom.co.ke/mpesa/b2c/v3/paymentrequest');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer SY1e4FtA15hv0B7sGVkEgm1vUWdU',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "OriginatorConversationID" => "0bbd15c3-cf75-45a5-a1a0-72da5bbc60bc",
    "InitiatorName" => "testapi",
    "SecurityCredential" => "GPE1zC5ntUL2KedSwFKa193L5egAHsJyX72wKR8fEuxj7pKvWjtpkW06LhWknIL46K50MssYFROrdGy+YPi/Sk0/BDpqdI7ke1Fr+rpalZ9OGll/7HiW22AMWjGhl3ilU8rdi+immg/QN1uuCYnUWcd25NKhJq/tuV8R/7aTlJ2zRENg1uBcrFhC2bxs81ntqLYwffS/PVzKVWEJbvX/JOqAhUlbWbZpkTHzd8IP+8NJc0o8ESzEDJ7TCBaa/ac0rz0hsDYMKX6exEbFLjc1QhITVOXrrMwMMVSWgW3RO420oiiekCwgQvjpOfVssdTQ6yKgrZaXea7Vv9J46HjKlw==",
    "CommandID" => "BusinessPayment",
    "Amount" => $amount,
    "PartyA" => 600984, // Your till number or account number
    "PartyB" => $supplierId, // Supplier's phone number or account number
    "Remarks" => "Payment to $supplierName for stock order",
    "QueueTimeOutURL" => "https://mydomain.com/b2c/queue",
    "ResultURL" => "https://mydomain.com/b2c/result",
    "Occasion" => "",
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
curl_close($ch);

// Decode the response to get the confirmation message
$responseData = json_decode($response, true);

// Store the confirmation message in the database
try {
    $dsn = 'mysql:host=' . $databaseConfig['host'] . ';dbname=' . $databaseConfig['dbname'];
    $pdo = new PDO($dsn, $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare and execute the SQL query to insert confirmation message
    $sql = 'INSERT INTO stock_orders (supplier_id, amount, confirmation_message) VALUES (:supplierId, :amount, :confirmationMessage)';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':supplierId', $supplierId, PDO::PARAM_INT);
    $stmt->bindParam(':amount', $amount, PDO::PARAM_INT);
    $stmt->bindParam(':confirmationMessage', $responseData['ResponseDescription'], PDO::PARAM_STR);
    $stmt->execute();

    // Display success notification
    echo json_encode(['status' => 'success', 'message' => 'Stock order placed successfully']);
    exit();
} catch (PDOException $e) {
    // Display error notification
    echo json_encode(['status' => 'error', 'message' => 'Error placing stock order: ' . $e->getMessage()]);
    exit();
}
?>

