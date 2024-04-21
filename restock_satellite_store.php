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
      overflow-y: auto; /* Enable vertical scrolling */
      border-right: 1px solid #fff; /* Add border to indicate scrollable area */
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

    #content {
      margin-left: 220px; /* Adjusted to accommodate the sidebar */
    }
#sidebar a:not(:last-child) {
    margin-bottom: 100px; /* Increased margin for better separation */
}

#welcome-message {
    margin-bottom: 20px; /* Added margin for better separation */
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
  position: relative; /* Added to make the close button position relative to this container */
  width: 60%;
  max-width: 600px; /* Added to limit maximum width of popup content */
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
  font-size: 20px; /* Adjust font size for better visibility */
  color: #333; /* Adjust color for better visibility */
}
    #popup-message {
      margin-bottom: 15px;
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
        <form id="restockForm" onsubmit="event.preventDefault(); submitForm();">
          <label for="quantity">Quantity:</label>
          <input type="text" id="quantity" name="quantity" required>

          <label for="satellite-location">Satellite Location:</label>
          <select id="satellite-location" name="destination-location" required></select>

          <!-- Additional fields -->
          <label for="price">Price:</label>
          <input type="text" id="price" name="price" required>

          <label for="product-name">Product Name:</label>
          <select id="product-name" name="product-name" required></select>

          <label for="category">Category:</label>
          <select id="category" name="category" required></select>

          <!-- Hidden input fields for store_name and location_name -->
          <input type="hidden" id="store-name" name="store-name" value="<?php echo $_SESSION['store_name']; ?>">
          <input type="hidden" id="location-name" name="location-name" value="<?php echo $_SESSION['location_name']; ?>">

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
      <span class="close-popup" id="close-popup">&times;</span>
      <h2>Restock Orders</h2>
      <div id="popup-message"></div>
      <div id="orders-list" style="max-height: 300px; overflow-y: auto;"></div>
      <button onclick="downloadOrders()">Download Orders</button>
    </div>
  </div>
</div>
  <script>
    // Fetch satellite stores and populate the dropdown
    window.onload = function () {
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

      // Fetch products and categories and populate select fields
      fetch('fetchproducts.php')
        .then(response => response.json())
        .then(data => {
          const productSelect = document.getElementById('product-name');
          const categorySelect = document.getElementById('category');

          // Populate product select field
          data.products.forEach(product => {
            const option = document.createElement('option');
            option.value = product;
            option.text = product;
            productSelect.appendChild(option);
          });

          // Populate category select field
          data.categories.forEach(category => {
            const option = document.createElement('option');
            option.value = category;
            option.text = category;
            categorySelect.appendChild(option);
          });
        })
        .catch(error => console.error('Error fetching products:', error));
    };

    function toggleUserInfo() {
      var userInfo = document.getElementById('user-info');
      userInfo.style.display = (userInfo.style.display === 'none') ? 'block' : 'none';
    }

    function openViewOrdersPopup() {
      document.getElementById('view-orders-popup').style.display = 'flex';
    }

      // Function to close the view orders popup
  document.getElementById('close-popup').addEventListener('click', function() {
    document.getElementById('view-orders-popup').style.display = 'none';
  });

    function submitForm() {
  // Prevent default form submission
  event.preventDefault();

  // Get form data
  const form = document.getElementById('restockForm');
  const formData = new FormData(form);

  // Append store_name and location_name to the formData
  formData.append('store_name', document.getElementById('store-name').value);
  formData.append('location_name', document.getElementById('location-name').value);

  // Send form data using fetch
  fetch('recordRestock.php', {
    method: 'POST',
    body: formData
  })
    .then(response => response.json())
    .then(data => {
      // Display the response message in a separate pop-up
      alert(data.message);
    })
    .catch(error => console.error('Error submitting form:', error));
}


  // Function to fetch and display restock orders
  function fetchRestockOrders() {
    fetch('fetchRestockOrders.php')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Populate orders list
          const ordersList = document.getElementById('orders-list');
          ordersList.innerHTML = ''; // Clear previous content
          data.orders.forEach(order => {
            const orderInfo = document.createElement('div');
            orderInfo.innerHTML = `<strong>Product Name:</strong> ${order.product_name}<br>
                                   <strong>Category:</strong> ${order.category}<br>
                                   <strong>Price:</strong> ${order.price}<br>
                                   <strong>Date:</strong> ${order.order_date}<br>
                                   <strong>Satellite Location:</strong> ${order.satellite_location}<br><br>`;
            ordersList.appendChild(orderInfo);
          });
        } else {
          // Display error message if fetching orders fails
          document.getElementById('popup-message').innerText = data.message;
        }
      })
      .catch(error => console.error('Error fetching restock orders:', error));
  }

  // Function to open the view orders popup and fetch orders
  function openViewOrdersPopup() {
    document.getElementById('view-orders-popup').style.display = 'flex';
    fetchRestockOrders(); // Fetch restock orders when popup is opened
  }

   // Close the view orders popup when clicking on the "x" icon
    document.getElementById('close-popup').addEventListener('click', function() {
        document.getElementById('view-orders-popup').style.display = 'none';
    });

    function downloadOrders() {
  fetch('fetchRestockOrders.php')
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        let formattedOrders = ''; // Variable to store formatted orders
        // Format each order
        data.orders.forEach(order => {
          formattedOrders += `Product Name: ${order.product_name}\n` +
                             `Category: ${order.category}\n` +
                             `Price: ${order.price}\n` +
                             `Date: ${order.order_date}\n` +
                             `Satellite Location: ${order.satellite_location}\n\n`;
        });
        // Create a Blob object containing the formatted orders
        const blob = new Blob([formattedOrders], { type: 'text/plain' });
        // Create a temporary URL for the Blob object
        const url = window.URL.createObjectURL(blob);
        // Create a link element to trigger the download
        const link = document.createElement('a');
        link.href = url;
        link.download = 'restock_orders.txt';
        // Simulate a click on the link to start the download
        document.body.appendChild(link);
        link.click();
        // Clean up by revoking the URL
        window.URL.revokeObjectURL(url);
      } else {
        console.error('Error downloading orders:', data.message);
      }
    })
    .catch(error => console.error('Error downloading orders:', error));
}


    function openResponsePopup() {
      // Display a separate pop-up for success or error messages
      // You can customize this part based on your design or use a library like SweetAlert
      const popupMessage = document.getElementById('popup-message').innerHTML;
      alert('Displaying response pop-up: ' + popupMessage);
    }

 document.getElementById('logoutLink').addEventListener('click', function(event) {
      // Prevent the default behavior of the link
      event.preventDefault();

      // Redirect the user to the logout.php file for logout
      window.location.href = 'logout.php';
    });

  </script>
</body>
</html>

