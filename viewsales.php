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
          body, html {
            height: 100%;
            margin: 0;
            font-family: 'Arial', sans-serif;
        }

        #dashboard {
            display: flex;
            height: 100vh; /* Use viewport height to make sure the layout covers the entire screen */
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
            position: fixed; /* Fix the sidebar position */
            height: 100%;
        }

        #content {
            flex: 1;
            padding: 20px;
            margin-left: 200px; /* Adjust content area margin to accommodate the fixed sidebar */
        }

        #sidebar a {
            color: #fff;
            text-decoration: none;
            margin-bottom: 15px;
            width: 100%;
            display: flex;
            align-items: center;
        }                                                                                                                                                                                                               #sidebar i {
            margin-right: 10px;
        }

        #sidebar a:not(:last-child) {
            margin-bottom: 60px;
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
        }

        #main-entry-table-container {
            margin-top: 20px;
        }

        #main-entry-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        #main-entry-table th,
        #main-entry-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        #main-entry-table th {
            background-color: #3498db;
            color: #fff;
        }

        #main-entry-table tbody tr:hover {
            background-color: #f5f5f5;
        }                                                                                                                                                                                                              .more-info-button {
            background-color: #4caf50;
            color: white;
            border: none;
            padding: 5px 10px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 12px;
            margin: 2px 2px;
            cursor: pointer;
        }

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
        }                                                                                                                                                                                                               .modal-header {
            padding: 2px 16px;
            background-color: #5cb85c;
            color: white;
        }

        .modal-body {
            padding: 2px 16px;
        }

        #search-bar {
            margin-top: 20px;
        }
        #search-bar label {
            margin-right: 10px;
        }
        #search-bar input {
            margin-right: 10px;
        }
        #search-bar button {
            cursor: pointer;
        }
    </style>
</head>

<body>
       <div id="dashboard">
        <div id="sidebar">
            <div class="welcome-message" id="welcome-message"></div>
            <a href="#" id="user-icon" onclick="toggleUserInfo();"><i class="fas fa-user"></i> User</a>
            <div id="user-info">
                <!-- User info will be displayed here using JavaScript -->
            </div>
            <a href="notifications.html"><i class="fas fa-bell"></i> Notifications</a>
            <a href="mpesa-c2b.html"><i class="fas fa-coins"></i> Mpesa C2B</a>
            <a href="mpesa-b2b.html"><i class="fas fa-exchange-alt"></i> Mpesa B2B</a>
            <a href="home.html" onclick="redirectToPage('home.html');"><i class="fas fa-home"></i> Dashboard</a>
            <a href="#" id="logoutLink"><i class="fas fa-sign-out-alt"></i> Logout</a>
     </div>
    <div id="content">
        <div class="alert" id="alert-message"></div>

        <!-- Add the search bar -->
        <div id="search-bar">
            <label for="product-search">Search Product:</label>
            <input type="text" id="product-search" placeholder="Enter product name">
            <button onclick="searchProduct()">Search</button>
        </div>

        <div id="main-entry-table-container">
            <h3>Sales Information</h3>
            <table id="main-entry-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Total Quantity Sold</th>
                        <th>Total Price</th>
                        <th>Sale Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="main-entry-table-body">
                    <!-- Sales data will be dynamically inserted here -->
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
            </div>
        </div>

        <script>
            let salesData;

            function viewSales() {
                // Fetch sales data from session
                const salesDataSession = <?php echo json_encode($_SESSION['sales_data'] ?? null); ?>;

                if (salesDataSession) {
                    // Update the content of the current page with the sales data
                    salesData = salesDataSession;
                    displaySalesData(salesData);
                    // Display more info button for each sale
                    addMoreInfoButtons(salesData);
                } else {
                    showAlert('No sales data available.');
                }
            }

            function displaySalesData(salesData, searchTerm) {
                var tableBody = document.querySelector('#main-entry-table-body');
                tableBody.innerHTML = '';

                salesData.forEach(function (sale) {
                    if (!searchTerm || sale.product_name.toLowerCase().includes(searchTerm.toLowerCase())) {
                        var row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${sale.product_name}</td>
                            <td>${sale.quantity_sold}</td>
                            <td>${sale.total_price}</td>
                            <td>${sale.sale_date}</td>
                            <td><button class="more-info-button" onclick="showDetailedEntries(${sale.sale_id})">More Info</button></td>
                        `;
                        tableBody.appendChild(row);
                    }
                });
            }

            function addMoreInfoButtons(salesData) {
                // Additional info buttons for each sale (if needed)
            }

            function showDetailedEntries(saleId) {
                const detailedSale = salesData.find(function (sale) {
                    return sale.sale_id === saleId;
                });

                var modalBody = document.querySelector('#modal-body-content');
                modalBody.innerHTML = '';

                var row = document.createElement('div');
                row.innerHTML = `
                    <p>Product Name: ${detailedSale.product_name}</p>
                    <p>Quantity Sold: ${detailedSale.quantity_sold}</p>
                    <p>Total Price: ${detailedSale.total_price}</p>
                    <p>Sale Date: ${detailedSale.sale_date}</p>
                `;
                modalBody.appendChild(row);

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

            function searchProduct() {
                var searchInput = document.getElementById('product-search').value;
                displaySalesData(salesData, searchInput);
            }

            document.addEventListener('DOMContentLoaded', function () {
                viewSales();
            });
                        document.getElementById('logoutLink').addEventListener('click', function(event) {
    // Prevent the default behavior of the link
    event.preventDefault();

    // Redirect the user to the logout.php file for logout
    window.location.href = 'logout.php';
  });
        </script>
    </div>
</body>

</html>

