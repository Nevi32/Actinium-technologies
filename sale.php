<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sale Management Dashboard</title>
  <!-- Include necessary CSS and font-awesome library -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
  <style>
    /* Body CSS */
    body, html {
      height: 100%;
      margin: 0;
      font-family: 'Arial', sans-serif;
    }

    #sales-page {
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
      margin-bottom: 15px;
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
      color: #fff;
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
      cursor: pointer;
    }

    .section-card h3 {
      color: #fff;
      margin-bottom: 10px;
    }

    .section-card p {
      color: #fff;
    }

    .section-card:hover {
      transform: scale(1.02);
      box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
    }

  </style>
</head>
<body>
  <div id="sales-page">
    <div id="sidebar">
      <a href="#" id="user-icon"><i class="fas fa-user"></i> User</a>
      <div id="user-info">
        <?php
        session_start();
        if (isset($_SESSION['user_id'])) {
          echo "User ID: " . $_SESSION['user_id'] . "<br>Username: " . $_SESSION['username'] . "<br>Role: " . $_SESSION['role'];
          if ($_SESSION['role'] === 'owner' || $_SESSION['comp_staff'] == 1) {
            echo "<br>Store Name: " . $_SESSION['store_name'] . "<br>Location: " . $_SESSION['location_name'];
          }
        }
        ?>
      </div>
      <a href="notifications.html"><i class="fas fa-bell"></i> Notifications</a>
      <a href="mpesa-c2b.html"><i class="fas fa-coins"></i> Mpesa C2B</a>
      <a href="mpesa-b2b.html"><i class="fas fa-exchange-alt"></i> Mpesa B2B</a>
      <a href="home.php"><i class="fas fa-home"></i> Dashboard</a>
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div id="content">
      <!-- Input Form Card -->
      <div class="section-card">
        <h3>Record Sales</h3>
        <form action="process_sale.php" method="POST">
          <div>
            <label for="product_name">Product Name:</label>
            <input type="text" id="product_name" name="product_name" required>
          </div>
          <div>
            <label for="category">Category:</label>
            <input type="text" id="category" name="category" required>
          </div>
          <div>
            <label for="staff">Staff:</label>
            <select id="staff" name="staff" required>
              <!-- Staff names will be populated dynamically using JavaScript -->
            </select>
          </div>
          <div>
            <label for="quantity_sold">Quantity Sold:</label>
            <input type="number" id="quantity_sold" name="quantity_sold" required>
          </div>
          <div>
            <label for="total_price">Total Price:</label>
            <input type="number" id="total_price" name="total_price" required>
          </div>
          <button type="submit">Record Sale</button>
        </form>
      </div>

      <!-- View Sales Card -->
      <div class="section-card" onclick="viewSales()">
        <h3>View Sales</h3>
        <p>Click here to view sales.</p>
      </div>
    </div>
  </div>

  <!-- Include necessary JavaScript -->
  <script>
    // Function to redirect to view sales page
    function viewSales() {
      window.location.href = 'whichsales.php';
    }

    // Function to fetch staff names associated with the store
    function fetchStaffNames() {
      var xhr = new XMLHttpRequest();
      xhr.open('GET', 'fetchstaff.php', true);
      xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 400) {
          var staffNames = JSON.parse(xhr.responseText);
          var staffDropdown = document.getElementById('staff');
          staffNames.forEach(function(name) {
            var option = document.createElement('option');
            option.value = name;
            option.textContent = name;
            staffDropdown.appendChild(option);
          });
        } else {
          console.error('Failed to fetch staff names');
        }
      };
      xhr.onerror = function() {
        console.error('Connection error');
      };
      xhr.send();
    }

    // Call fetchStaffNames function on page load
    document.addEventListener('DOMContentLoaded', function() {
      fetchStaffNames();
    });
  </script>
</body>
</html>

