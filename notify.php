<?php

require('config.php');

// Function to send notification using Ntfy API
function sendNotification($message) {
  // Send notification logic remains the same
}

try {
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get store name and subscription from the form
    $storeName = $_POST['storename'];
    $subscription = $_POST['subscription'];

    // Send welcome message with emojis (only if subscription is new)
    $welcomeMessage = "Welcome to the $storeName notifications subscription! 🎉📢";
    $welcomeSent = false;

    // Connect to the database
    $conn = new PDO(
      "mysql:host=" . $databaseConfig['host'] . ";dbname=" . $databaseConfig['dbname'],
      $databaseConfig['user'],
      $databaseConfig['password']
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch store IDs associated with the store name
    $sql = "SELECT store_id FROM stores WHERE store_name = :store_name";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':store_name', $storeName);
    $stmt->execute();
    $storeIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Fetch notifications for selected store IDs
    $sql = "SELECT notification_id, subject, message FROM notifications WHERE is_sent = 0 AND store_id IN (" . implode(',', $storeIds) . ")";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Loop through each notification
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      // Add appropriate emoji based on the message content
      $message = $row['subject'] . " - " . $row['message'] . " ";
      if (strpos($row['message'], 'inventory') !== false) {
        $message .= "📦";
      } elseif (strpos($row['message'], 'cleared_orders') !== false) {
        $message .= "✅";
      }

      sendNotification($message);

      // Update is_sent status to 1
      $updateSql = "UPDATE notifications SET is_sent = 1 WHERE notification_id = :id";
      $updateStmt = $conn->prepare($updateSql);
      $updateStmt->bindParam(':id', $row['notification_id'], PDO::PARAM_INT);
      $updateStmt->execute();

      // If welcome message hasn't been sent yet, send it now
      if (!$welcomeSent) {
        sendNotification($welcomeMessage);
        $welcomeSent = true;
      }
    }

    echo "Notifications sent successfully!";
  }
} catch (PDOException $e) {
  echo "Error: " . $e->getMessage();
  error_log("Database error: " . $e->getMessage()); // Log database errors
}

$conn = null; // Close connection
?>

