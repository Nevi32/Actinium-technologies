<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Store Management Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
   <!-- In the head section of your HTML files -->
  <link rel="stylesheet" type="text/css" href="main.css">

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
  flex: 1;
  padding: 20px;
  margin-left: 220px;
  overflow-y: scroll;
}

.welcome-message {
  margin-bottom: 25px;
  font-size: 2em; /* Increase font size for a bolder look */
  font-weight: bold; /* Make the text bold */
  color: #333; /* Adjust the color to your preference */
}

.row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;
}

.card, .stock-satellite-stores {
  width: calc(50% - 20px);
  height: 200px;
  margin: 10px;
  padding: 20px;
  border-radius: 15px;
  text-align: center;
  cursor: pointer;
  transition: background-color 0.3s ease-in-out;
}

.card:hover, .stock-satellite-stores:hover {
  background-color: #16a085;
  color: #fff;
}

.inventory {
  background-color: #3498db;
  color: #fff;
}

.sales {
  background-color: #2ecc71;
  color: #fff;
}

.StoreResources {
  background-color: #e74c3c;
  color: #fff;
}

.stats {
  background-color: #f39c12;
  color: #fff;
}

.stock-satellite-stores {
  width: calc(100% - 20px);
  height: 300px;
  margin: 10px;
  padding: 20px;
  border-radius: 15px;
  text-align: center;
  cursor: pointer;
  transition: background-color 0.3s ease-in-out;
  background-color: #16a085; /* Adjusted background color */
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

.card h3, .stock-satellite-stores h3 {
  margin: 0;
  font-size: 1.5em;
}

.card p, .stock-satellite-stores p {
  margin: 10px 0;
  font-size: 1em;
}

#logoutLink {
  cursor: pointer;
}

/* Media queries for responsiveness */
@media screen and (max-width: 768px) {
  .row {
    flex-direction: column; /* Stack cards vertically on smaller screens */
  }

  .card, .stock-satellite-stores {
    width: 100%; /* Make cards take full width on smaller screens */
    height: auto; /* Let height adjust automatically */
    margin: 10px 0; /* Adjust margin for spacing */
  }
}
/* Media queries for responsiveness */
/* Media queries for responsiveness */
@media screen and (max-width: 768px) {
  #sidebar {
    width: 120px; /* Reduce sidebar width on smaller screens */
  }

  #content {
    margin-left: 140px; /* Adjust main content margin to accommodate the narrower sidebar */
  }
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
      <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
      <a href="mpesa.php"><i class="fas fa-coins"></i> Mpesa C2B</a>
      <a href="mpesa.php"><i class="fas fa-exchange-alt"></i> Mpesa B2B</a>
      <a href="home.php" onclick="redirectToPage('hom.php');"><i class="fas fa-home"></i> Dashboard</a>
      <a href="memo.php"><i class="fas fa-bell"></i> Memos</a>
      <a href="#" id="logoutLink" onclick="logout();"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div id="content">
      <!-- Move the welcome message here -->
      <div class="welcome-message" id="welcome-message">
        <?php
        echo "Welcome to the " . $_SESSION['store_name'] . " System";
        ?>
      </div>

      <!-- Satellite Stores card -->
      <div class="row">
        <div class="card stock-satellite-stores" onclick="redirectToPage('restock_satellite_store.php');">
          <h3>Satellite Stores</h3>
          <p>Manage stock and data for satellite stores.</p>
        </div>
      </div>

      <!-- Other cards below the Satellite Stores card -->
      <div class="row">
        <div class="card inventory" onclick="redirectToPage('selectinventorypage.php');">
          <h3>Inventory</h3>
          <p>You can record and view your inventory here.</p>
        </div>

        <div class="card sales" onclick="redirectToPage('sale.php');">
          <h3>Sales</h3>
          <p>Track and analyze your sales data.</p>
        </div>
      </div>
      <div class="row">
        <div class="card StoreResources" onclick="redirectToPage('StoreResources.php');">
          <h3>StoreResources</h3>
          <p>Manage and process your store resources like staff suppliers and bills efficiently.</p>
        </div>
        <div class="card stats" onclick="redirectToPage('stats.php');">
          <h3>Stats</h3>
          <p>Explore detailed statistics and reports.</p>
        </div>
      </div>

      <!-- ... (existing script content) ... -->
    </div>
  </div>
  <script>
  function toggleUserInfo() {
      var userInfo = document.getElementById('user-info');
      userInfo.style.display = (userInfo.style.display === 'none') ? 'block' : 'none';
    }

      function redirectToPage(page) {
    // Redirect to the specified page without passing user info in the URL
    window.location.href = page;
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
