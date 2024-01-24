<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Restock Satellite Stores</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
  <style>
 /* Global styles */
body, html {
  height: 100%;
  margin: 0;
  font-family: 'Arial', sans-serif;
}

/* Dashboard layout */
#dashboard {
  display: flex;
  height: 100%;
}

/* Sidebar styles */
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
  margin-bottom: 25px;
  width: 100%;
  display: flex;
  align-items: center;
}

#sidebar i {
  margin-right: 10px;
}

#sidebar a:not(:last-child) {
  margin-bottom: 100px;
}

/* User info display */
#user-info {
  display: none;
  color: #fff;
  margin-top: 5px;
  padding: 10px;
  background-color: #555;
  border-radius: 15px;
}

/* Content area styles */
#content {
  flex: 1;
  padding: 20px;
  margin-left: 220px;
  overflow-y: scroll;
}

/* Restock form styles */
#restock-form {
  width: 50%;
  margin: 0 auto;
  padding: 20px;
  border-radius: 15px;
  background-color: #fff;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

#restock-form label {
  display: block;
  margin-top: 10px;
}

#restock-form input,
#restock-form select {
  width: 100%;
  padding: 8px;
  margin: 5px 0 15px 0;
  display: inline-block;
  border: 1px solid #ccc;
  box-sizing: border-box;
}

#restock-form button {
  background-color: #4CAF50;
  color: white;
  padding: 10px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

#restock-form button:hover {
  background-color: #45a049;
}

/* View Orders card styles */
#view-orders-card {
  background-color: #3498db;
  color: #fff;
  padding: 20px;
  border-radius: 10px;
  margin-bottom: 20px;
  cursor: pointer;
}

#view-orders-card:hover {
  background-color: #2980b9;
}

/* View Orders Popup styles */
#view-orders-popup {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  justify-content: center;
  align-items: center;
}

.popup-content {
  width: 60%;
  padding: 20px;
  border-radius: 15px;
  background-color: #fff;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.close-popup {
  position: absolute;
  top: 10px;
  right: 10px;
  cursor: pointer;
}

/* Additional styles if needed... */


  </style>
</head>
<body>
  <?php
  // Include session handling or any other necessary PHP code
  session_start();
  if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
  }
  ?>

  <div id="dashboard">
    <div id="sidebar">
      <a href="#" id="user-icon" onclick="toggleUserInfo();"><i class="fas fa-user"></i> User</a>
      <div id="user-info">
        <?php
        if (isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['role'])) {
          echo "User ID: " . $_SESSION['user_id'] . "<br>Username: " . $_SESSION['username'] . "<br>Role: " . $_SESSION['role'];

          if ($_SESSION['role'] === 'owner' || $_SESSION['comp_staff'] == 1) {
            echo "<br>Store Name: " . $_SESSION['store_name'] . "<br>Location: " . $_SESSION['location_name'];
          }
        } else {
          echo "User information not available.";
        }
        ?>
      </div>
      <a href="fetch_notifications.php"><i class="fas fa-bell"></i> Notifications</a>
      <a href="mpesa-c2b.html"><i class="fas fa-coins"></i> Mpesa C2B</a>
      <a href="mpesa-b2b.html"><i class="fas fa-exchange-alt"></i> Mpesa B2B</a>
      <a href="home.php" onclick="redirectToPage('hom.php');"><i class="fas fa-home"></i> Dashboard</a>
      <a href="#" id="logoutLink" onclick="logout();"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
     <div id="content">
      <!-- Restock form -->
      <div id="restock-form">
        <h2>Create Restock Order</h2>
        <form action="submit_restock_order.php" method="post">
          <label for="quantity">Quantity:</label>
          <input type="text" id="quantity" name="quantity" required>

          <label for="satellite-location">Satellite Location:</label>
          <select id="satellite-location" name="satellite-location" required></select>

          <!-- Additional fields -->
          <label for="price">Price:</label>
          <input type="text" id="price" name="price" required>

          <label for="product-name">Product Name:</label>
          <input type="text" id="product-name" name="product-name" required>

          <label for="category">Category:</label>
          <input type="text" id="category" name="category" required>

          <button type="submit">Submit Order</button>
        </form>
      </div>

      <!-- View Orders card -->
      <div class="row">
        <div class="card" id="view-orders-card" onclick="openViewOrdersPopup();">
          <h3>View Restock Orders</h3>
          <p>See a list of all restock orders.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- View Orders Popup -->
  <div id="view-orders-popup">
    <div class="popup-content">
      <span class="close-popup" onclick="closeViewOrdersPopup()">&times;</span>
      <h2>Restock Orders</h2>
      <?php echo "Fetching restock orders functionality not implemented yet"; ?>
      <button onclick="downloadOrders()">Download Orders</button>
    </div>
  </div>

  <script>
    // Fetch satellite stores and populate the dropdown
    window.onload = function() {
      fetch('fetch_satellite_stores.php')
        .then(response => response.json())
        .then(data => {
          const select = document.getElementById('satellite-location');
          data.forEach(store => {
            const option = document.createElement('option');
            option.value = store.location_name;
            option.text = store.location_name;
            select.add(option);
          });
        })
        .catch(error => console.error('Error fetching satellite stores:', error));
    };

    function toggleUserInfo() {
      var userInfo = document.getElementById('user-info');
      userInfo.style.display = (userInfo.style.display === 'none') ? 'block' : 'none';
    }

    function openViewOrdersPopup() {
      document.getElementById('view-orders-popup').style.display = 'flex';
    }

    function closeViewOrdersPopup() {
      document.getElementById('view-orders-popup').style.display = 'none';
    }

    function downloadOrders() {
      // Implement download functionality here
      alert('Downloading orders...');
      // Replace the alert with actual download logic
    }

    // Other scripts...
  </script>
</body>
</html>

