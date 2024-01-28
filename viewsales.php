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
        }

        .more-info-button {
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
        }

        .modal-header {
            padding: 2px 16px;
            background-color: #5cb85c;
            color: white;
        }

        .modal-body {
            padding: 2px 16px;
        }
    </style>
</head>

<body>
    <div id="dashboard">
        <div id="sidebar">
            <!-- Sidebar content here -->
        </div>
        <div id="content">
            <div class="alert" id="alert-message"></div>

            <!-- Search bar -->
            <div id="search-bar">
                <label for="product-search">Search Product:</label>
                <input type="text" id="product-search" placeholder="Enter product name">
                <button onclick="searchProduct()">Search</button>
            </div>

            <!-- Select store type for main store -->
            <?php if ($_SESSION['location_type'] === 'main_store' && isset($_SESSION['satellite_locations'])) : ?>
                <label for="store-type">Select Store Type:</label>
                <select id="store-type" onchange="viewSatelliteStoreSales(this.value)">
                    <option value="">All Stores</option>
                    <?php foreach ($_SESSION['satellite_locations'] as $satelliteLocation) : ?>
                        <option value="<?php echo $satelliteLocation; ?>"><?php echo $satelliteLocation; ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <!-- Display sales information -->
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

            <!-- Detailed entries modal -->
            <div id="detailed-entries-modal" class="modal">
                <!-- Modal content here -->
            </div>

            <script>
                 function searchProduct() {
        var searchInput = document.getElementById('product-search').value.toLowerCase();
        var salesData = <?php echo json_encode($_SESSION['sales_data'] ?? null); ?>;
        var filteredSales = salesData.filter(function(sale) {
            return sale.product_name.toLowerCase().includes(searchInput);
        });
        displaySalesData(filteredSales);
    }

    function viewSatelliteStoreSales(storeName) {
        var salesData = <?php echo json_encode($_SESSION['sales_data'] ?? null); ?>;
        var filteredSales = salesData.filter(function(sale) {
            return sale.location_name === storeName;
        });
        displaySalesData(filteredSales);
    }

    function displaySalesData(salesData) {
        var tableBody = document.getElementById('main-entry-table-body');
        tableBody.innerHTML = '';

        salesData.forEach(function(sale) {
            var row = document.createElement('tr');
            row.innerHTML = `
                <td>${sale.product_name}</td>
                <td>${sale.quantity_sold}</td>
                <td>${sale.total_price}</td>
                <td>${sale.sale_date}</td>
                <td><button class="more-info-button" onclick="showDetailedEntries(${sale.sale_id})">More Info</button></td>
            `;
            tableBody.appendChild(row);
        });
    }

    function showDetailedEntries(saleId) {
        var detailedSale = <?php echo json_encode($_SESSION['sales_data'] ?? null); ?>.find(function(sale) {
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

        setTimeout(function() {
            alertMessage.style.display = 'none';
        }, 3000);
    }

    document.addEventListener('DOMContentLoaded', function() {
        var salesData = <?php echo json_encode($_SESSION['sales_data'] ?? null); ?>;
        if (!salesData) {
            showAlert('No sales data available.');
            return;
        }

        displaySalesData(salesData);
    });

    document.getElementById('logoutLink').addEventListener('click', function(event) {
        event.preventDefault();
        window.location.href = 'logout.php';
    });


            </script>
        </div>
    </div>
</body>

</html>

