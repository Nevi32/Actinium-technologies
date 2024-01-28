<?php
session_start(); // Start the session

// Check if the user is not logged in, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Retrieve user information from session
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];
$store_name = isset($_SESSION['store_name']) ? $_SESSION['store_name'] : "";
$location_name = isset($_SESSION['location_name']) ? $_SESSION['location_name'] : "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        /* Body CSS */
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Arial', sans-serif;
        }

        #sales-page {
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

        #sidebar a {
            color: #fff;
            text-decoration: none;
            margin-bottom: 15px;
            width: 100%;
            display: flex;
            align-items: center;
            transition: background-color 0.3s;
        }

        #sidebar i {
            margin-right: 10px;
            transition: transform 0.2s;
        }

        #sidebar a:not(:last-child) {
            margin-bottom: 60px;
        }

        #sidebar a:hover {
            background-color: #555;
        }

        #sidebar a:hover i {
            transform: translateY(-3px);
        }

        #user-icon {
            margin-bottom: 20px;
        }

        #user-info {
            display: none;
            color: #fff;
            margin-top: 10px;
            padding: 10px;
            background-color: #555;
            border-radius: 15px;
        }

        #content {
            flex: 1;
            padding: 20px;
        }

        .section-card {
            width: 100%;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            background-color: #3498db;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .section-card h3 {
            color: #fff;
            margin-bottom: 10px;
        }

        .section-card label {
            color: #fff;
            margin-right: 10px;
            font-weight: bold;
        }

        .section-card input, .section-card select {
            width: calc(100% - 12px);
            padding: 8px;
            margin-bottom: 15px;
            border: none;
            border-radius: 5px;
        }

        .section-card button {
            width: calc(100% - 12px);
            padding: 8px;
            border: none;
            background-color: #fff;
            color: #3498db;
            border-radius: 5px;
            cursor: pointer;
        }

        .section-card button:hover {
            background-color: #eee;
        }

        .section-card:hover {
            transform: scale(1.02);
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
        }

        #alert-message {
            display: none;
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 15px;
            background-color: #e74c3c;
            color: #fff;
            border-radius: 5px;
            text-align: center;
            z-index: 1000;
        }

        /* Your CSS styles here */

    </style>
</head>
<body>

<div id="sales-page">
    <div id="sidebar">
        <!-- Display welcome message and user info -->
        <div class="welcome-message" id="welcome-message">Welcome to <?php echo htmlspecialchars($store_name); ?>'s Sales Page</div>
        <a href="#" id="user-icon" onclick="toggleUserInfo();"><i class="fas fa-user"></i> <?php echo htmlspecialchars($username); ?> (<?php echo htmlspecialchars($store_name); ?> - <?php echo htmlspecialchars($location_name); ?>)</a>
        <div id="user-info" style="display: none;">
            <!-- Display user info using PHP -->
            User ID: <?php echo htmlspecialchars($user_id); ?><br>
            Username: <?php echo htmlspecialchars($username); ?><br>
            Role: <?php echo htmlspecialchars($role); ?><br>
        </div>
        <a href="notifications.html"><i class="fas fa-bell"></i> Notifications</a>
        <a href="mpesa-c2b.html"><i class="fas fa-coins"></i> Mpesa C2B</a>
        <a href="mpesa-b2b.html"><i class="fas fa-exchange-alt"></i> Mpesa B2B</a>
        <a href="home.php" onclick="redirectToPage('home.php');"><i class="fas fa-home"></i> Dashboard</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div id="content">
        <div class="section-card">
            <h3>Record Sale</h3>
            <form id="sales-form">
                <label for="productName">Product Name:</label>
                <input type="text" name="productName" placeholder="Product name" required list="productList">

                <label for="category">Category:</label>
                <input type="text" name="category" placeholder="Category" list="categoryList">

                <label for="quantity">Quantity Sold:</label>
                <input type="number" name="quantity" step="0.01" placeholder="Quantity" required>

                <label for="totalPrice">Total Price:</label>
                <input type="number" name="totalPrice" step="0.01" placeholder="Total Price" required>

                <!-- Hidden fields for store name and location -->
                <input type="hidden" name="storeName" value="<?php echo htmlspecialchars($store_name); ?>">
                <input type="hidden" name="locationName" value="<?php echo htmlspecialchars($location_name); ?>">

                <!-- Visible field for staff name -->
                <label for="staffName">Staff Name:</label>
                <select name="staffName" id="staffName">
                    <!-- Options will be dynamically populated using JavaScript -->
                </select>

                <button type="submit">Record Sale</button>
            </form>
        </div>

        <div class="section-card clickable-card" onclick="viewSales();" style="background-color: #2ecc71;">
            <h3>View Sales</h3>
            <p>Click here to view and manage your sales records.</p>
        </div>
    </div>
