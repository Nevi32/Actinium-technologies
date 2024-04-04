<?php
require_once 'config.php';

// Function to connect to the database
function connectDatabase() {
    global $databaseConfig;
    $dsn = 'mysql:host=' . $databaseConfig['host'] . ';dbname=' . $databaseConfig['dbname'];
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    try {
        $pdo = new PDO($dsn, $databaseConfig['user'], $databaseConfig['password'], $options);
        return $pdo;
    } catch (PDOException $e) {
        exit("Database connection failed: " . $e->getMessage());
    }
}

// Function to fetch notifications with is_sent status 0
function fetchNotifications() {
    global $pdo;
    $pdo = connectDatabase();
    $query = "SELECT * FROM notifications WHERE is_sent = 0";
    $statement = $pdo->query($query);
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

// Function to send notification messages through ntfy API
function sendNotifications($notifications) {
    global $pdo;
    foreach ($notifications as $notification) {
        $storeId = $notification['store_id'];
        $storeName = $notification['store_name'];
        $subject = '';
        $message = $notification['message'];
        $productDetails = $notification['product_details'];
        
        // Prepare subject message from PHP script
        if (strpos($notification['subject'], 'Sales') !== false) {
            $subject = "New sales have been made at your store!\n";
        } elseif (strpos($notification['subject'], 'Inventory Update') !== false) {
            $subject = "New inventory has been restocked at your store!\n";
        } elseif (strpos($notification['subject'], 'Inventory Status') !== false) {
            $subject = "There is an update on your inventory status!\n";
        }
        
        // Concatenate subject message with message from notifications table
        $fullMessage = $subject . $message . "\n" . $productDetails;

        // Send notification message via ntfy API
        $url = "https://ntfy.sh/$storeName";
        $data = array('Content-Type: text/plain', 'content' => $fullMessage);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => implode("\r\n", $data),
                'content' => $fullMessage
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        // Check if notification sent successfully
        if ($result === false) {
            echo "Failed to send notification for store ID: $storeId ($storeName)<br>";
        } else {
            // Update is_sent status in the database
            $notificationId = $notification['notification_id'];
            updateSentStatus($pdo, $notificationId);
            echo "Notification sent successfully for store ID: $storeId ($storeName)<br>";
        }
    }
}

// Function to update is_sent status in the notifications table
function updateSentStatus($pdo, $notificationId) {
    $query = "UPDATE notifications SET is_sent = 1 WHERE notification_id = :notificationId";
    $statement = $pdo->prepare($query);
    $statement->bindParam(':notificationId', $notificationId);
    $statement->execute();
}

// Fetch notifications with is_sent status 0
$notifications = fetchNotifications();

// Send notifications through ntfy API
if (!empty($notifications)) {
    sendNotifications($notifications);
    echo "Notifications have been successfully sent!";
} else {
    echo "No notifications with is_sent status 0 found.";
}
?>

