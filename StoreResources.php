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
      max-height: 70vh; /* Set maximum height for the content area */
      overflow-y: auto; /* Enable vertical scrolling when content overflows */
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

    /* Style for the "Record Suppliers" form */
    #supplierForm {
      max-width: 400px;
      margin: 0 auto;
    }

    #supplierForm label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }

    #supplierForm input[type="text"],
    #supplierForm input[type="email"] {
      width: 100%;
      padding: 8px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 5px;
      box-sizing: border-box;
    }

    #supplierForm button[type="submit"] {
      background-color: #4CAF50;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    #supplierForm button[type="submit"]:hover {
      background-color: #45a049;
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
      <div class="option" onclick="openPopup('suppliersPopup')">Manage Suppliers</div>
      <div class="option" onclick="openPopup('billsPopup')">Manage Bills</div>
      <div class="option" onclick="openPriceManagementPopup()">Price Management</div>

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
            <button class="action-button" onclick="resetCommission()">Reset Commissions</button>
          </div>
        </div>
      </div>

      <!-- Suppliers Popup -->
      <div class="popup" id="suppliersPopup">
        <span class="close" onclick="closePopup('suppliersPopup')">&times;</span>
        <div class="popup-content">
          <h2>Manage Suppliers</h2>
          <!-- Form for adding a new supplier -->
          <form id="supplierForm" style="display: block;">
            <label for="supplierName">Supplier Name:</label>
            <input type="text" id="supplierName" name="supplierName" required><br><br>
            <label for="phoneNumber">Phone Number:</label>
            <input type="text" id="phoneNumber" name="phoneNumber" required><br><br>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email"><br><br>
            <label for="address">Address:</label>
            <input type="text" id="address" name="address"><br><br>
            <button type="submit">Add Supplier</button>
          </form>

          <!-- List for displaying existing suppliers -->
          <div id="supplierList" style="display: none;">
            <table id="suppliersTable">
              <thead>
                <tr>
                  <th>Supplier Name</th>
                  <th>Phone Number</th>
                  <th>Email</th>
                  <th>Address</th>
                </tr>
              </thead>
              <tbody>
                <!-- Table rows will be dynamically populated via JavaScript -->
              </tbody>
            </table>
          </div>
          <br>
          <!-- Button to toggle between form and list view -->
          <button onclick="toggleSupplierView()">View Suppliers</button>
        </div>
      </div>

      <!-- Bills Popup -->
      <div class="popup" id="billsPopup">
        <span class="close" onclick="closePopup('billsPopup')">&times;</span>
        <div class="popup-content">
          <h2>Manage Bills</h2>
          <!-- Spreadsheet-like table for managing bills -->
          <table id="billsTable">
            <thead>
              <tr>
                <th>Bill Type</th>
                <th>Amount</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <!-- Table rows will be dynamically populated via JavaScript -->
            </tbody>
          </table>
          <br>
          <!-- Button to download bills as Excel file -->
          <button onclick="downloadBills()">Download Bills</button>
        </div>
      </div>

        <!-- Price Management Popup -->
<div class="popup" id="priceManagementPopup">
  <span class="close" onclick="closePopup('priceManagementPopup')">&times;</span>
  <div class="popup-content">
    <h2>Price Management</h2>
    <div class="product-list"> <!-- Added product list container -->
      <!-- Product list will be dynamically populated here -->
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
     function openPriceManagementPopup() {
  openPopup('priceManagementPopup');
  fetchInventory();
}

function openPriceManagementPopup() {
  openPopup('priceManagementPopup');
  fetchInventory();
}

