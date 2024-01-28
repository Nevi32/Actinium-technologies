<?php
// Start the session
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Inventory</title>
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

        .download-button {
            background-color: #3498db;
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
            max-height: 70%;
            overflow-y: auto;
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
                <h1>Welcome to Inventory Management</h1>
                <p>This is where your inventory data will be displayed.</p>

                <table id="main-entry-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Total Quantity</th>
                            <th>Quantity Description</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="main-entry-table-body">
                        <!-- Display inventory data using PHP -->
                        <?php
                        // Check if inventory data is set in the session
                        $mainEntryData = $_SESSION['main_entries'] ?? null;

                        if ($mainEntryData) {
                            foreach ($mainEntryData as $entry) {
                                echo "<tr>";
                                echo "<td>{$entry['product_name']}</td>";
                                echo "<td>{$entry['category']}</td>";
                                echo "<td>{$entry['total_quantity']}</td>";
                                echo "<td>{$entry['quantity_description']}</td>";
                                echo "<td><button class=\"more-info-button\" onclick=\"showDetailedEntries({$entry['main_entry_id']})\">More Info</button></td>";
                                echo "</tr>";
                            }
                        } else {
                            // Display a message if no inventory data is available
                            echo "<tr><td colspan='5'>No inventory data available.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Modal for detailed entries -->
            <div id="detailed-entries-modal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <span class="close" onclick="closeModal()">&times;</span>
                        <h2>Detailed Info on Inventory Entry</h2>
                        <button class="download-button" onclick="downloadDetails()">Download</button>
                    </div>
                    <div class="modal-body" id="modal-body-content">
                        <!-- Detailed entry data will be displayed here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Your existing JavaScript code goes here

       // Function to show detailed entries in the modal
    function showDetailedEntries(mainEntryId) {
        // Fetch main entry data from session
        let mainEntryData = <?php echo json_encode($_SESSION['main_entries'] ?? null); ?>;
        // Fetch individual entry data from session
        let individualEntryData = <?php echo json_encode($_SESSION['inventory_entries'] ?? null); ?>;

        if (!mainEntryData || !individualEntryData) {
            console.error("Main entry data or individual entry data not found in session.");
            return;
        }

        // Find the main entry corresponding to the given mainEntryId
        let mainEntry = mainEntryData.find(entry => entry.main_entry_id === mainEntryId);

        if (!mainEntry) {
            console.error("Main entry not found for main entry ID:", mainEntryId);
            return;
        }

        let modalBody = document.getElementById('modal-body-content');
        modalBody.innerHTML = '';

        // Generate HTML for detailed entries
        let detailHtml = `
            <p>Product Name: ${mainEntry.product_name}</p>
            <p>Category: ${mainEntry.category}</p>
        `;

        // Filter individual entry data for the given main entry ID
        let detailedEntries = individualEntryData.filter(entry => entry.main_entry_id === mainEntryId);

        if (detailedEntries.length === 0) {
            detailHtml += `
                <p>Total Quantity: N/A</p>
                <p>Quantity Description: N/A</p>
                <p>Price: N/A</p>
                <p>Record Date: N/A</p>
            `;
        } else {
            detailedEntries.forEach(entry => {
                detailHtml += `
                    <p>Total Quantity: ${entry.quantity || 'N/A'}</p>
                    <p>Quantity Description: ${entry.quantity_description || 'N/A'}</p>
                    <p>Price: ${entry.price || 'N/A'}</p>
                    <p>Record Date: ${entry.record_date || 'N/A'}</p>
                `;
            });
        }

        modalBody.insertAdjacentHTML('beforeend', detailHtml);

        // Display the modal
        document.getElementById('detailed-entries-modal').style.display = 'block';
    }

        // Function to close the modal
        function closeModal() {
            document.getElementById('detailed-entries-modal').style.display = 'none';
        }

        // Function to search for a product
        function searchProduct() {
            let searchTerm = document.getElementById('product-search').value.toLowerCase();
            let mainEntryData = <?php echo json_encode($_SESSION['main_entries'] ?? null); ?>;

            if (!mainEntryData) {
                console.error("Main entry data not found in session.");
                return;
            }

            // Filter main entry data based on search term
            let filteredEntries = mainEntryData.filter(entry => entry.product_name.toLowerCase().includes(searchTerm));

            // Display filtered data
            let tableBody = document.getElementById('main-entry-table-body');
            tableBody.innerHTML = '';

            filteredEntries.forEach(entry => {
                let row = document.createElement('tr');
                row.innerHTML = `
                    <td>${entry.product_name}</td>
                    <td>${entry.category}</td>
                    <td>${entry.total_quantity}</td>
                    <td>${entry.quantity_description}</td>
                    <td><button class="more-info-button" onclick="showDetailedEntries(${entry.main_entry_id})">More Info</button></td>
                `;
                tableBody.appendChild(row);
            });
        }

        // Function to download the modal content as a text file
        function downloadDetails() {
            let modalContent = document.getElementById('modal-body-content').innerText;
            let filename = 'inventory_details.txt';
            let blob = new Blob([modalContent], { type: 'text/plain' });

            // Create a temporary link element
            let link = document.createElement('a');
            link.download = filename;
            link.href = window.URL.createObjectURL(blob);

            // Trigger the download
            link.click();

            // Clean up
            window.URL.revokeObjectURL(link.href);
        }

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

