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
      border-radius: 20px; /* Increased border-radius for rounded edges */
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      z-index: 1000;
      width: 70%; /* Increased width */
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

    /* Style for the table inside the popup */
    table {
      border-collapse: collapse;
      width: 100%;
    }

    th, td {
      padding: 8px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    th {
      background-color: #f2f2f2;
    }

    /* Style for action buttons */
    .action-button {
      background-color: blue;
      color: white;
      margin-right: 10px;
      cursor: pointer;
    }

    /* Additional margin for buttons */
    .button-margin {
      margin-top: 20px;
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
      <div class="option" onclick="openStaffPopup()">Manage Staff</div>
      <div class="option" onclick="openPopup('suppliers')">Manage Suppliers</div>
      <div class="option" onclick="openPopup('bills')">Manage Bills</div>
       <!-- Staff Popup -->
<div class="popup" id="staffPopup">
  <span class="close" onclick="closePopup('staffPopup')">&times;</span>
  <div class="popup-content" style="width: 100%;">
    <!-- Content for managing staff -->
    <h2 style="margin-bottom: 20px;">Manage Staff</h2>
    <table style="width: 100%;" id="staffTable">
      <tr>
        <th>Staff Name</th>
        <th>Location</th>
        <th>Commission Accumulated</th>
        <th>Action</th>
      </tr>
    </table>
    <div class="button-margin">
      <button class="action-button" onclick="redirectToRegisterPage()">Add Staff</button>
      <button class="action-button" onclick="resetCommissions()">Reset Commissions</button>
    </div>
  </div>
</div>


      <!-- Other Popups -->
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
    // JavaScript function for opening and closing popups
      
  function openPopup(popupId) {
    var popup = document.getElementById(popupId);
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

  function toggleUserInfo() {
    var userInfo = document.getElementById('user-info');
    userInfo.style.display = (userInfo.style.display === 'none') ? 'block' : 'none';
  }

  function openStaffPopup() {
    openPopup('staffPopup');
    fetchStaffInfo();
  }

  function fetchStaffInfo() {
    fetch('fetchstaff.php')
      .then(response => response.json())
      .then(data => {
        const staffTable = document.getElementById('staffTable');
        staffTable.innerHTML = ''; // Clear existing rows
        data.forEach(staff => {
        
       staffTable.innerHTML += `
            <tr>
              <th>Staff Name</th>
              <th>Location</th>
              <th>Commission Accumulated</th>
              <th>Action</th>
            </tr>
          `;
           
        staffTable.innerHTML += `
            <tr>
              <td>${staff.name}</td>
              <td>${staff.location}</td>
              <td>${staff.commission}</td>
              <td>
                <button style="background-color: red; color: white;" onclick="removeStaff(${staff.user_id})">Remove Staff</button>
                <button style="background-color: blue; color: white;" onclick="calculateCommission(${staff.user_id})">Calculate Commission</button>
              </td>
            </tr>
          `;
        });
      })
      .catch(error => {
        console.error('Error fetching staff info:', error);
      });
  }

   function removeStaff(userId) {
  fetch('removestaff.php?id=' + userId)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        fetchStaffInfo(); // Refresh staff list after removal
      } else {
        alert(data.message);
      }
    })
    .catch(error => {
      console.error('Error removing staff:', error);
    });
}

function redirectToRegisterPage() {
  window.location.href = 'registerX.php';
}


function calculateCommission(userId) {
    fetch('calculate_commission.php?id=' + userId)
      .then(response => response.json())
      .then(data => {
        fetchStaffInfo(); // Refresh staff list after commission calculation
      })
      .catch(error => {
        console.error('Error calculating commission:', error);
      });
}

function resetCommission(userId) {
    fetch('resetcommission.php?id=' + userId)
      .then(response => response.json())
      .then(data => {
        fetchStaffInfo(); // Refresh staff list after commission reset
      })
      .catch(error => {
        console.error('Error resetting commission:', error);
      });
}


  </script>
</body>
</html>

