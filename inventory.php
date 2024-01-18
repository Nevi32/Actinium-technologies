<?php
session_start();

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login.html if the session is not active
    header("Location: login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
<style>
body, html {
  height: 100%;
  margin: 0;
  font-family: 'Arial', sans-serif;
}

#inventory-page {
  display: flex;
  height: 100%;
}

#sidebar {
  width: 220px;
  background-color: #333;
  color: #fff;
  padding: 20px;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  justify-content: flex-start;
}

#sidebar a {
  color: #fff;
  text-decoration: none;
  margin-bottom: 15px;
  width: 100%;
  display: flex;
  align-items: center;
  transition: background-color 0.3s;
}

#sidebar i {
  margin-right: 10px;
  transition: transform 0.2s;
}

#sidebar a:not(:last-child) {
  margin-bottom: 60px;
}

#sidebar a:hover {
  background-color: #555;
}

#sidebar a:hover i {
  transform: translateY(-3px);
}

#user-icon {
  margin-bottom: 20px;
}

#user-info {
  display: none;
  color: #fff;
  margin-top: 10px;
  padding: 10px;
  background-color: #555;
  border-radius: 15px;
}

#content {
  flex: 1;
  padding: 20px;
}

.section-card {
  width: 100%;
  padding: 15px;
  border-radius: 10px;
  margin-bottom: 20px;
  background-color: #3498db;
  transition: transform 0.3s, box-shadow 0.3s;
}

.section-card h3 {
  color: #fff;
  margin-bottom: 10px;
}

.section-card label {
  color: #fff;
  margin-right: 10px;
  font-weight: bold;
}

.section-card input {
  width: calc(100% - 12px);
  padding: 8px;
  margin-bottom: 15px;
  border: none;
  border-radius: 5px;
}

.section-card button {
  width: calc(100% - 12px);
  padding: 8px;
  border: none;
  background-color: #fff;
  color: #3498db;
  border-radius: 5px;
  cursor: pointer;
}

.section-card button:hover {
  background-color: #eee;
}

.section-card:hover {
  transform: scale(1.02);
  box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
}

.clickable-card {
  cursor: pointer;
  background-color: #2ecc71;
  color: #fff;
  border-radius: 8px;
  transition: transform 0.3s, box-shadow 0.3s;
}

.clickable-card:hover {
  transform: scale(1.02);
  box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
}

.clickable-card h3 {
  margin-bottom: 10px;
}

