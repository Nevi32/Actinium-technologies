<?php
// Database configuration
$databaseConfig = [
    'host' => 'localhost',
    'dbname' => 'StoreY11',
    'user' => 'nevill',
    'password' => '7683Nev!//'
];

// Parse JSON input
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON);

if ($input && isset($input->notificationId)) {
    try {
        $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Update is_sent status in the database
        $stmt = $pdo->prepare("UPDATE notifications SET is_sent = 1 WHERE notification_id = :notificationId");
        $stmt->bindParam(':notificationId', $input->notificationId, PDO::PARAM_INT);
        $stmt->execute();

        // Return success response
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        // Return error response
        echo json_encode(['error' => 'Failed to mark notification as sent: ' . $e->getMessage()]);
    }
} else {
    // Return error response
    echo json_encode(['error' => 'Invalid input']);
}
?>

