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
     <!-- In the head section of your HTML files -->
   <link rel="stylesheet" type="text/css" href="main.css">

    <style>
      body,
html {
    height: 100%;
    margin: 0;
    font-family: 'Arial', sans-serif;
}

#dashboard {
    display: flex;
    min-height: 100vh; /* Using min-height to ensure the dashboard fills the viewport height */
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
    margin-left: 150px; /* Adjusted margin for smaller screens */
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
    margin-bottom: 20px; /* Increased margin for better separation */
}

#welcome-message {
    margin-bottom: 20px; /* Added margin for better separation */
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

#satellite-buttons-container {
    margin-top: 20px;
    display: flex;
    flex-wrap: wrap;
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

.modal-content {
    /* Adjust max-height for scrolling */
    max-height: 400px;
    /* Enable scrolling */
    overflow-y: auto;
}

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
                        $inventoryData = $_SESSION['mainstoreData'] ?? $_SESSION['satelliteData'] ?? null;

                        if ($inventoryData) {
                            foreach ($inventoryData as $entry) {
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
        </div>
        <div class="modal-body" id="modal-body-content">
            <!-- Detailed inventory data will be dynamically inserted here -->
        </div>
        <!-- Add download button -->
        <div class="modal-footer">
            <button class="download-button" onclick="downloadData()">Download Data</button>
        </div>
    </div>
</div>

            <!-- Modify the JavaScript code inside your <script> tag at the end of the HTML body -->
            <script>
                // Initialize inventoryData JavaScript variable
                let inventoryData = <?php echo json_encode($inventoryData); ?>;

                // Function to view inventory data
                function viewInventory() {
                    // Fetch inventory data from session
                    const inventoryDataSession = <?php echo json_encode($_SESSION['mainstoreData'] ?? $_SESSION['satelliteData'] ?? null); ?>;

                    if (inventoryDataSession) {
                        // Update the content of the current page with the inventory data
                        inventoryData = inventoryDataSession;
                        displayInventoryData(inventoryData);
                        // Display more info button for each entry
                        addMoreInfoButtons(inventoryData);
                    } else {
                        showAlert('No inventory data available.');
                    }
                }

                // Function to display inventory data
                function displayInventoryData(inventoryData, searchTerm) {
                    var tableBody = document.querySelector('#main-entry-table-body');
                    tableBody.innerHTML = '';

                    inventoryData.forEach(function (entry) {
                        if (!searchTerm || entry.product_name.toLowerCase().includes(searchTerm.toLowerCase())) {
                            var row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${entry.product_name}</td>
                                <td>${entry.category}</td>
                                <td>${entry.total_quantity}</td>
                                <td>${entry.quantity_description}</td>
                                <td><button class="more-info-button" onclick="showDetailedEntries(${entry.main_entry_id})">More Info</button></td>
                            `;
                            tableBody.appendChild(row);
                        }
                    });
                }

                // Function to add more info buttons
                function addMoreInfoButtons(inventoryData) {
                    // Additional info buttons for each entry (if needed)
                }

                // Function to show detailed entries in the modal
                function showDetailedEntries(mainEntryId) {
                    const detailedEntry = inventoryData.find(function (entry) {
                        return entry.main_entry_id === mainEntryId;
                    });

                    const detailedIndividualEntries = <?php echo json_encode($_SESSION['mainstore_individual_entry_data'] ?? []); ?>;
                    const filteredEntries = detailedIndividualEntries.filter(function (entry) {
                        return entry.main_entry_id === mainEntryId;
                    });

                    var modalBody = document.querySelector('#modal-body-content');
                    modalBody.innerHTML = '';

                    var row = document.createElement('div');
                    row.innerHTML = `
                        <p>Product Name: ${detailedEntry.product_name}</p>
                        <p>Category: ${detailedEntry.category}</p>
                        <p>Total Quantity: ${detailedEntry.total_quantity}</p>
                        <p>Quantity Description: ${detailedEntry.quantity_description}</p>
                        <h3>Individual Entries:</h3>
                        <ul>
                            ${filteredEntries.map(function (individualEntry) {
                                return `<li>Quantity: ${individualEntry.quantity}, Description: ${individualEntry.quantity_description}, Price: ${individualEntry.price}, Date: ${individualEntry.record_date}</li>`;
                            }).join('')}
                        </ul>
                    `;
                    modalBody.appendChild(row);

                    document.getElementById('detailed-entries-modal').style.display = 'block';
                }

                // Function to close the modal
                function closeModal() {
                    document.getElementById('detailed-entries-modal').style.display = 'none';
                }

                // Function to display an alert
                function showAlert(message) {
                    var alertMessage = document.getElementById('alert-message');
                    alertMessage.innerHTML = message;
                    alertMessage.style.display = 'block';

                    setTimeout(function () {
                        alertMessage.style.display = 'none';
                    }, 3000);
                }

                // Function to search for a product
                function searchProduct() {
                    var searchInput = document.getElementById('product-search').value;
                    displayInventoryData(inventoryData, searchInput);
                }

                // Function to change the store type
                function changeStoreType() {
                    var storeTypeSelect = document.getElementById('store-type-select');
                    var selectedStoreType = storeTypeSelect.value;

                    if (selectedStoreType === 'main_store') {
                        // Redirect to the main store page
                        window.location.href = 'viewinventory.php?storeType=main_store';
                    } else if (selectedStoreType === 'satellite') {
                        // Display buttons with satellite locations
                        displaySatelliteButtons();
                    }
                }

                // Function to display buttons with satellite locations
                function displaySatelliteButtons() {
                    var satelliteData = <?php echo json_encode($_SESSION['satelliteData'] ?? []); ?>;
                    var buttonsContainer = document.getElementById('satellite-buttons-container');

                    // Clear existing buttons
                    buttonsContainer.innerHTML = '';

                    // Create buttons for each satellite store
                    satelliteData.forEach(function (satelliteStore) {
                        var button = document.createElement('button');
                        button.textContent = satelliteStore.location_name;
                        button.addEventListener('click', function () {
                            // Display satellite store data when a button is clicked
                            displaySatelliteStoreData(satelliteStore);
                        });

                        buttonsContainer.appendChild(button);
                    });
                }

                // Function to display satellite store data
                function displaySatelliteStoreData(satelliteStore) {
                    // Update the displayed inventory with the data from the selected satellite store
                    inventoryData = satelliteStore.main_entry_data;
                    displayInventoryData(inventoryData);
                    addMoreInfoButtons(inventoryData);
                }

                // Call viewInventory when the page is loaded
                document.addEventListener('DOMContentLoaded', function () {
                    viewInventory();
                });

                // Function to download data as text file
function downloadData() {
    // Convert inventory data to text format
    const data = JSON.stringify(inventoryData, null, 2);

    // Create a Blob with the data
    const blob = new Blob([data], { type: 'text/plain' });

    // Create a temporary anchor element
    const a = document.createElement('a');
    a.href = window.URL.createObjectURL(blob);

    // Set the file name
    a.download = 'inventory_data.txt';

    // Append the anchor to the body and click it to trigger download
    document.body.appendChild(a);
    a.click();

    // Remove the anchor from the body
    document.body.removeChild(a);
}


                // Logout functionality
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

