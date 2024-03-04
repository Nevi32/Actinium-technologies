<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notification Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
  <style>
  /* Notifications Page Styles */

body, html {
  height: 100%;
  margin: 0;
  font-family: Arial, sans-serif;
}

#dashboard {
  display: flex;
  height: 100%;
}

#sidebar {
  width: 200px;
  background-color: #333;
  color: #fff;
  padding: 20px;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  position: fixed;
  height: 100%;
}

#sidebar a {
  color: #fff;
  text-decoration: none;
  margin-bottom: 15px;
  width: 100%;
  display: flex;
  align-items: center;
}

#sidebar i {
  margin-right: 10px;
}

#user-info {
  display: none;
  color: #fff;
  margin-top: 5px;
  padding: 10px;
  background-color: #555;
  border-radius: 15px;
}

#content {
  flex: 1;
  padding: 20px;
  margin-left: 220px;
  z-index: 1; /* Ensure content appears above sidebar */
}

/* Notification Button Styles */
#allowNotificationsButton {
  padding: 10px 20px;
  background-color: #3498db;
  color: #fff;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 16px;
  margin-bottom: 20px;
}

#allowNotificationsButton:hover {
  background-color: #2980b9;
}

/* Notification Message Styles */
.notification {
  padding: 10px;
  background-color: #f9f9f9;
  border: 1px solid #ddd;
  margin-bottom: 10px;
}

.notification h4 {
  margin-top: 0;
  color: #333;
}

.notification p {
  margin-bottom: 5px;
  color: #666;
}


  </style>
</head>
<body>
  <div id="dashboard">    
    <div id="sidebar">
      <a href="#" id="user-icon" onclick="toggleUserInfo();"><i class="fas fa-user"></i> User</a>
      <div id="user-info">
        <?php
        session_start();
        if (!isset($_SESSION['user_id'])) {
          header("Location: login.html");
          exit();
        }
        echo "User ID: " . $_SESSION['user_id'] . " <br> Username: " . $_SESSION['username'] . " <br> Role: " . $_SESSION['role'];
        if ($_SESSION['role'] === 'owner' || $_SESSION['comp_staff'] == 1) {
          echo "<br> Store Name: " . $_SESSION['store_name'] . " <br> Location: " . $_SESSION['location_name'];
        }
        ?>
      </div>
      <a href="fetch_notifications.php"><i class="fas fa-bell"></i> Notifications</a>
      <!-- Add other sidebar links as needed -->
    </div>
    <div id="content">
      <div class="welcome-message" id="welcome-message">
        <?php
        echo "Welcome to the Notification Dashboard";
        ?>
      </div>
      <!-- Button to allow notifications -->
      <button id="allowNotificationsButton">Allow Notifications</button>
    </div>
  </div>
  <script>
   // JavaScript functions here
function toggleUserInfo() {
  var userInfo = document.getElementById('user-info');
  userInfo.style.display = (userInfo.style.display === 'none') ? 'block' : 'none';
}

// Function to request permission for notifications
function requestNotificationPermission() {
  if ('Notification' in window) {
    Notification.requestPermission().then(function(permission) {
      if (permission === 'granted') {
        // Send welcome notification when permission is granted
        sendWelcomeNotification();
        console.log('Notification permission granted');
      }
    });
  }
}

// Function to send welcome notification
function sendWelcomeNotification() {
  new Notification("Welcome to the AutoSmS notification platform! 🚀");
}

// Function to handle incoming push notifications
function handlePushNotification(notification) {
  alert(notification.subject + ': ' + notification.message);
}

// Function to fetch notifications from the server
function fetchNotifications() {
  // Replace this with your actual AJAX call
  console.log('Fetching notifications...');
}

// Check notification permission and show content accordingly
document.addEventListener('DOMContentLoaded', function() {
  if ('Notification' in window) {
    if (Notification.permission === 'default') {
      document.getElementById('allowNotificationsButton').style.display = 'block';
    } else if (Notification.permission === 'granted') {
      fetchNotifications();
    }
  }
});

// Event listener for the allow notifications button
document.getElementById('allowNotificationsButton').addEventListener('click', function() {
  requestNotificationPermission();
  this.style.display = 'none';
  fetchNotifications();
});

</script> <!-- Include script.js file -->
</body>
</html>

