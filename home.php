<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Store Management Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
  <style>
    body, html {
      height: 100%;
      margin: 0;
      font-family: 'Arial', sans-serif;
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

    #sidebar a:not(:last-child) {
      margin-bottom: 60px;
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
    }

    .welcome-message {
      margin-bottom: 20px;
    }

    .row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 20px;
    }

    .card {
      width: calc(50% - 20px);
      height: 200px;
      margin: 10px;
      padding: 20px;
      border-radius: 15px;
      text-align: center;
      cursor: pointer;
      transition: background-color 0.3s ease-in-out;
    }

    .card:hover {
      background-color: #eee;
    }

    .inventory {
      background-color: #3498db;
      color: #fff;
    }

    .sales {
      background-color: #2ecc71;
      color: #fff;
    }

    .orders {
      background-color: #e74c3c;
      color: #fff;
    }

    .stats {
      background-color: #f39c12;
      color: #fff;
    }

    .mpesa-c2b {
      background-color: #9b59b6;
      color: #fff;
    }

    .mpesa-b2b {
      background-color: #e67e22;
      color: #fff;
    }

    .card h3 {
      margin: 0;
      font-size: 1.5em;
    }

    .card p {
      margin: 10px 0;
      font-size: 1em;
    }
  </style>
</head>
<body>
  <?php
  // Start the session to access session variables
  session_start();

  // Check if the user is logged in
  if (!isset($_SESSION['user_id'])) {
      header("Location: login.html");
      exit();
  }
  ?>
  <div id="dashboard">
    <div id="sidebar">
      <div class="welcome-message" id="welcome-message">
        <?php
        // Populate welcome message with session data
        echo "Welcome to the " . $_SESSION['store_name'] . " System";
        ?>
      </div>
      <a href="#" id="user-icon" onclick="toggleUserInfo();"><i class="fas fa-user"></i> User</a>
      <div id="user-info">
        <?php
        // Populate user info with session data
        echo "User ID: " . $_SESSION['user_id'] . " <br> Username: " . $_SESSION['username'] . " <br> Role: " . $_SESSION['role'];

        // Additional info for owner or comp staff
        if ($_SESSION['role'] === 'owner' || $_SESSION['comp_staff'] == 1) {
          echo "<br> Store Name: " . $_SESSION['store_name'] . " <br> Location: " . $_SESSION['location_name'];
        }
        ?>
      </div>
      <a href="fetch_notifications.php"><i class="fas fa-bell"></i> Notifications</a>
      <a href="mpesa-c2b.html"><i class="fas fa-coins"></i> Mpesa C2B</a>
      <a href="mpesa-b2b.html"><i class="fas fa-exchange-alt"></i> Mpesa B2B</a>
      <a href="home.php" onclick="redirectToPage('hom.php');"><i class="fas fa-home"></i> Dashboard</a>
      <a href="#" id="logoutLink"><i class="fas fa-sign-out-alt"></i> Logout</a>  
    </div>

    <div id="content">
      <div class="row">
        <div class="card inventory" onclick="redirectToPage('inventory.php');">
          <h3>Inventory</h3>
          <p>You can record and view your inventory here.</p>
        </div>
        <div class="card sales" onclick="redirectToPage('sales.html');">
          <h3>Sales</h3>
          <p>Track and analyze your sales data.</p>
        </div>
      </div>
      <div class="row">
        <div class="card orders" onclick="redirectToPage('orders.html');">
          <h3>Orders</h3>
          <p>Manage and process salary payments and stock orders efficiently.</p>
        </div>
        <div class="card stats" onclick="redirectToPage('fetchdata.php');">
          <h3>Stats</h3>
          <p>Explore detailed statistics and reports.</p>
        </div>
      </div>
    </div>
  </div>

  <script>
    function toggleUserInfo() {
      var userInfo = document.getElementById('user-info');
      userInfo.style.display = (userInfo.style.display === 'none') ? 'block' : 'none';
    }

    function redirectToPage(page) {
      // Redirect to the specified page with user info
      var urlParams = new URLSearchParams(window.location.search);
      var userId = urlParams.get('user_id') || '';
      var username = urlParams.get('username') || '';
      var role = urlParams.get('role') || '';
      var storeName = urlParams.get('store_name') || '';
      var locationName = urlParams.get('location_name') || ''; // Add location info

      window.location.href = page + '?user_id=' + userId + '&username=' + username + '&role=' + role + '&store_name=' + storeName + '&location_name=' + locationName;
    }
    document.addEventListener('DOMContentLoaded', function() {
    var role = "<?php echo $_SESSION['role']; ?>";

    // Function to toggle visibility of elements based on user role
    function toggleElements() {
      var cards = document.querySelectorAll('.card');
      var sidebarLinks = document.querySelectorAll('#sidebar a');

       if (role === 'staff') {
        // If the user is a staff, adjust layout for the two cards
        cards.forEach(function(card) {
          if (card.classList.contains('inventory') || card.classList.contains('sales')) {
            card.style.width = '100%'; // Make cards take full width
            card.style.height = '600px'; // Set the desired height
            card.style.marginBottom = '20px'; // Add some space between the cards
          } else {
            card.style.display = 'none'; // Hide other cards
          }
        });
        // Disable sidebar links except for logout and user info
        sidebarLinks.forEach(function(link) {
          if (!link.id.includes('logoutLink') && !link.id.includes('user-icon')) {
            link.style.pointerEvents = 'none';
            link.style.color = '#999'; // Optional: Change the color to indicate it's disabled
          }
        });
      } else {
        // For any role other than 'staff', assume 'owner' in this case
        // All cards and sidebar links are accessible
        cards.forEach(function(card) {
          card.style.display = 'block';
        });

        sidebarLinks.forEach(function(link) {
          link.style.pointerEvents = 'auto';
          link.style.color = '#fff'; // Reset the color
        });
      }
    }

    // Call the function on page load
    toggleElements();
  });
    document.getElementById('logoutLink').addEventListener('click', function(event) {
      // Prevent the default behavior of the link
      event.preventDefault();

      // Redirect the user to the logout.php file for logout
      window.location.href = 'logout.php';
    });
  </script>
</body>
</html>
