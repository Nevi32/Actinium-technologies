<?php

require('config.php'); // Include the database configuration

// Function to send notification using Ntfy API
function sendNotification($message) {
  $url = "https://ntfy.sh/Duka1"; // Set subscription topic to "Duka1"
  $context = stream_context_create([
    'http' => [
      'method' => 'POST',
      'header' => 'Content-Type: text/plain',
      'content' => $message,
    ]
  ]);
  
  // Send the message and handle potential errors
  $result = file_get_contents($url, false, $context);
  if ($result === false) {
    error_log("Failed to send notification: " . $http_response_header[0]);
  }
}

try {
  // Connect to the database
  $conn = new PDO(
    "mysql:host=" . $databaseConfig['host'] . ";dbname=" . $databaseConfig['dbname'],
    $databaseConfig['user'],
    $databaseConfig['password']
  );
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Fetch unsent notifications
  $sql = "SELECT notification_id, subject, message FROM notifications WHERE is_sent = 0";
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

  echo "Notifications sent successfully!";
} catch (PDOException $e) {
  echo "Error: " . $e->getMessage();
  error_log("Database error: " . $e->getMessage()); // Log database errors
}

$conn = null; // Close connection
?>

