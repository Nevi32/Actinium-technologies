<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inventory Management Dashboard</title>
  <!-- Link to font-awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
  <link rel="stylesheet" href="inventorystyle.css">
</head>
<body>
  <div id="inventory-page">
    <div id="sidebar">
      <!-- Sidebar content -->
      <a href="#" id="user-icon"><i class="fas fa-user"></i> User</a>
      <div id="user-info">
        <?php
        session_start();
        if (isset($_SESSION['user_id'])) {
          echo "User ID: " . $_SESSION['user_id'] . "<br>Username: " . $_SESSION['username'] . "<br>Role: " . $_SESSION['role'];
        }
        ?>
      </div>
      <a href="notifications.html"><i class="fas fa-bell"></i> Notifications</a>
      <a href="add_product.html"><i class="fas fa-plus"></i> Add Product</a>
      <a href="home.php"><i class="fas fa-home"></i> Dashboard</a>
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div id="content">
      <!-- Navigation bar -->
      <div class="navbar">
        <a href="#" onclick="viewInventory()">View Inventory</a> <!-- View Inventory button -->
      </div>

      <!-- Modified Input Form Card to allow multiple inventory entries -->
      <div id="inventory-form" class="section-card">
        <h3>Record Inventory</h3>
        <form id="inventoryForm">
          <div id="inventoryEntries">
            <div class="entry-card">
              <label for="product_name_1">Product Name:</label>
              <input type="text" id="product_name_1" name="product_name[]" required>

              <label for="category_1">Category:</label>
              <input type="text" id="category_1" name="category[]" required>

              <label for="quantity_1">Quantity:</label>
              <input type="number" id="quantity_1" name="quantity[]" required>
               
              <label for="quantity_description_1">Quantity Description:</label>
              <input type="text" id="quantity_description_1" name="quantity_description[]">

              <label for="price_1">Price:</label>
              <input type="number" id="price_1" name="price[]" required>

              <label for="image_1">Image:</label>
              <input type="file" id="image_1" name="image[]">
            </div>
          </div>
          <button type="button" onclick="addInventoryEntry()">Add Another Entry</button>
          <button type="button" onclick="recordInventory()">Record Inventory</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Include necessary JavaScript -->
  <script>
    // Function to redirect to view inventory page
    function viewInventory() {
        // Pass store name and location to fetchinventory.php
        var storeName = "<?php echo $_SESSION['store_name']; ?>";
        var locationName = "<?php echo $_SESSION['location_name']; ?>";
        window.location.href = 'fetchinventory.php?storeName=' + storeName + '&locationName=' + locationName;
    }

    // Function to add new inventory entry dynamically
    let entryCount = 1;

    function addInventoryEntry() {
      entryCount++;
      const inventoryEntries = document.getElementById('inventoryEntries');

      const entryCard = document.createElement('div');
      entryCard.classList.add('entry-card');

      entryCard.innerHTML = `
        <label for="product_name_${entryCount}">Product Name:</label>
        <input type="text" id="product_name_${entryCount}" name="product_name[]" required>

        <label for="category_${entryCount}">Category:</label>
        <input type="text" id="category_${entryCount}" name="category[]" required>

        <label for="quantity_${entryCount}">Quantity:</label>
        <input type="number" id="quantity_${entryCount}" name="quantity[]" required>
     
        <label for="quantity_description_${entryCount}">Quantity Description:</label>
        <input type="text" id="quantity_description_${entryCount}" name="quantity_description[]">


        <label for="price_${entryCount}">Price:</label>
        <input type="number" id="price_${entryCount}" name="price[]" required>

        <label for="image_${entryCount}">Image:</label>
        <input type="file" id="image_${entryCount}" name="image[]">
      `;

      inventoryEntries.appendChild(entryCard);
    }

    // Function to record inventory
    function recordInventory() {
      // AJAX request to record inventory.php
      var formData = new FormData(document.getElementById('inventoryForm'));

      // AJAX request
      var xhr = new XMLHttpRequest();
      xhr.open('POST', 'recordinventory.php', true);
      xhr.onload = function () {
        if (xhr.status === 200) {
          showAlert(xhr.responseText, 'success'); // Pass response text to showAlert function
        } else {
          showAlert('Failed to record inventory. Please try again.', 'error');
        }
      };
      xhr.send(formData);
    }

    // Function to show alert messages
    function showAlert(message, type) {
      alert(message); // Display alert message
      // Implementation can be extended to show styled pop-up messages
    }
  </script>
</body>
</html>