</div>

<div id="alert-message"></div>

<script>
    // Your JavaScript code here

    // Function to toggle user information display
    function toggleUserInfo() {
        var userInfo = document.getElementById('user-info');
        userInfo.style.display = (userInfo.style.display === 'none') ? 'block' : 'none';
    }

    // Function to redirect to a page with user information
    function redirectToPage(page) {
        var urlParams = new URLSearchParams();
        urlParams.append('user_id', '<?php echo htmlspecialchars($user_id); ?>');
        urlParams.append('username', '<?php echo htmlspecialchars($username); ?>');
        urlParams.append('role', '<?php echo htmlspecialchars($role); ?>');
        urlParams.append('store_name', '<?php echo htmlspecialchars($store_name); ?>');
        window.location.href = page + '?' + urlParams.toString();
    }

    // Function to redirect to view sales page
    function viewSales() {
        window.location.href = 'fetchsales.php';
    }

    // Function to display alert message
    function showAlert(message) {
        var alertMessage = document.getElementById('alert-message');
        alertMessage.innerText = message;
        alertMessage.style.display = 'block'; // Display the alert message
        setTimeout(function () {
            alertMessage.style.display = 'none'; // Hide the alert after 3 seconds
        }, 3000);
    }

    // Function to fetch staff names associated with the store
    function fetchStaffNames() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'fetchstaff.php', true);
        xhr.onload = function () {
            if (xhr.status >= 200 && xhr.status < 400) {
                var staffNames = JSON.parse(xhr.responseText);
                var staffDropdown = document.getElementById('staffName');
                staffNames.forEach(function (name) {
                    var option = document.createElement('option');
                    option.value = name;
                    option.textContent = name;
                    staffDropdown.appendChild(option);
                });
            } else {
                console.error('Failed to fetch staff names');
            }
        };
        xhr.onerror = function () {
            console.error('Connection error');
        };
        xhr.send();
    }

    // Function to fetch products based on input value
    function fetchProducts(inputValue) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'fetchproducts.php?input=' + inputValue, true);
        xhr.onload = function () {
            if (xhr.status >= 200 && xhr.status < 400) {
                var productsData = JSON.parse(xhr.responseText);
                updateProductOptions(productsData);
            } else {
                console.error('Failed to fetch products');
            }
        };
        xhr.onerror = function () {
            console.error('Connection error');
        };
        xhr.send();
    }

    // Function to update product options
    function updateProductOptions(productsData) {
        var productNameInput = document.querySelector('input[name="productName"]');
        var categoryInput = document.querySelector('input[name="category"]');

        // Clear existing options
        productNameInput.innerHTML = '';
        categoryInput.innerHTML = '';

        // Add new options
        productsData.products.forEach(function (product) {
            var option = document.createElement('option');
            option.value = product;
            option.textContent = product;
            productNameInput.appendChild(option);
        });

        productsData.categories.forEach(function (category) {
            var option = document.createElement('option');
            option.value = category;
            option.textContent = category;
            categoryInput.appendChild(option);
        });
    }

    // Event listener for dynamically updating product options
    document.addEventListener('DOMContentLoaded', function () {
        fetchStaffNames();
        var salesForm = document.querySelector('#sales-form');
        salesForm.addEventListener('submit', function (event) {
            event.preventDefault();
            var formData = new FormData(salesForm);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'recordsale.php', true);
            xhr.onload = function () {
                if (xhr.status >= 200 && xhr.status < 400) {
                    showAlert(xhr.responseText); // Display the response message
                } else {
                    showAlert('Error: ' + xhr.status);
                }
            };
            xhr.onerror = function () {
                showAlert('Connection error');
            };
            xhr.send(formData);
        });
        var productNameInput = document.querySelector('input[name="productName"]');
        productNameInput.addEventListener('input', function (event) {
            fetchProducts(event.target.value);
        });
        var categoryInput = document.querySelector('input[name="category"]');
        categoryInput.addEventListener('input', function (event) {
            fetchProducts(event.target.value);
        });
    });
</script>

</body>
</html>

