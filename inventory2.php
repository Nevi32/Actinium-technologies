<?php
require_once 'config.php';

// Check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit("Unauthorized");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inventory Management</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
  <style>
    body, html {
      height: 100%;
      margin: 0;
      font-family: 'Arial', sans-serif;
    }

    /* Sidebar Styles */
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

    /* Navigation Bar Styles */
    .navbar {
      background-color: #fff;
      padding: 10px 20px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      position: fixed;
      top: 0;
      left: 240px; /* Adjusted to match sidebar width */
      right: 0;
    }

    .navbar-container {
      display: flex;
      justify-content: flex-end;
      align-items: center;
    }

    .nav-button {
      background-color: #3498db;
      color: #fff;
      border: none;
      border-radius: 5px;
      padding: 8px 15px;
      margin-left: 10px;
      cursor: pointer;
    }

    .nav-button:first-child {
      margin-left: 0;
    }

    .nav-button:hover {
      background-color: #2980b9;
    }

    /* Content Styles */
    #content {
      margin-left: 260px; /* Adjusted to match sidebar width */
      padding-top: 60px; /* Adjusted to provide space from navbar */
    }

    /* Additional Styles */
    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      padding: 8px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    th {
      background-color: #f2f2f2;
    }

    /* Additional style for the cleared button */
    .cleared-button {
      background-color: #4CAF50; /* Green */
    }

  </style>
</head>
<body>
  <!-- Sidebar -->
  <div id="sidebar">
    <a href="#" id="user-icon" onclick="toggleUserInfo();"><i class="fas fa-user"></i> User</a>
    <div id="user-info">
      <?php
      echo "User ID: " . $_SESSION['user_id'] . "<br>Username: " . $_SESSION['username'] . "<br>Role: " . $_SESSION['role'];

      if ($_SESSION['role'] === 'owner' || $_SESSION['comp_staff'] == 1) {
        echo "<br>Store Name: " . $_SESSION['store_name'] . "<br>Location: " . $_SESSION['location_name'];
      }
      ?>
    </div>
    <a href="fetch_notifications.php"><i class="fas fa-bell"></i> Notifications</a>
    <a href="mpesa-c2b.html"><i class="fas fa-coins"></i> Mpesa C2B</a>
    <a href="mpesa-b2b.html"><i class="fas fa-exchange-alt"></i> Mpesa B2B</a>
    <a href="home.php" onclick="redirectToPage('hom.php');"><i class="fas fa-home"></i> Dashboard</a>
    <a href="#" id="logoutLink" onclick="logout();"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </div>
  <!-- Navigation Bar -->
<nav class="navbar">
  <div class="navbar-container">
    <button class="nav-button" onclick="viewRestockOrders()">View Restock Orders</button>
    <button class="nav-button" onclick="redirectToInventory()">View Inventory</button>
  </div>
</nav>


  <!-- Content -->
  <div id="content">
    <!-- Explanation for when the page first loads -->
    <p>Welcome to the Restock Orders page. Click the button below to view restock orders.</p>
    <!-- JavaScript populates the restock orders here -->
  </div>

  <!-- JavaScript -->
  <script>
    function toggleUserInfo() {
      var userInfo = document.getElementById('user-info');
      userInfo.style.display = (userInfo.style.display === 'none') ? 'block' : 'none';
    }

    function viewRestockOrders() {
      fetch('fetchrestock.php')
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(data => {
          populateRestockOrders(data);
        })
        .catch(error => {
          console.error('There was a problem with the fetch operation:', error);
        });
    }

    function populateRestockOrders(data) {
      const content = document.getElementById('content');
      content.innerHTML = '<h2>Restock Orders</h2>';
      if (data.length === 0) {
        content.innerHTML += '<p>No restock orders found</p>';
      } else {
        let tableHTML = '<table><thead><tr><th>Product Name</th><th>Category</th><th>Price</th><th>Quantity</th><th>Quantity Description</th><th>Status</th></tr></thead><tbody>';
        data.forEach(order => {
          let statusButton = order.cleared === 1 ? '<button class="cleared-button" style="pointer-events:none;">Cleared</button>' : '<button onclick="confirmArrival(' + order.order_id + ')" style="background-color: red;">Pending</button>';
          tableHTML += `<tr><td>${order.product_name}</td><td>${order.category}</td><td>${order.price}</td><td>${order.quantity}</td><td>${order.quantity_description}</td><td>${statusButton}</td></tr>`;
        });
        tableHTML += '</tbody></table>';
        content.innerHTML += tableHTML;
      }
    }

    function confirmArrival(order_id) {
      if (confirm("Did the product arrive?")) {
        fetch('recordsatstoreinventory.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'order_id=' + order_id + '&confirm=yes',
        })
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.text();
        })
        .then(data => {
          alert(data); // Show success message
          // Refresh the restock orders after success
          viewRestockOrders();
        })
        .catch(error => {
          console.error('There was a problem with the fetch operation:', error);
          alert('There was an error processing your request.');
        });
      }
    }
    function redirectToInventory() {
  window.location.href = 'fetchinventory2.php';
}

  </script>
</body>
</html>

