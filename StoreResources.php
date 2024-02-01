<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Store Resource Management</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
  <style>
    /* Common styles */
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

    #sidebar a:not(:last-child) {
      margin-bottom: 100px;
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
      padding: 20px;
      margin-left: 220px;
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
    }

    /* Welcome message */
    .welcome-message {
      margin-bottom: 25px;
      font-size: 1.8em;
      font-weight: bold;
      color: #333;
    }

    /* Options in content section */
    .option {
      width: calc(50% - 20px);
      height: 150px;
      margin-bottom: 20px;
      padding: 20px;
      border-radius: 15px;
      background-color: #3498db;
      cursor: pointer;
      transition: background-color 0.3s ease-in-out;
      box-sizing: border-box;
      color: #fff;
    }

    .option:hover {
      background-color: #2980b9;
    }

    /* Popup */
    .popup {
      display: none;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background-color: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      z-index: 1000;
    }

    .popup-content {
      text-align: center;
    }

    .close {
      position: absolute;
      top: 10px;
      right: 10px;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <?php
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
        echo "User ID: " . $_SESSION['user_id'] . " <br> Username: " . $_SESSION['username'] . " <br> Role: " . $_SESSION['role'];

        if ($_SESSION['role'] === 'owner' || $_SESSION['comp_staff'] == 1) {
          echo "<br> Store Name: " . $_SESSION['store_name'] . " <br> Location: " . $_SESSION['location_name'];
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
      <div class="welcome-message">
        Welcome to the Store Resource Management. Click on the resource you wish to manage.
      </div>

      <!-- Options for managing resources -->
      <div class="option" onclick="openPopup('staff')">Manage Staff</div>
      <div class="option" onclick="openPopup('suppliers')">Manage Suppliers</div>
      <div class="option" onclick="openPopup('bills')">Manage Bills</div>

      <!-- Popups for managing resources -->
      <div class="popup" id="staffPopup">
        <span class="close" onclick="closePopup('staffPopup')">&times;</span>
        <div class="popup-content">
          <!-- Content for managing staff -->
          <h2>Manage Staff</h2>
          <p>What staff management action would you like to perform?</p>
          <ul>
            <li><a href="#">Calculate Staff Commission</a></li>
            <li><a href="#">Add Staff</a></li>
            <li><a href="#">Remove Staff</a></li>
          </ul>
        </div>
      </div>

      <div class="popup" id="suppliersPopup">
        <span class="close" onclick="closePopup('suppliersPopup')">&times;</span>
        <div class="popup-content">
          <!-- Content for managing suppliers -->
          <h2>Manage Suppliers</h2>
          <p>What supplier management action would you like to perform?</p>
          <ul>
            <li><a href="#">Add Supplier</a></li>
            <li><a href="#">Remove Supplier</a></li>
          </ul>
        </div>
      </div>

      <div class="popup" id="billsPopup">
        <span class="close" onclick="closePopup('billsPopup')">&times;</span>
        <div class="popup-content">
          <!-- Content for managing bills -->
          <h2>Manage Bills</h2>
          <p>What bill management action would you like to perform?</p>
          <ul>
            <li><a href="#">Record Transport</a></li>
            <li><a href="#">Record Electric</a></li>
            <li><a href="#">Record Rent</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <script>
    // JavaScript functions for opening and closing popups
    function openPopup(option) {
      var popup = document.getElementById(option + 'Popup');
      if (popup) {
        popup.style.display = 'block';
      }
    }

    function closePopup(popupId) {
      var popup = document.getElementById(popupId);
      if (popup) {
        popup.style.display = 'none';
      }
    }

    // JavaScript function to toggle user info display
    function toggleUserInfo() {
      var userInfo = document.getElementById('user-info');
      userInfo.style.display = (userInfo.style.display === 'none') ? 'block' : 'none';
    }
  </script>
</body>
</html>

