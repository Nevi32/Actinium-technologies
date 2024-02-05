<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sale Management Dashboard</title>
  <!-- Link to salestyle.css -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
  <link rel="stylesheet" href="salestyle.css">
</head>
<body>
  <div id="sales-page">
    <div id="sidebar">
      <!-- Sidebar content -->
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
      <!-- Navigation bar -->
      <div class="navbar">
        <a href="#" onclick="viewSales()">View Sales</a> <!-- Click here to view sales -->
      </div>

      <!-- Modified Input Form Card to allow multiple sales entries -->
      <div id="sales-form" class="section-card">
        <h3>Record Sales</h3>
        <form action="process_sale.php" method="POST" id="salesForm">
          <div id="salesEntries">
            <div class="entry-card">
              <label for="product_name_1">Product Name:</label>
              <input type="text" id="product_name_1" name="product_name[]" required>
              
              <label for="category_1">Category:</label>
              <input type="text" id="category_1" name="category[]" required>
              
              <label for="staff_1">Staff:</label>
              <select id="staff_1" name="staff[]" required>
                <!-- Staff names will be populated dynamically using JavaScript -->
              </select>
              
              <label for="quantity_sold_1">Quantity Sold:</label>
              <input type="number" id="quantity_sold_1" name="quantity_sold[]" required>
              
              <label for="total_price_1">Total Price:</label>
              <input type="number" id="total_price_1" name="total_price[]" required>
            </div>
          </div>
          <button type="button" onclick="addSalesEntry()">Add Another Entry</button>
          <button type="submit">Record Sales</button>
        </form>
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
          var staffDropdowns = document.querySelectorAll('select[name="staff[]"]');
          staffDropdowns.forEach(function(dropdown) {
            staffNames.forEach(function(name) {
              var option = document.createElement('option');
              option.value = name;
              option.textContent = name;
              dropdown.appendChild(option);
            });
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

    // Function to add new sales entry dynamically
    let entryCount = 1;

    function addSalesEntry() {
      entryCount++;
      const salesEntries = document.getElementById('salesEntries');

      const entryCard = document.createElement('div');
      entryCard.classList.add('entry-card');

      entryCard.innerHTML = `
        <label for="product_name_${entryCount}">Product Name:</label>
        <input type="text" id="product_name_${entryCount}" name="product_name[]" required>
        
        <label for="category_${entryCount}">Category:</label>
        <input type="text" id="category_${entryCount}" name="category[]" required>
        
        <label for="staff_${entryCount}">Staff:</label>
        <select id="staff_${entryCount}" name="staff[]" required>
          <!-- Staff names will be populated dynamically using JavaScript -->
        </select>
        
        <label for="quantity_sold_${entryCount}">Quantity Sold:</label>
        <input type="number" id="quantity_sold_${entryCount}" name="quantity_sold[]" required>
        
        <label for="total_price_${entryCount}">Total Price:</label>
        <input type="number" id="total_price_${entryCount}" name="total_price[]" required>
      `;

      salesEntries.appendChild(entryCard);

      // Fetch staff names for the newly added entry
      fetchStaffNames();
    }

    // Call fetchStaffNames and handleResponseMessages functions on page load
    document.addEventListener('DOMContentLoaded', function() {
      fetchStaffNames();
      handleResponseMessages();
    });
  </script>
</body>
</html>