function fetchInventory() {
  fetch('fetchproductfinace.php')
    .then(response => response.json())
    .then(data => {
      const productContainer = document.querySelector('.popup-content .product-list');
      if (!productContainer) {
        console.error('Product container not found');
        return;
      }
      productContainer.innerHTML = ''; // Clear existing content
      data.forEach(product => {
        const buyingPrice = parseFloat(product.unit_price).toFixed(2); // Use the unit price from the fetched data
        const productCard = `
          <div class="product-card">
            <h3>${product.product_name}</h3>
            <p><strong>Category:</strong> ${product.category}</p>
            <p><strong>Buying Price per unit:</strong> Ksh ${buyingPrice}</p>
            <label for="profit">Desired Profit (Ksh):</label>
            <input type="number" id="profit-${product.product_id}" min="0" step="0.01">
            <label for="dynamicPricing">Dynamic Pricing:</label>
            <input type="checkbox" id="dynamicPricing-${product.product_id}" onchange="toggleDynamicPricing(${product.product_id})">
            <label for="sellingPrice">Selling Price:</label>
            <input type="number" id="sellingPrice-${product.product_id}" disabled>
            <button onclick="calculateSellingPrice(${product.product_id}, ${buyingPrice})">Calculate Selling Price</button>
          </div>
        `;
        productContainer.innerHTML += productCard;
      });
    })
    .catch(error => {
      console.error('Error fetching inventory:', error);
    });
}


function calculateSellingPrice(productId, buyingPrice) {
  const profitInput = document.getElementById(`profit-${productId}`);
  const dynamicPricingCheckbox = document.getElementById(`dynamicPricing-${productId}`);
  const sellingPriceInput = document.getElementById(`sellingPrice-${productId}`);
  
  const profit = parseFloat(profitInput.value);
  
  if (isNaN(profit) || isNaN(buyingPrice)) {
    sellingPriceInput.value = '';
    return;
  }

  if (dynamicPricingCheckbox.checked) {
    // Calculate dynamic selling price
    const dynamicSellingPrice = buyingPrice + profit;
    sellingPriceInput.value = dynamicSellingPrice.toFixed(2);
  } else {
    // Set constant selling price
    sellingPriceInput.value = (buyingPrice + profit).toFixed(2);
  }
}

function toggleDynamicPricing(productId) {
  const dynamicPricingCheckbox = document.getElementById(`dynamicPricing-${productId}`);
  const sellingPriceInput = document.getElementById(`sellingPrice-${productId}`);
  
  if (dynamicPricingCheckbox.checked) {
    sellingPriceInput.disabled = true;
  } else {
    sellingPriceInput.disabled = false;
  }
}



// Other JavaScript functions for managing staff, suppliers, bills, etc. go here...


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

     // Function to toggle between form and list view
function toggleSupplierView() {
  var form = document.getElementById('supplierForm');
  var list = document.getElementById('supplierList');

  if (form.style.display === 'block') {
    form.style.display = 'none';
    list.style.display = 'block';
    fetchSuppliers();
  } else {
    form.style.display = 'block';
    list.style.display = 'none';
  }
}

// Function to fetch and display suppliers
function fetchSuppliers() {
  fetch('fetchsuppliersinfo.php')
    .then(response => response.json())
    .then(data => {
      const suppliersTable = document.getElementById('suppliersTable');
      suppliersTable.innerHTML = ''; // Clear existing rows
      data.forEach(supplier => {
        suppliersTable.innerHTML += `
          <tr>
            <td>${supplier.supplier_name}</td>
            <td>${supplier.phone_number}</td>
            <td>${supplier.email}</td>
            <td>${supplier.address}</td>
          </tr>
        `;
      });
    })
    .catch(error => {
      console.error('Error fetching suppliers:', error);
    });
}

// Function to add a new supplier
document.getElementById('supplierForm').addEventListener('submit', function(event) {
  event.preventDefault();

  var formData = new FormData(this);

  fetch('recordsuppliers.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    // Handle success or display error message
    if (data.success) {
      // Clear form fields
      this.reset();
      // If in list view, refresh the list
      if (document.getElementById('supplierList').style.display === 'block') {
        fetchSuppliers();
      }
    } else {
      alert(data.message);
    }
  })
  .catch(error => {
    console.error('Error recording supplier:', error);
  });
});

  </script>
</body>
</html>

