<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notification Subscription</title>
<style>
  body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 20px;
  }
  label {
    display: block;
    margin-bottom: 10px;
  }
</style>
</head>
<body>

<h2>Notification Subscription</h2>
<form action="notification.php" method="post">
  <label for="subscription">Select Subscription:</label>
  <select name="subscription" id="subscription">
    <option value="Duka1">Lucys Duka</option>
    <option value="Duka2">Duka</option>
  </select>
  <button type="submit">Subscribe</button>
</form>

<?php
require('config.php');

// Function to send notification using Ntfy API
function sendNotification($message) {
  // Send notification logic remains the same
}

try {
  // Connect to the database
  $conn = new PDO(
    "mysql:host=" . $databaseConfig['host'] . ";dbname=" . $databaseConfig['dbname'],
    $databaseConfig['user'],
    $databaseConfig['password']
  );
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get selected subscription from the form
    $subscription = $_POST['subscription'];

    // Fetch store IDs associated with selected subscription
    $sql = "SELECT store_id FROM stores WHERE store_name = :store_name";
    $stmt = $conn->prepare($sql);
    
    if ($subscription === 'Duka1') {
      $storeName = 'Lucys Duka';
    } else if ($subscription === 'Duka2') {
      $storeName = 'Duka';
    }

    $stmt->bindParam(':store_name', $storeName);
    $stmt->execute();
    $storeIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Fetch unsent notifications for selected store IDs
    $sql = "SELECT notification_id, subject, message FROM notifications WHERE is_sent = 0 AND store_id IN (" . implode(',', $storeIds) . ")";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Loop through each notification
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $message = $row['subject'] . " - " . $row['message'] . " "; // Add emoji to the message
      sendNotification($message);

      // Update is_sent status to 1
      $updateSql = "UPDATE notifications SET is_sent = 1 WHERE notification_id = :id";
      $updateStmt = $conn->prepare($updateSql);
      $updateStmt->bindParam(':id', $row['notification_id'], PDO::PARAM_INT);
      $updateStmt->execute();
    }

    echo "<p>Notifications sent successfully!</p>";
  }
} catch (PDOException $e) {
  echo "<p>Error: " . $e->getMessage() . "</p>";
  error_log("Database error: " . $e->getMessage()); // Log database errors
}

$conn = null; // Close connection
?>

</body>
</html>