.entry-card {
  width: 50%;
  padding: 10px;
  border-radius: 15px;
  background-color: #3498db;
  margin-bottom: 20px;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.entry-card label {
  color: #fff;
  margin-right: 10px;
  font-weight: bold;
  flex: 1;
}

.entry-card input {
  flex: 3;
  padding: 10px;
  margin-bottom: 15px;
  border: none;
  border-radius: 5px;
}

.entry-card button {
  flex: 1;
  padding: 10px;
  border: none;
  background-color: #fff;
  color: #3498db;
  border-radius: 5px;
  cursor: pointer;
}

.entry-card button:hover {
  background-color: #eee;
}

.alert {
  background-color: #4CAF50;
  color: white;
  padding: 10px;
  margin-bottom: 15px;
  border-radius: 5px;
  display: none;
}

.popup {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background-color: #333;
  color: #fff;
  padding: 15px;
  border-radius: 5px;
  display: none;
  z-index: 999;
}

</style>
</head>
<body>

<!-- Pop-up messages -->
<div id="success-popup" class="popup" style="background-color: #4CAF50;"></div>
<div id="error-popup" class="popup" style="background-color: #f44336;"></div>

<div id="inventory-page">
    <div id="sidebar">
        <div class="welcome-message" id="welcome-message"></div>
        <a href="#" id="user-icon" onclick="toggleUserInfo();"><i class="fas fa-user"></i> User</a>
        <div id="user-info">
            <!-- User info will be displayed here using JavaScript -->
        </div>
        <a href="fetch_notifications.php"><i class="fas fa-bell"></i> Notifications</a>
        <a href="mpesa-c2b.html"><i class="fas fa-coins"></i> Mpesa C2B</a>
        <a href="mpesa-b2b.html"><i class="fas fa-exchange-alt"></i> Mpesa B2B</a>
        <a href="home.php" onclick="redirectToPage('home.php');"><i class="fas fa-home"></i> Dashboard</a>
        <a href="#" id="logoutLink"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div id="content">
        <div class="alert" id="alert-message"></div>
        <div class="section-card">
            <h3>Record Inventory</h3>
            <form id="inventory-form">
                <label for="productName">Product Name:</label>
                <input type="text" name="productName" placeholder="Product name" required>

                <label for="category">Category:</label>
                <input type="text" name="category" placeholder="Category">

                <label for="quantity">Quantity:</label>
                <input type="number" name="quantity" step="0.01" placeholder="Quantity" required>

                <label for="quantityDescription">Quantity Description:</label>
                <input type="text" name="quantityDescription" placeholder="Quantity description" required>

                <label for="price">Price:</label>
                <input type="number" name="price" step="0.01" placeholder="Price">

                <!-- Hidden input fields to store user information -->
                <input type="hidden" name="userId" value="<?php echo $_SESSION['user_id']; ?>">
                <input type="hidden" name="username" value="<?php echo $_SESSION['username']; ?>">
                <input type="hidden" name="role" value="<?php echo $_SESSION['role']; ?>">
                <input type="hidden" name="storeName" value="<?php echo $_SESSION['store_name'] ?? ''; ?>">
                <input type="hidden" name="locationName" value="<?php echo $_SESSION['location_name'] ?? ''; ?>">

                <button type="button" onclick="recordInventory();">Record Inventory</button>
            </form>
        </div>

        <div class="section-card clickable-card" onclick="viewInventory();">
            <h3>View Inventory</h3>
            <p>Click here to view and edit your inventory.</p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Check if the user is logged in
        <?php if (isset($_SESSION['user_id'])) : ?>
        // Fetch user details from the session
        var userId = <?php echo json_encode($_SESSION['user_id']); ?>;
        var username = <?php echo json_encode($_SESSION['username']); ?>;
        var role = <?php echo json_encode($_SESSION['role']); ?>;
        var storeName = <?php echo json_encode($_SESSION['store_name'] ?? ''); ?>;
        var locationName = <?php echo json_encode($_SESSION['location_name'] ?? ''); ?>;

        // Update welcome message
        var welcomeMessage = document.getElementById('welcome-message');
        welcomeMessage.innerText = "Welcome to " + storeName + "'s Inventory Page";

        // Update user info
        var userInfo = document.getElementById('user-info');
        userInfo.innerHTML = "User ID: " + userId + " <br> Username: " + username + " <br> Role: " + role + " <br> Location: " + locationName + " <br> Store: " + storeName;

        // Save user details for later use
        window.userId = userId;
        window.username = username;
        window.role = role;
        window.storeName = storeName;
        window.locationName = locationName;

        // Show user information section
        userInfo.style.display = 'none'; // Hide initially
        <?php else : ?>
        // Hide user information section
        var userInfo = document.getElementById('user-info');
        userInfo.style.display = 'none';
        <?php endif; ?>
    });

    function toggleUserInfo() {
        var userInfo = document.getElementById('user-info');
        userInfo.style.display = (userInfo.style.display === 'none') ? 'block' : 'none';
    }

    function recordInventory() {
        // AJAX request to recordinventory.php
        var formData = new FormData(document.getElementById('inventory-form'));

        // AJAX request
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'recordinventory.php', true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                showAlert('Inventory recorded successfully!', 'success');
            } else {
                showAlert('Failed to record inventory. Please try again.', 'error');
            }
        };
        xhr.send(formData);
    }

    function showAlert(message, type) {
        var popup = document.getElementById(type + '-popup');
        popup.innerText = message;
        popup.style.display = 'block';

        // Hide the pop-up after 3 seconds
        setTimeout(function () {
            popup.style.display = 'none';
        }, 3000);
    }

     function viewInventory() {
        // Retrieve store information from hidden fields
        var storeName = document.getElementsByName('storeName')[0].value;
        var locationName = document.getElementsByName('locationName')[0].value;

        // Redirect to fetchinventory.php with user information as query parameters
        var url = 'fetchinventory.php';
        url += '?userId=' + encodeURIComponent(window.userId);
        url += '&username=' + encodeURIComponent(window.username);
        url += '&role=' + encodeURIComponent(window.role);
        url += '&storeName=' + encodeURIComponent(storeName);
        url += '&locationName=' + encodeURIComponent(locationName);

        window.location.href = url;
    }

    document.getElementById('logoutLink').addEventListener('click', function (event) {
        // Prevent the default behavior of the link
        event.preventDefault();

        // Redirect the user to the logout.php file for logout
        window.location.href = 'logout.php';
    });
</script>
</body>
</html>

