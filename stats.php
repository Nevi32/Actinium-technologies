<?php
session_start(); // Start the session

// Include the database configuration
require_once 'config.php';

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
    'Nevishoes' => 12000, // Example benchmark for Nevistore
    'Tevstore' => 7000,
    'Lucyhair' => 10000
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

    #content {
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      background-color: lightgrey;
      padding: 20px;
      margin-left: 250px;
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
      <a href="stats.html">Stat reports</a>
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
      <select id="sales-period-select" onchange="fetchSalesData(this.value);">
        <option value="Daily">Daily</option>
        <option value="Weekly">Weekly</option>
        <option value="Monthly">Monthly</option>
      </select>
      <p id="sales-message">Loading...</p>
    </div>
    <div class="profit-card card">
      <h2>Profit Card</h2>
      <select id="profit-period-select" onchange="fetchProfitData(this.value);">
        <option value="Daily">Daily</option>
        <option value="Weekly">Weekly</option>
        <option value="Monthly">Monthly</option>
      </select>
      <p id="profit-message">Loading...</p>
    </div>
    <div class="expenses-profit-card card">
      <h2>Expenses and True Profit</h2>
      <select id="period-select" onchange="fetchExpensesAndTrueProfitData(this.value);">
        <option value="Daily">Daily</option>
        <option value="Weekly">Weekly</option>
        <option value="Monthly">Monthly</option>
      </select>
      <p id="expenses-message">Loading...</p>
      <p id="true-profit-message"></p>
    </div>
    <div class="product-performance-card card">
      <h2>Product Performance</h2>
      <select id="product-performance-select" onchange="fetchProductPerformanceData(this.value, $('#product-period-select').val());">
        <option value="price">Performance by Prices</option>
        <option value="quantity">Performance by Quantity</option>
      </select>
      <select id="product-period-select" onchange="fetchProductPerformanceData($('#product-performance-select').val(), this.value);">
        <option value="Daily">Daily</option>
        <option value="Weekly">Weekly</option>
        <option value="Monthly">Monthly</option>
      </select>
      <p id="product-performance-message">Loading...</p>
    </div>
  </div>

  <!-- jQuery library -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    $(document).ready(function() {
      // Fetch initial data when the page loads
      fetchInitialData();
    });

    // Function to fetch all data when the page loads
    function fetchInitialData() {
      fetchBadgeData();
      fetchSalesData('Daily');
      fetchProfitData('Daily');
      fetchExpensesAndTrueProfitData('Daily');
      fetchProductPerformanceData('price', 'Daily');
    }

    // Function to fetch badge data
    function fetchBadgeData() {
      var benchmark = <?php echo isset($benchmarkSales[$storeName]) ? $benchmarkSales[$storeName] : 'undefined'; ?>;
      var totalSales = <?php echo isset($totalSalesToday) ? $totalSalesToday : 0; ?>; // Assume you have this data available
      if (totalSales > benchmark) {
        $('#badge-message').html('Congratulations, <strong><?php echo $username; ?></strong>! Today\'s sales exceeded the benchmark! 🎉');
      } else {
        $('#badge-message').html('Sorry, <strong><?php echo $username; ?></strong>. Today\'s sales did not exceed the benchmark. 😔');
      }
    }

    // Function to fetch sales data
    function fetchSalesData(period) {
      $.ajax({
        url: 'fetchSalesStats.php',
        type: 'POST',
        data: { period: period },
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            $('#sales-message').html('Today\'s total sales: Ksh ' + response.data.total_sales.toLocaleString());
          } else {
            console.error('Error fetching sales data:', response.error);
            $('#sales-message').text('Error fetching sales data. Please try again later.');
          }
        },
        error: function(xhr, status, error) {
          console.error("Error fetching sales data:", error);
          $('#sales-message').text('Error fetching sales data. Please try again later.');
        }
      });
    }

    // Function to fetch profit data
    function fetchProfitData(period) {
      $.ajax({
        url: 'fetchProfitStats.php',
        type: 'POST',
        data: { period: period },
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            $('#profit-message').html('Today\'s total profit: Ksh ' + response.data.profit.toLocaleString());
          } else {
            console.error('Error fetching profit data:', response.error);
            $('#profit-message').text('Error fetching profit data. Please try again later.');
          }
        },
        error: function(xhr, status, error) {
          console.error("Error fetching profit data:", error);
          $('#profit-message').text('Error fetching profit data. Please try again later.');
        }
      });
    }
     // Function to fetch expenses and true profit data
function fetchExpensesAndTrueProfitData(period) {
  $.ajax({
    url: 'fetchTrueProfitStats.php',
    type: 'POST',
    data: { period: period },
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        var expenses = response.data.total_expenses;
        var trueProfit = response.data.true_profit;

        // Update expenses message
        var expensesMessage = 'Total Expenses: ';
        for (var expenseType in expenses) {
          expensesMessage += expenseType + ': Ksh ' + expenses[expenseType].toLocaleString() + ', ';
        }
        $('#expenses-message').html(expensesMessage);

        // Update true profit message
        $('#true-profit-message').html('True Profit: Ksh ' + trueProfit.toLocaleString());
      } else {
        console.error('Error fetching expenses and true profit data:', response.error);
        $('#expenses-message').text('Error fetching data. Please try again later.');
      }
    },
    error: function(xhr, status, error) {
      console.error("Error fetching expenses and true profit data:", error);
      $('#expenses-message').text('Error fetching data. Please try again later.');
    }
  });
}
   // Function to fetch and display product performance data
    function fetchProductPerformanceData(sortType, period) {
      $.ajax({
        url: 'fetchSalesStats.php',
        type: 'POST',
        data: { period: period },
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            // Extract product sales data from the response
            var productSales = response.data.product_sales;

            // Sort product sales data based on the selected sort type
            if (sortType === 'price') {
              productSales.sort(function(a, b) {
                return b.total_price - a.total_price; // Sort by total price (descending)
              });
            } else if (sortType === 'quantity') {
              productSales.sort(function(a, b) {
                return b.total_quantity - a.total_quantity; // Sort by total quantity (descending)
              });
            }
            // Construct HTML for displaying product performance
            var html = '<ul>';
            productSales.forEach(function(product) {
              html += '<li>' + product.product_name + ' (' + product.category + ') - ' + (sortType === 'price' ? product.total_price : product.total_quantity).toLocaleString() + ' (' + product.location_name + ')</li>';
            });
              html += '</ul>';

            // Display product performance data
            $('#product-performance-message').html(html);
          } else {
            console.error('Error fetching product performance data:', response.error);
            $('#product-performance-message').text('Error fetching product performance data. Please try again later.');
          }
        },
        error: function(xhr, status, error) {
          console.error("Error fetching product performance data:", error);
          $('#product-performance-message').text('Error fetching product performance data. Please try again later.');
        }
      });
    }
  </script>
</body>
</html>

