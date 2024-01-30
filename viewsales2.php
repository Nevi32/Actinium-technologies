<?php
include 'config.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

// Retrieve main store data from session
$mainStoreSalesData = $_SESSION['main_store_data'] ?? [];
$satelliteStoreData = $_SESSION['satellite_store_data'] ?? [];
$satelliteLocations = $_SESSION['satellite_store_locations'] ?? [];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Sales</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">

    <style>
        /* CSS styles go here */
        body,
        html {
            height: 100%;
            margin: 0;
            font-family: 'Arial', sans-serif;
        }

        #dashboard {
            display: flex;
            height: 100vh;
        }

        #sidebar {
            width: 150px;
            background-color: #333;
            color: #fff;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: flex-start;
            position: fixed;
            height: 100%;
        }

        #content {
            flex: 1;
            padding: 20px;
            margin-left: 200px;
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

        /* Add your additional CSS styles here */

        /* Style for modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0, 0, 0);
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

        /* Style for download button */
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

        /* Style for sales table */
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

        /* Style for satellite buttons */
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
            <a href="home.php" onclick="redirectToPage('home.html');"><i class="fas fa-home"></i> Dashboard</a>
            <a href="#" id="logoutLink"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <div id="content">
            <!-- Content goes here -->
            <div id="search-bar">
                <!-- Select Store Type -->
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

            <!-- Display satellite store buttons -->
            <h2>Satellite Stores</h2>
            <div id="satellite-buttons" class="satellite-button-container">
                <!-- Satellite store buttons will be dynamically inserted here -->
            </div>

            <div id="sales-table-container">
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
                        <!-- Sales data will be dynamically inserted here -->
                    </tbody>
                </table>
            </div>

            <!-- Modal for detailed sales -->
            <div id="detailed-sales-modal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <span class="close" onclick="closeModal()">&times;</span>
                        <h2>Detailed Sales Information</h2>
                    </div>
                    <div class="modal-body" id="modal-body-content">
                        <!-- Detailed sales data will be displayed here -->
                    </div>
                    <div class="modal-footer">
                        <button class="download-button" onclick="downloadSalesData()">Download Sales Data</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include your JavaScript code here -->
    <script>
        // JavaScript code goes here
        // Retrieve sales data from session
        let mainStoreSalesData = <?php echo json_encode($_SESSION['main_store_data'] ?? []); ?>;
        let satelliteStoreSalesData = <?php echo json_encode($_SESSION['satellite_store_data'] ?? []); ?>;
        let satelliteStoreLocations = <?php echo json_encode($_SESSION['satellite_store_locations'] ?? []); ?>;
        
        // Function to display sales data
        function displaySalesData() {
            var tableBody = document.getElementById('sales-table-body');
            tableBody.innerHTML = '';

            mainStoreSalesData.forEach(function(sale) {
                var row = document.createElement('tr');
                row.innerHTML = `
                    <td>${sale.product_name}</td>
                    <td>${sale.quantity_sold}</td>
                    <td>${sale.total_price}</td>
                    <td>${sale.record_date}</td>
       <td><button class="more-info-button" onclick="showDetailedSales('${sale.product_name}')">More Info</button></td>

                `;
                tableBody.appendChild(row);
            });
        }

        // Function to change the store type
        function changeStoreType() {
            var storeTypeSelect = document.getElementById('store-type-select');
            var selectedStoreType = storeTypeSelect.value;

            // Show or hide satellite buttons based on the selected store type
            var satelliteButtonContainer = document.getElementById('satellite-buttons');
            if (selectedStoreType === 'satellite') {
                satelliteButtonContainer.style.display = 'block';
                displaySatelliteButtons();
            } else {
                satelliteButtonContainer.style.display = 'none';
            }
        }

        // Function to display satellite store buttons
        function displaySatelliteButtons() {
            var satelliteButtonContainer = document.getElementById('satellite-buttons');
            satelliteButtonContainer.innerHTML = '';

            satelliteStoreLocations.forEach(function(location) {
                var button = document.createElement('button');
                button.textContent = location;
                button.onclick = function() {
                    fetchSatelliteSales(location);
                };
                satelliteButtonContainer.appendChild(button);
            });
        }

        // Function to fetch satellite store sales data
        function fetchSatelliteSales(location) {
            // Get the sales data for the selected satellite store location
            var salesData = satelliteStoreSalesData[location];
            // Display sales data
            displaySatelliteSalesData(salesData);
        }

        // Function to display satellite store sales data
        function displaySatelliteSalesData(salesData) {
            var tableBody = document.getElementById('sales-table-body');
            tableBody.innerHTML = '';

            salesData.forEach(function(sale) {
                var row = document.createElement('tr');
                row.innerHTML = `
                    <td>${sale.product_name}</td>
                    <td>${sale.quantity_sold}</td>
                    <td>${sale.total_price}</td>
                    <td>${sale.record_date}</td>
                    <td><button class="more-info-button" onclick="showDetailedSales('${sale.product_name}')">More Info</button></td>
                `;
                tableBody.appendChild(row);
            });
        }

        // Logout functionality
        document.getElementById('logoutLink').addEventListener('click', function(event) {
            // Prevent the default behavior of the link
            event.preventDefault();

            // Redirect the user to the logout.php file for logout
            window.location.href = 'logout.php';
        });

        // Function to toggle user info display
        function toggleUserInfo() {
            var userInfo = document.getElementById('user-info');
            if (userInfo.style.display === 'block') {
                userInfo.style.display = 'none';
            } else {
                userInfo.style.display = 'block';
            }
        }

        // Function to redirect to a page
        function redirectToPage(page) {
            window.location.href = page;
        }

        // Function to search for a product
        function searchProduct() {
            var searchInput = document.getElementById('product-search').value.toLowerCase();
            var rows = document.querySelectorAll('#sales-table-body tr');

            rows.forEach(function(row) {
                var productName = row.cells[0].textContent.toLowerCase();
                if (productName.includes(searchInput)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Function to show detailed sales in the modal
        function showDetailedSales(productName) {
            var modalBody = document.getElementById('modal-body-content');
            modalBody.innerHTML = '';

            var rows = document.querySelectorAll('#sales-table-body tr');
            rows.forEach(function(row) {
                var name = row.cells[0].textContent;
                if (name === productName) {
                    var cloneRow = row.cloneNode(true);
                    modalBody.appendChild(cloneRow);
                }
            });

            document.getElementById('detailed-sales-modal').style.display = 'block';
        }

        // Function to close the modal
        function closeModal() {
            document.getElementById('detailed-sales-modal').style.display = 'none';
        }

        // Function to download sales data as text file
        function downloadSalesData() {
            var salesTable = document.getElementById('sales-table');
            var data = [];

            for (var i = 0; i < salesTable.rows.length; i++) {
                var rowData = [];
                for (var j = 0; j < salesTable.rows[i].cells.length; j++) {
                    rowData.push(salesTable.rows[i].cells[j].textContent);
                }
                data.push(rowData.join(','));
            }

            var csvContent = 'data:text/csv;charset=utf-8,';
            data.forEach(function(row) {
                csvContent += row + '\r\n';
            });

            var encodedUri = encodeURI(csvContent);
            var link = document.createElement('a');
            link.setAttribute('href', encodedUri);
            link.setAttribute('download', 'sales_data.csv');
            document.body.appendChild(link); // Required for FF

            link.click();
        }

        // Call displaySalesData function initially
        displaySalesData();
    </script>
</body>

</html>

