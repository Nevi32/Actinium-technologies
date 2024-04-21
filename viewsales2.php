<?php
// Start the session
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Sales</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
     <!-- In the head section of your HTML files -->
    <link rel="stylesheet" type="text/css" href="main.css">

    <style>
     /* General styles */
body,
html {
    height: 100%;
    margin: 0;
    font-family: 'Arial', sans-serif;
}

/* Sidebar styles */
#dashboard {
    display: flex;
    height: 100vh;
}

 /* Paste the sidebar CSS here */
    body, html {
      height: 100%;
      margin: 0;
      font-family: 'Arial', sans-serif;
    }

    #sidebar {
      width: 180px;
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

    /* Media queries for responsiveness */
    @media screen and (max-width: 768px) {
      #sidebar {
        width: 120px; /* Reduce sidebar width on smaller screens */
      }

      #content {
        margin-left: 140px; /* Adjust main content margin to accommodate the narrower sidebar */
      }
    }
/* Adjust sidebar links margin */
#sidebar a:not(:last-child) {
    margin-bottom: 100px;
}

/* Content area styles */
#content {
    flex: 1;
    padding: 20px;
    margin-left: 200px; /* Adjust content area margin to accommodate the fixed sidebar */
}

/* Modal styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4);
    padding-top: 60px;
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

.modal-header {
    padding: 2px 16px;
    background-color: #5cb85c;
    color: white;
}

.modal-body {
    padding: 2px 16px;
}

.modal-footer {
    padding: 2px 16px;
    background-color: #5cb85c;
    color: white;
    text-align: center;
}

/* Download button styles */
.download-button {
    background-color: #008CBA;
    color: white;
    border: none;
    padding: 10px 20px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    margin-top: 10px;
    cursor: pointer;
}

/* Sales table styles */
#sales-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

#sales-table th,
#sales-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}

#sales-table th {
    background-color: #3498db;
    color: #fff;
}

#sales-table tbody tr:hover {
    background-color: #f5f5f5;
}

/* Satellite button styles */
.satellite-button {
    background-color: #4CAF50;
    color: white;
    border: none;
    padding: 10px 20px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    margin-top: 10px;
    cursor: pointer;
}

/* Hide satellite buttons initially */
.satellite-button-container {
    display: none;
}


    </style>
</head>

