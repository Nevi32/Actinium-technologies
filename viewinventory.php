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
            display: flex;
            flex-direction: column;
        }

        #store-selection {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
        }

        #search-bar {
            margin-bottom: 20px;
            display: flex;
            flex-direction: row;
            justify-content: flex-end;
        }

        #store-type,
        #product-search {
            margin-right: 10px;
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
            <!-- Store Selection Options -->
            <div id="store-selection">
                <label for="store-type">Select Store Type:</label>
                <select id="store-type" onchange="handleStoreTypeChange()">
                    <option value="mainstore">Main Store</option>
                    <?php
                    // Check if there are satellite stores in the session
                    $satelliteStores = $_SESSION['satellite_stores'] ?? null;

                    if ($satelliteStores) {
                        echo '<option value="satellite">Satellite Stores</option>';
                    }
                    ?>
                </select>

                <!-- Satellite Store Buttons (hidden by default) -->
                <div id="satellite-buttons" class="satellite-buttons" style="display: none;">
                    <?php
                    foreach ($satelliteStores as $satelliteStore) {
                        echo '<button class="satellite-button" onclick="handleSatelliteButtonClick(\'' . $satelliteStore['location_name'] . '\')">' . $satelliteStore['location_name'] . '</button>';
                    }
                    ?>
                </div>
            </div>

            <!-- Search Bar -->
            <div id="search-bar">
                <label for="product-search">Search Product:</label>
                <input type="text" id="product-search" placeholder="Enter product name">
                <button onclick="searchProduct()">Search</button>
            </div>

            <!-- Your existing content goes here -->
            <h1>Welcome to Inventory Management</h1>
            <p>This is where your inventory data will be displayed.</p>

            <!-- Include your JavaScript scripts here -->
            <script>
                   let mainEntryData;

            function viewInventory() {
                // Fetch main entry data from session
                const mainEntryDataSession = <?php echo json_encode($_SESSION['main_entry_data'] ?? null); ?>;

                if (mainEntryDataSession) {
                    // Update the content of the current page with the main entry data
                    mainEntryData = mainEntryDataSession;
                    displayMainEntryData(mainEntryData);
                    // Display more info button for each main entry
                    addMoreInfoButtons(mainEntryData);
                } else {
                    showAlert('No main entry data available.');
                }

                // Detailed entry data is already stored in the window object during PHP processing
            }

            function displayMainEntryData(mainEntryData, searchTerm) {
                // Display main entry data in a table
                var tableBody = document.querySelector('#main-entry-table-body');
                tableBody.innerHTML = ''; // Clear existing table content

                mainEntryData.forEach(function (entry) {
                    // Check if the product name contains the search term (case-insensitive)
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

            function addMoreInfoButtons(mainEntryData) {
                // Add more info buttons for each main entry
                // (Removed as per your request since they are unnecessary)
            }

            function showDetailedEntries(mainEntryId) {
                // Retrieve detailed entries for the selected main entry ID
                const detailedEntries = <?php echo json_encode($_SESSION['detailed_entry_data'] ?? null); ?>;
                const filteredEntries = detailedEntries.filter(function (entry) {
                    return entry.main_entry_id === mainEntryId;
                });

                // Display detailed entries in a modal
                var modalBody = document.querySelector('#modal-body-content');
                modalBody.innerHTML = ''; // Clear existing modal content

                filteredEntries.forEach(function (entry) {
                    var row = document.createElement('div');
                    row.innerHTML = `
                        <p>Quantity: ${entry.quantity}</p>
                        <p>Quantity Description: ${entry.quantity_description}</p>
                        <p>Price: ${entry.price !== null ? entry.price : 'N/A'}</p>
                        <p>Date: ${entry.record_date}</p>
                    `;
                    modalBody.appendChild(row);
                });

                // Show the modal
                document.getElementById('detailed-entries-modal').style.display = 'block';
            }

            function closeModal() {
                // Close the modal
                document.getElementById('detailed-entries-modal').style.display = 'none';
            }

            function showAlert(message) {
                // Show an alert message
                var alertMessage = document.getElementById('alert-message');
                alertMessage.innerHTML = message;
                alertMessage.style.display = 'block';

                // Hide the alert after 3 seconds
                setTimeout(function () {
                    alertMessage.style.display = 'none';
                }, 3000);
            }

            function searchProduct() {
                // Get the search input value
                var searchInput = document.getElementById('product-search').value;

                // Display main entry data based on the search input
                displayMainEntryData(mainEntryData, searchInput);
            }

            // Call viewInventory when the page is loaded
            document.addEventListener('DOMContentLoaded', function () {
                viewInventory();
            });
               document.getElementById('logoutLink').addEventListener('click', function(event) {
    // Prevent the default behavior of the link
    event.preventDefault();

    // Redirect the user to the logout.php file for logout
    window.location.href = 'logout.php';
  });
                // New functions for store selection
                function handleStoreTypeChange() {
                    var storeType = document.getElementById('store-type').value;
                    var satelliteButtons = document.getElementById('satellite-buttons');
                    satelliteButtons.style.display = storeType === 'satellite' ? 'flex' : 'none';
                }

                function handleSatelliteButtonClick(locationName) {
                    <?php
                    echo "var locationName = '$locationName';";
                    echo "var storeName = '$_SESSION[storeName]';";
                    echo "var locationType = 'satellite';";
                    ?>
                    setStoreSessionData(storeName, locationName, locationType);
                    window.location.href = 'fetchinventory.php';
                }

                function setStoreSessionData(storeName, locationName, locationType) {
                    <?php
                    echo "var storeName = '$storeName';";
                    echo "var locationName = '$locationName';";
                    echo "var locationType = '$locationType';";
                    ?>
                    <?php
                    echo "var satelliteStores = json_encode($_SESSION['satellite_stores'] ?? null);";
                    echo "var inventoryData = json_encode($_SESSION['inventory_data'] ?? null);";
                    ?>
                    var sessionData = {
                        'storeName': storeName,
                        'locationName': locationName,
                        'locationType': locationType,
                        'satelliteStores': satelliteStores,
                        'inventoryData': inventoryData
                    };
                    <?php echo "var sessionDataScript = 'var sessionData = ' + JSON.stringify(sessionData) + ';';"; ?>
                    eval(sessionDataScript);
                }
            </script>
        </div>
    </div>

    <!-- Include your additional JavaScript scripts here -->
</body>

</html>

