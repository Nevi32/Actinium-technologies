<?php
session_start();

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login.html if the session is not active
    header("Location: login.html");
    exit();
}

// Assuming you have a function to fetch delivery information from the database
// Replace this with your actual database logic
function getDeliveries() {
    // Implement your logic to fetch delivery information from the database
    // and return the result as an array
}

$deliveries = getDeliveries();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>

    <style>
        /* Add your CSS styles here */
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Arial', sans-serif;
        }

        #inventory-page {
            display: flex;
            height: 100%;
        }

        #sidebar {
            width: 220px;
            background-color: #333;
            color: #fff;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: flex-start;
        }

        /* ... (Include the rest of your CSS styles) */

        .delivery-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .delivery-item {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            width: 300px;
            margin-bottom: 20px;
        }

        .delivery-item p {
            margin: 0;
        }

        .delivery-item button {
            padding: 8px 15px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .delivery-item button:hover {
            background-color: #2980b9;
        }

        /* Add any additional styles as needed */
    </style>
</head>
<body>

<!-- Pop-up messages -->
<div id="success-popup" class="popup" style="background-color: #4CAF50;"></div>
<div id="error-popup" class="popup" style="background-color: #f44336;"></div>

<div id="inventory-page">
    <div id="sidebar">
        <!-- Sidebar content -->
        <!-- You can include the sidebar content here -->
    </div>

    <div id="content">
        <div class="alert" id="alert-message"></div>

        <!-- View Inventory Card -->
        <div class="section-card clickable-card" onclick="viewInventory();">
            <h3>View Inventory</h3>
            <p>Click here to view and edit your inventory.</p>
        </div>

        <!-- Inventory List -->
        <div class="section-card">
            <h3>Delivery Status</h3>
            <div id="delivery-list">
                <?php foreach ($deliveries as $delivery) : ?>
                    <div class="delivery-item">
                        <p>Delivery ID: <?php echo $delivery['delivery_id']; ?></p>
                        <p>Status: <?php echo $delivery['status']; ?></p>
                        <button onclick="clearDeliveryStatus(<?php echo $delivery['delivery_id']; ?>);">
                            Clear Status
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
 // Add your existing JavaScript code here

    function clearDeliveryStatus(deliveryId) {
        // AJAX request to cleardeliverystatus.php
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'cleardeliverystatus.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                showAlert('Delivery status cleared successfully!', 'success');
                // Optionally, update the UI or remove the cleared delivery item
            } else {
                showAlert('Failed to clear delivery status. Please try again.', 'error');
            }
        };
        xhr.send('deliveryId=' + encodeURIComponent(deliveryId));
    }

</script>

</body>
</html>