<body>
    <div id="dashboard">
        <div id="sidebar">
        <a href="#" id="user-icon" onclick="toggleUserInfo();"><i class="fas fa-user"></i> User</a>
            <div id="user-info">
                <!-- User info will be displayed here using JavaScript -->
            </div>
            <a href="notifications.html"><i class="fas fa-bell"></i> Notifications</a>
            <a href="mpesa-c2b.html"><i class="fas fa-coins"></i> Mpesa C2B</a>
            <a href="mpesa-b2b.html"><i class="fas fa-exchange-alt"></i> Mpesa B2B</a>
            <a href="home.php" onclick="redirectToPage('home.php');"><i class="fas fa-home"></i> Dashboard</a>
            <a href="#" id="logoutLink"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>


        <div id="content">
            <div class="alert" id="alert-message"></div>

            <!-- Add the search bar and store type selection -->
            <div id="search-bar">
                <!-- Store Type Selection -->
                <label for="store-type-select">Select Store Type:</label>
                <select id="store-type-select" onchange="changeStoreType()">
                    <option value="main_store">Main Store</option>
                    <option value="satellite">Satellite Store</option>
                </select>

                <!-- Search Bar -->
                <label for="product-search">Search Product:</label>
                <input type="text" id="product-search" placeholder="Enter product name">
                <button onclick="searchProduct()">Search</button>
            </div>

            <div id="satellite-buttons-container"></div>

            <div id="sales-table-container">
                <h1>Welcome to Sales Management</h1>
                <p>This is where your sales data will be displayed.</p>

                <table id="sales-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Quantity Sold</th>
                            <th>Total Price</th>
                            <th>Record Date</th>
                             <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="sales-table-body">
                        <!-- Display sales data using PHP -->
                        <?php
                        // Check if sales data is set in the session
                        $salesData = $_SESSION['mainstore_sales_data'] ?? $_SESSION['satellite_sales_data'] ?? null;

                        if ($salesData) {
                            foreach ($salesData as $sale) {
                                echo "<tr>";
                                echo "<td>{$sale['product_name']}</td>";
                                echo "<td>{$sale['quantity_sold']}</td>";
                                echo "<td>{$sale['total_price']}</td>";
                                echo "<td>{$sale['record_date']}</td>";
                                 echo "<td><button class='more-info-button' onclick='showDetailedEntries(" . $sale['sale_id'] . ")'>More Info</button></td>";
                                echo "</tr>";
                            }
                        } else {
                            // Display a message if no sales data is available
                            echo "<tr><td colspan='4'>No sales data available.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
                 <div id="detailed-entries-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Detailed info on Sale</h2>
        </div>
        <div class="modal-body" id="modal-body-content">
            <!-- Detailed sale data will be dynamically inserted here -->
        </div>
        <div class="modal-footer">
            <button onclick="downloadDetailedInfo(currentSaleId)" class="download-button">Download</button>
        </div>
    </div>
</div>

            <!-- Modify the JavaScript code inside your <script> tag at the end of the HTML body -->
            <script>
  var salesData = <?php echo json_encode($_SESSION['mainstore_sales_data'] ?? $_SESSION['satellite_sales_data'] ?? []); ?>;

              // Function to display sales data
function displaySalesData(salesData, searchTerm) {
    var tableBody = document.querySelector('#sales-table-body');
    tableBody.innerHTML = '';

    salesData.forEach(function (sale) {
        if (!searchTerm || sale.product_name.toLowerCase().includes(searchTerm.toLowerCase())) {
            var row = document.createElement('tr');
            row.innerHTML = `
                <td>${sale.product_name}</td>
                <td>${sale.quantity_sold}</td>
                <td>${sale.total_price}</td>
                <td>${sale.record_date}</td>
                <td><button class="more-info-button" onclick="showDetailedEntries(${sale.sale_id})">More Info</button></td>
            `;
            tableBody.appendChild(row);
        }
    });
}

// Function to search for a product
function searchProduct() {
    var searchInput = document.getElementById('product-search').value;
    displaySalesData(salesData, searchInput);
}

// Function to change the store type
function changeStoreType() {
    var storeTypeSelect = document.getElementById('store-type-select');
    var selectedStoreType = storeTypeSelect.value;

    if (selectedStoreType === 'main_store') {
        // Redirect to the main store page
        window.location.href = 'viewsales2.php?storeType=main_store';
    } else if (selectedStoreType === 'satellite') {
        // Display buttons with satellite locations
        displaySatelliteButtons();
    }
}

// Function to display buttons with satellite locations
function displaySatelliteButtons() {
    var satelliteSalesData = <?php echo json_encode($_SESSION['satellite_sales_data'] ?? []); ?>;
    var buttonsContainer = document.getElementById('satellite-buttons-container');

    // Clear existing buttons
    buttonsContainer.innerHTML = '';

    // Create buttons for each satellite store
    Object.keys(satelliteSalesData).forEach(function (locationName) {
        var button = document.createElement('button');
        button.textContent = locationName;
        button.addEventListener('click', function () {
            // Display sales data for the selected satellite store
            displaySalesData(satelliteSalesData[locationName]);
        });

        buttonsContainer.appendChild(button);
    });
}

// Call the initial function to display sales data
document.addEventListener('DOMContentLoaded', function () {
    var storeTypeSelect = document.getElementById('store-type-select');
    var selectedStoreType = storeTypeSelect.value;

    if (selectedStoreType === 'satellite') {
        // Display buttons with satellite locations if the selected store type is satellite
        displaySatelliteButtons();
    } else {
        // Fetch and display sales data for the main store if the selected store type is main store
        fetchMainSalesData();
    }
});

// Function to fetch and display sales data for the main store
function fetchMainSalesData() {
    // Fetch sales data for the main store from the session
    var mainSalesData = <?php echo json_encode($_SESSION['mainstore_sales_data'] ?? []); ?>;
    // Display the sales data for the main store
    displaySalesData(mainSalesData);
}
  function addMoreInfoButtons(salesData) {
            // Additional info buttons for each sale (if needed)
        }

         // Function to show detailed entries in the modal
function showDetailedEntries(saleId) {
    const detailedSale = salesData.find(function(sale) {
        return sale.sale_id === saleId;
    });

    var modalBody = document.querySelector('#modal-body-content');
    modalBody.innerHTML = `
        <p>Product Name: ${detailedSale.product_name}</p>
        <p>Category: ${detailedSale.category}</p>
        <p>Quantity Sold: ${detailedSale.quantity_sold}</p>
        <p>Quantity Description: ${detailedSale.quantity_description}</p>
        <p>Total Price: ${detailedSale.total_price}</p>
        <p>Sale Date: ${detailedSale.record_date}</p>
    `;

    // Set the current sale ID for download function
    currentSaleId = saleId;

    document.getElementById('detailed-entries-modal').style.display = 'block';
}


        function closeModal() {
            document.getElementById('detailed-entries-modal').style.display = 'none';
        }

        function showAlert(message) {
            var alertMessage = document.getElementById('alert-message');
            alertMessage.innerHTML = message;
            alertMessage.style.display = 'block';

            setTimeout(function () {
                alertMessage.style.display = 'none';
            }, 3000);
        }

        // Function to download detailed sale info
function downloadDetailedInfo(saleId) {
    const detailedSale = salesData.find(function(sale) {
        return sale.sale_id === saleId;
    });

    // Prepare the data to be downloaded
    const dataToDownload = `
        Product Name: ${detailedSale.product_name}
        Quantity Sold: ${detailedSale.quantity_sold}
        Total Price: ${detailedSale.total_price}
        Sale Date: ${detailedSale.record_date}
    `;

    // Create a Blob containing the data
    const blob = new Blob([dataToDownload], { type: 'text/plain' });

    // Create a download link and trigger click event
    const downloadLink = document.createElement('a');
    downloadLink.download = 'detailed_sale_info.txt';
    downloadLink.href = window.URL.createObjectURL(blob);
    downloadLink.click();

    // Cleanup
    window.URL.revokeObjectURL(downloadLink.href);
}

  document.getElementById('logoutLink').addEventListener('click', function (event) {
            // Prevent the default behavior of the link
            event.preventDefault();

            // Redirect the user to the logout.php file for logout
            window.location.href = 'logout.php';
        });


            </script>
        </div>
    </div>
</body>

</html>

