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

    <style>
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
                <!-- Store Type Selection -->
                <label for="store-type-select">Select Store Type:</label>
                <select id="store-type-select" onchange="changeStoreType()">
                    <option value="main_store">Main Store</option>
                    <option value="satellite">Satellite Store</option>
                </select>

                <!-- Select Main Store -->
                <label for="main-store-select">Select Main Store:</label>
                <select id="main-store-select" onchange="initializeSatelliteStores()">
                    <option value="1">Main Store 1</option>
                    <option value="2">Main Store 2</option>
                    <!-- Add options for other main stores as needed -->
                </select>

                <!-- Search Bar -->
                <label for="product-search">Search Product:</label>
                <input type="text" id="product-search" placeholder="Enter product name">
                <button onclick="searchProduct()">Search</button>
            </div>

            <div id="satellite-buttons-container" style="display: none;"></div>

            <div id="sales-table-container">
                <table id="sales-table">
                    <thead>
                        <tr>
                            <th>Sale ID</th>
                            <th>Main Entry ID</th>
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
        // Initialize salesData JavaScript variable
        let salesData = <?php echo json_encode($_SESSION['sales_data'] ?? []); ?>;

        // Function to display sales data
        function displaySalesData() {
            var tableBody = document.getElementById('sales-table-body');
            tableBody.innerHTML = '';

            salesData.forEach(function(sale) {
                var row = document.createElement('tr');
                row.innerHTML = `
                    <td>${sale.sale_id}</td>
                    <td>${sale.main_entry_id}</td>
                    <td>${sale.product_name}</td>
                    <td>${sale.quantity_sold}</td>
                    <td>${sale.total_price}</td>
                    <td>${sale.record_date}</td>
                    <td><button class="more-info-button" onclick="showDetailedSales(${sale.sale_id})">More Info</button></td>
                `;
                tableBody.appendChild(row);
            });
        }

        // Function to show detailed sales in the modal
        function showDetailedSales(saleId) {
            const detailedSale = salesData.find(function(sale) {
                return sale.sale_id === saleId;
            });

            var modalBody = document.getElementById('modal-body-content');
            modalBody.innerHTML = '';

            var row = document.createElement('div');
            row.innerHTML = `
                <p>Sale ID: ${detailedSale.sale_id}</p>
                <p>Main Entry ID: ${detailedSale.main_entry_id}</p>
                <p>Product Name: ${detailedSale.product_name}</p>
                <p>Quantity Sold: ${detailedSale.quantity_sold}</p>
                <p>Total Price: ${detailedSale.total_price}</p>
                <p>Record Date: ${detailedSale.record_date}</p>
            `;
            modalBody.appendChild(row);

            document.getElementById('detailed-sales-modal').style.display = 'block';
        }

        // Function to close the modal
        function closeModal() {
            document.getElementById('detailed-sales-modal').style.display = 'none';
        }

        // Function to download sales data as text file
        function downloadSalesData() {
            // Convert sales data to text format
            const data = JSON.stringify(salesData, null, 2);

            // Create a Blob with the data
            const blob = new Blob([data], { type: 'text/plain' });

            // Create a temporary anchor element
            const a = document.createElement('a');
            a.href = window.URL.createObjectURL(blob);

            // Set the file name
            a.download = 'sales_data.txt';

            // Append the anchor to the body and click it to trigger download
            document.body.appendChild(a);
            a.click();

            // Remove the anchor from the body
            document.body.removeChild(a);
        }

        // Function to search for a product
        function searchProduct() {
            var searchInput = document.getElementById('product-search').value.toLowerCase();
            var filteredSalesData = salesData.filter(function(sale) {
                return sale.product_name.toLowerCase().includes(searchInput);
            });
            displaySalesData(filteredSalesData);
        }

        // Function to change the store type
        function changeStoreType() {
            var storeTypeSelect = document.getElementById('store-type-select');
            var selectedStoreType = storeTypeSelect.value;

            if (selectedStoreType === 'main_store') {
                // Show satellite buttons container
                document.getElementById('satellite-buttons-container').style.display = 'block';
            } else {
                // Hide satellite buttons container
                document.getElementById('satellite-buttons-container').style.display = 'none';

                // Retrieve and display satellite stores associated with the selected main store
                var mainStoreId = document.getElementById('main-store-select').value;
                var satelliteStores = getSatelliteStores(mainStoreId);
                displaySatelliteStores(satelliteStores);
            }
        }

        // Function to retrieve satellite stores associated with the selected main store
        function getSatelliteStores(mainStoreId) {
            // Example code to retrieve satellite stores using AJAX or fetch API
            // Replace this with your actual implementation to retrieve satellite stores data
            // For demonstration purposes, I'm returning static data here
            var satelliteStoresData = [
                { id: 1, name: 'Satellite Store 1', main_store_id: 1 },
                { id: 2, name: 'Satellite Store 2', main_store_id: 1 },
                { id: 3, name: 'Satellite Store 3', main_store_id: 2 },
                // Add more satellite stores as needed
            ];

            // Filter satellite stores based on the selected main store ID
            var satelliteStores = satelliteStoresData.filter(function(store) {
                return store.main_store_id == mainStoreId;
            });

            return satelliteStores;
        }

        // Function to display satellite stores
        function displaySatelliteStores(satelliteStores) {
            var satelliteButtonsContainer = document.getElementById('satellite-buttons-container');
            satelliteButtonsContainer.innerHTML = '';

            satelliteStores.forEach(function(store) {
                var button = document.createElement('button');
                button.textContent = store.name;
                button.className = 'satellite-button';
                // Add click event listener to handle click on satellite store button
                button.addEventListener('click', function() {
                    // Add your logic to handle click on satellite store button
                    // For example, you can retrieve and display sales data for the selected satellite store
                    // This can involve making an AJAX request to fetch satellite store sales data
                    console.log('Clicked on satellite store: ' + store.name);
                });
                satelliteButtonsContainer.appendChild(button);
            });
        }

        // Function to initialize satellite stores based on the selected main store
        function initializeSatelliteStores() {
            var mainStoreId = document.getElementById('main-store-select').value;
            var satelliteStores = getSatelliteStores(mainStoreId);
            displaySatelliteStores(satelliteStores);
        }

        // Call displaySalesData when the page is loaded
        document.addEventListener('DOMContentLoaded', function() {
            displaySalesData();
        });

        // Logout functionality
        document.getElementById('logoutLink').addEventListener('click', function(event) {
            // Prevent the default behavior of the link
            event.preventDefault();

            // Redirect the user to the logout.php file for logout
            window.location.href = 'logout.php';
        });
    </script>
</body>

</html>

