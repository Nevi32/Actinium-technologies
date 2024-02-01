<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sale Management Dashboard</title>
  <!-- Include necessary CSS and font-awesome library -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
  <style>
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
            width: 100%; /* Adjusted to match the width of the input fields */
            padding: 15px;
            border-radius: 10px;
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
            padding: 8px;
            margin-bottom: 15px;
            border: none;
            border-radius: 5px;
        }

        .entry-card button {
            flex: 1;
            padding: 8px;
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
      <div id="sales-form" class="section-card">
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
      <div id="view-sales-card" class="section-card" onclick="viewSales()">
        <h3>View Sales</h3>
        <p>Click here to view sales.</p>
      </div>
    </div>
  </div>

  <!-- Include necessary JavaScript -->
  <script>
    // Include necessary JavaScript
    // Function to redirect to view sales page
    function viewSales() {
      window.location.href = 'whichsales.php';
    }

    // Function to fetch staff names associated with the store
    function fetchStaffNames() {
      var xhr = new XMLHttpRequest();
      xhr.open('GET', 'fetchstaff2.php', true);
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

    // Function to handle response messages
    function handleResponseMessages() {
      <?php
      session_start();
      if (isset($_SESSION['success_message'])) {
        echo "alert('" . $_SESSION['success_message'] . "')";
        unset($_SESSION['success_message']);
      }
      if (isset($_SESSION['error_message'])) {
        echo "alert('" . $_SESSION['error_message'] . "')";
        unset($_SESSION['error_message']);
      }
      ?>
    }

    // Call fetchStaffNames and handleResponseMessages functions on page load
    document.addEventListener('DOMContentLoaded', function() {
      fetchStaffNames();
      handleResponseMessages();
    });
  </script>
</body>
</html>

