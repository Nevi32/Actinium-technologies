<?php
session_start();
if (!isset($_SESSION['user_id'])) {
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
    /* Add any additional styles here */
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
      <button class="nav-button" onclick="viewInventory()">View Inventory</button>
    </div>
  </nav>

  <!-- Content -->
  <div id="content">
    <!-- Content area where inventory restock list will be populated -->
    <!-- Empty for now -->
  </div>

  <!-- JavaScript -->
  <script>
    function toggleUserInfo() {
      var userInfo = document.getElementById('user-info');
      userInfo.style.display = (userInfo.style.display === 'none') ? 'block' : 'none';
    }

    // Function to populate user info
    function populateUserInfo() {
      // Fetch user info from the server-side or session variables
      // and populate the user-info section in the sidebar
      // Example:
      // document.getElementById('user-id').innerText = "User ID: <?php echo $_SESSION['user_id']; ?>";
      // document.getElementById('username').innerText = "Username: <?php echo $_SESSION['username']; ?>";
      // document.getElementById('role').innerText = "Role: <?php echo $_SESSION['role']; ?>";
    }

    // Call the function to populate user info on page load
    populateUserInfo();
    
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
        content.innerHTML += '<table><thead><tr><th>Order ID</th><th>Product Name</th><th>Category</th><th>Quantity</th></tr></thead><tbody>';
        data.forEach(order => {
          content.innerHTML += `<tr><td>${order.order_id}</td><td>${order.product_name}</td><td>${order.category}</td><td>${order.quantity}</td></tr>`;
        });
        content.innerHTML += '</tbody></table>';
      }
    }
  </script>
</body>
</html>

