
<?php
session_start(); // Start the session

// Check if the user is logged in and has a store name in the session
if (!isset($_SESSION['store_name']) || !isset($_SESSION['username'])) {
    // If the store name or username is not set, return an error response
    echo json_encode(array('error' => 'User not logged in or store name/username not set in session'));
    exit();
}

// Get the store name and username from the session
$storeName = $_SESSION['store_name'];
$username = $_SESSION['username'];

// Define benchmark sales for different stores
$benchmarkSales = [
    'Nevistore' => 5000, // Example benchmark for Nevistore
    'ABC Store' => 7000,
    'XYZ Supermarket' => 10000
    // Add more stores and their benchmarks here
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Store Statistics Dashboard</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    /* Additional CSS styles */
    body {
      margin: 0;
      padding: 0;
      display: flex;
    }

    #sidebar {
      width: 200px;
      background-color: #333;
      color: #fff;
      padding: 20px;
    }

    #content {
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      background-color: lightgrey;
      padding: 20px;
      margin-left: 250px
    }

    .sidebar-navigation a {
      display: block;
      color: #fff;
      text-decoration: none;
      padding: 10px 0;
    }

    .sidebar-navigation a:hover {
      background-color: #555;
    }

    .card {
      border-radius: 15px;
      background-color: #fff;
      padding: 20px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <!-- Sidebar and navigation -->
  <div id="sidebar">
    <div class="sidebar-header">
      <h2>Store Statistics Dashboard</h2>
    </div>
    <nav class="sidebar-navigation">
      <a href="home.php">Dashboard</a>
      <a href="#" id="user-icon" onclick="toggleUserInfo();"><i class="fas fa-user"></i> User</a>
      <div id="user-info">
        <p>Welcome, <?php echo $username; ?></p> <!-- Display the username -->
      </div>
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
  </div>

  <!-- Main content -->
  <div id="content">
    <h1 id="welcome-message">Welcome to the <span id="store-name"><?php echo $storeName; ?></span> Statistics Dashboard 😊</h1>
    <div class="badge-card card">
      <h2>Badge Card</h2>
      <p id="badge-message">Loading...</p>
    </div>
    <div class="sales-card card">
      <h2>Sales Card</h2>
      <p id="sales-message">Loading...</p>
    </div>
    <div class="profit-card card">
      <h2>Profit Card</h2>
      <p id="profit-message">Loading...</p>
    </div>
  </div>

  <!-- jQuery library -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    $(document).ready(function() {
      // Fetch daily sales data when the page loads
      fetchDailySalesData();
    });

    function fetchDailySalesData() {
      $.ajax({
        url: 'fetchSalesStats.php',
        type: 'POST',
        data: { period: 'Daily' },
        dataType: 'json',
        success: function(response) {
          // Check if the response contains the necessary data
          if (response.success) {
            // Extract total sales from the response
            var totalSales = response.data.total_sales;

            // Update badge message based on comparison with benchmark sales
            var benchmark = getBenchmarkSales();
            if (benchmark !== undefined) {
              if (totalSales > benchmark) {
                $('#badge-message').html('Congratulations, <strong><?php echo $username; ?></strong>! Today\'s sales exceeded the benchmark! 🎉');
              } else {
                $('#badge-message').html('Sorry, <strong><?php echo $username; ?></strong>. Today\'s sales did not exceed the benchmark. 😔');
              }
            } else {
              console.error('Benchmark sales not defined for store: ' + getStoreName());
              $('#badge-message').text('Error: Benchmark sales not defined for this store.');
            }

            // Update sales message with Ksh currency
            $('#sales-message').html('Today\'s total sales: Ksh ' + totalSales.toLocaleString());

            // Fetch profit data
            fetchDailyProfitData(totalSales);
          } else {
            console.error('Error fetching daily sales data:', response.error);
            $('#badge-message').text('Error fetching daily sales data. Please try again later.');
          }
        },
        error: function(xhr, status, error) {
          console.error("Error fetching daily sales data:", error);
          $('#badge-message').text('Error fetching daily sales data. Please try again later.');
        }
      });
    }

    function fetchDailyProfitData(sales) {
  $.ajax({
    url: 'fetchProfitStats.php',
    type: 'POST',
    data: { period: 'Daily' }, // Pass period as Daily
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        var profit = response.data.profit;
        // Update profit message with Ksh currency
        $('#profit-message').html('Today\'s total profit: Ksh ' + profit.toLocaleString());
      } else {
        console.error('Error fetching daily profit data:', response.error);
        $('#profit-message').text('Error fetching daily profit data. Please try again later.');
      }
    },
    error: function(xhr, status, error) {
      console.error("Error fetching daily profit data:", error);
      $('#profit-message').text('Error fetching daily profit data. Please try again later.');
    }
  });
}

    // Function to get the store name
    function getStoreName() {
      return $('#store-name').text();
    }

    // Function to get the benchmark sales for the current store
    function getBenchmarkSales() {
      var storeName = getStoreName();
      return <?php echo isset($benchmarkSales[$storeName]) ? $benchmarkSales[$storeName] : 'undefined'; ?>;
    }
  </script>
</body>
</html>
