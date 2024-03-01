<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Resources Management</title>
    <link rel="stylesheet" href="SRstyles.css">
    <!-- In the head section of your HTML files -->
   <link rel="stylesheet" type="text/css" href="main.css">

</head>
<body>
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
?>
<div id="dashboard">
    <div id="sidebar">
          <a href="#" id="user-icon" onclick="toggleUserInfo();"><i class="fas fa-user"></i> User</a>
      <div id="user-info">
        <?php
        echo "User ID: " . $_SESSION['user_id'] . " <br> Username: " . $_SESSION['username'] . " <br> Role: " . $_SESSION['role'];

        if ($_SESSION['role'] === 'owner' || $_SESSION['comp_staff'] == 1) {
          echo "<br> Store Name: " . $_SESSION['store_name'] . " <br> Location: " . $_SESSION['location_name'];
        }
        ?>
      </div>
      <a href="fetch_notifications.php"><i class="fas fa-bell"></i> Notifications</a>
      <a href="mpesa-c2b.html"><i class="fas fa-coins"></i> Mpesa C2B</a>
      <a href="mpesa-b2b.html"><i class="fas fa-exchange-alt"></i> Mpesa B2B</a>
      <a href="home.php" onclick="redirectToPage('hom.php');"><i class="fas fa-home"></i> Dashboard</a>
      <a href="#" id="logoutLink" onclick="logout();"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    <div id="content">
        <div class="welcome-message" id="welcome-message">
            <?php echo "Welcome to the " . $_SESSION['store_name'] . " System"; ?>
        </div>
        <div class="row">
            <div class="card" onclick="fetchStaffInfo();">
                <h3>Manage Staff</h3>
                <p>Manage your store's staff members.</p>
            </div>
            <div class="card" onclick="togglePopup('suppliers-popup');">
                <h3>Manage Suppliers</h3>
                <p>Manage your store's suppliers and vendors.</p>
            </div>
        </div>
        <div class="row">
            <div class="card" onclick="togglePopup('expenses-popup');">
                <h3>Record Expenses</h3>
                <p>Record your store's expenses.</p>
            </div>
            <div class="card" onclick="fetchProductFinance();">
                <h3>Prices</h3>
                <p>Manage prices of products in your store.</p>
            </div>
        </div>
    </div>
</div>

<div id="staff-popup" class="popup">
    <div id="staff-popup-content" class="popup-content">
        <!-- Staff details table will be populated here -->
    </div>
    <div class="popup-buttons">
        <button onclick="window.location.href = 'registerX.php';">Add Staff</button>
        <button onclick="resetCommission()">Reset Commissions</button>
    </div>
</div>

<div class="overlay" onclick="closePopup()"></div>

<div id="suppliers-popup" class="popup">
    <div class="popup-content">
        <h2>Add Supplier</h2>
        <form id="addSupplierForm">
            <label for="supplierName">Supplier Name:</label>
            <input type="text" id="supplierName" name="supplierName" required><br>
            <label for="phoneNumber">Phone Number:</label>
            <input type="text" id="phoneNumber" name="phoneNumber" required><br>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>
            <label for="address">Address:</label>
            <textarea id="address" name="address" required></textarea><br>
            <button type="submit">Add Supplier</button>
        </form>
        <button onclick="fetchSuppliersInfo()">View Suppliers</button>
        <div id="suppliers-list"></div>
    </div>
</div>

<div id="expenses-popup" class="popup">
    <div class="popup-content">
        <h2>Record Expenses</h2>
        <form id="expensesForm">
            <div id="expensesList">
                <!-- Expenses will be dynamically added here -->
            </div>
            <button type="button" onclick="addExpenseField()">Add Expense</button>
            <button type="submit">Record Expenses</button>
        </form>
    </div>
</div>

<div id="prices-popup" class="popup">
    <div class="popup-content">
        <h2>Price Management</h2>
        <div>
            <label for="dynamicPrices">Dynamic Prices:</label>
            <input type="checkbox" id="dynamicPrices" onchange="toggleDynamicPrices()">
        </div>
         <table id="productPricesTable">
    <thead>
        <tr>
            <th>Product Name</th>
            <th>Category</th>
            <th>Buying Price (per unit)</th>
            <th>Selling Price</th>
            <th>Profit</th>
            <th>Percentage Profit</th>
        </tr>
    </thead>
    <tbody>
        <!-- Product rows will be dynamically added here -->
        <tr>
            <td>Product A</td>
            <td>Category A</td>
            <td>10.00</td>
            <td><input type="number" name="sellingPrice_ProductA_0" step="0.01" required></td>
            <td><input type="number" class="profit" readonly></td>
            <td><input type="number" class="percentageProfit" readonly></td>
            <!-- Hidden fields for product info -->
            <input type="hidden" name="productName[]" value="Product A">
            <input type="hidden" name="category[]" value="Category A">
            <input type="hidden" name="buyingPrice[]" value="10.00">
        </tr>
        <!-- Add more rows for other products -->
    </tbody>
</table>
      <button type="button" onclick="calculateAllProfits()">Calculate Profits</button>
       <button type="button" onclick="setAllPrices()">Set Prices</button>
    </div>
</div>

<!-- Overlay for pop-up -->
<div class="overlay" onclick="closePopup()"></div>

<script>
function togglePopup(popupId) {
    var popup = document.getElementById(popupId);
    var overlay = document.querySelector('.overlay');
    if (popup.style.display === 'block') {
        popup.style.display = 'none';
        overlay.style.display = 'none';
    } else {
        popup.style.display = 'block';
        overlay.style.display = 'block';
    }
}

function closePopup() {
    var popups = document.querySelectorAll('.popup');
    var overlay = document.querySelector('.overlay');
    popups.forEach(function(popup) {
        popup.style.display = 'none';
    });
    overlay.style.display = 'none';
}



function fetchStaffInfo() {
    fetch('fetchstaff.php')
        .then(response => response.json())
        .then(data => {
            var popupContent = document.getElementById('staff-popup-content');
            var staffTable = '<table>';
            staffTable += '<tr><th>Name</th><th>Location</th><th>Commission</th><th>Actions</th></tr>';
            data.forEach(staff => {
                staffTable += '<tr>';
                staffTable += '<td>' + staff.name + '</td>';
                staffTable += '<td>' + staff.location + '</td>';
                staffTable += '<td>' + staff.commission + '</td>';
                staffTable += '<td>';
                staffTable += '<button onclick="removeStaff(' + staff.user_id + ')" data-user-id="' + staff.user_id + '">Remove Staff</button>';
                staffTable += '<button onclick="calculateCommission(' + staff.user_id + ')">Calculate Commission</button>';
                staffTable += '</td>';
                staffTable += '</tr>';
            });
            staffTable += '</table>';
            popupContent.innerHTML = staffTable;

            var popup = document.getElementById('staff-popup');
            var overlay = document.querySelector('.overlay');
            popup.style.display = 'block';
            overlay.style.display = 'block';
        })
        .catch(error => console.error('Error fetching staff information:', error));
}

function calculateCommission(userId) {
    fetch('calc_commission.php?id=' + userId)
        .then(response => response.text())
        .then(data => {
            alert(data);
            fetchStaffInfo();
        })
        .catch(error => console.error('Error calculating commission:', error));
}

function resetCommission() {
    if (confirm('Are you sure you want to reset commissions for all staff members?')) {
        fetch('resetcommission.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    fetchStaffInfo();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error resetting commissions:', error));
    }
}

function removeStaff(userId) {
    if (confirm('Are you sure you want to remove this staff member?')) {
        fetch('removestaff.php?id=' + userId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    fetchStaffInfo();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error removing staff:', error));
    }
}

function fetchSuppliersInfo() {
    fetch('fetchsuppliersinfo.php')
        .then(response => response.json())
        .then(data => {
            var suppliersList = document.getElementById('suppliers-list');
            var listHTML = '<h2>Suppliers List</h2>';
            listHTML += '<ul>';
            data.forEach(supplier => {
                listHTML += '<li>' + supplier.supplier_name + '</li>';
            });
            listHTML += '</ul>';
            suppliersList.innerHTML = listHTML;
            togglePopup('suppliers-popup');
        })
        .catch(error => console.error('Error fetching suppliers information:', error));
}

document.getElementById('addSupplierForm').addEventListener('submit', function(event) {
    event.preventDefault();
    var formData = new FormData(this);
    fetch('recordsuppliers.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            fetchSuppliersInfo(); // Reload the suppliers list
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error('Error adding supplier:', error));
});

function addExpenseField() {
    var expensesList = document.getElementById('expensesList');
    var expenseField = document.createElement('div');
    expenseField.innerHTML = '<label for="expenseType">Expense Type:</label>' +
                             '<input type="text" id="expenseType" name="expenseType[]" required>' +
                             '<label for="amount">Amount:</label>' +
                             '<input type="number" id="amount" name="amount[]" step="0.01" required>' +
                             '<button type="button" onclick="removeExpenseField(this)">Remove</button>';
    expensesList.appendChild(expenseField);
}

function removeExpenseField(button) {
    button.parentNode.remove();
}

document.getElementById('expensesForm').addEventListener('submit', function(event) {
    event.preventDefault();
    var formData = new FormData(this);
    fetch('recordexpenses.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            // Optionally, you can close the popup or clear the form here
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error('Error recording expenses:', error));
});


function fetchProductFinance() {
    fetch('fetchproductfinace.php')
        .then(response => response.json())
        .then(data => {
            var productPricesTable = document.getElementById('productPricesTable');
            var tbody = productPricesTable.getElementsByTagName('tbody')[0];
            tbody.innerHTML = ''; // Clear existing rows
            
            data.forEach(product => {
                var row = document.createElement('tr');
                row.innerHTML = '<td>' + product.product_name + '</td>' +
                                '<td>' + product.category + '</td>' +
                                '<td>' + product.unit_price + '</td>' +
                                '<td><input type="number" class="sellingPrice" step="0.01" required></td>' +
                                '<td><input type="number" class="profit" readonly></td>' +
                                '<td><input type="number" class="percentageProfit" readonly></td>';
                tbody.appendChild(row);
            });
            
            togglePopup('prices-popup');
        })
        .catch(error => console.error('Error fetching product finance information:', error));
}

function calculateAllProfits() {
    var rows = document.querySelectorAll('#productPricesTable tbody tr');
    rows.forEach(row => {
        var sellingPrices = row.querySelectorAll('.sellingPrice');
        var buyingPrice = parseFloat(row.cells[2].textContent); // Buying price from the table cell
        var totalProfit = 0;
        var totalPercentageProfit = 0;

        sellingPrices.forEach(sellingPriceInput => {
            var sellingPrice = parseFloat(sellingPriceInput.value);
            var profit = sellingPrice - buyingPrice;
            var percentageProfit = (profit / buyingPrice) * 100;

            totalProfit += profit;
            totalPercentageProfit += percentageProfit;
        });

        row.querySelector('.profit').value = totalProfit.toFixed(2);
        row.querySelector('.percentageProfit').value = (totalPercentageProfit / sellingPrices.length).toFixed(2);
    });
}

function setAllPrices() {
    var rows = document.querySelectorAll('#productPricesTable tbody tr');
    var data = []; // Array to store product data

    rows.forEach(row => {
        var productName = row.cells[0].textContent;
        var category = row.cells[1].textContent;
        var buyingPrice = parseFloat(row.cells[2].textContent);
        var sellingPriceInput = row.querySelector('.sellingPrice');
        var sellingPrice = parseFloat(sellingPriceInput.value);
        var profitInput = row.querySelector('.profit');
        var profit = parseFloat(profitInput.value);
        var percentageProfitInput = row.querySelector('.percentageProfit');
        var percentageProfit = parseFloat(percentageProfitInput.value);
        var dynamicPricesCheckbox = document.getElementById('dynamicPrices');
        var dynamicPrices = dynamicPricesCheckbox.checked ? 1 : 0; // 1 if checked, 0 if not

        // Check if selling price, profit, and percentage profit are valid numbers
        if (!isNaN(sellingPrice) && !isNaN(profit) && !isNaN(percentageProfit)) {
            data.push({
                productName: productName,
                category: category,
                buyingPrice: buyingPrice,
                sellingPrice: sellingPrice,
                profit: profit,
                percentageProfit: percentageProfit,
                dynamicPrices: dynamicPrices
            });
        }
    });

    // Log the data to the console
    console.log('Data to be sent to server:', data);

    // Send data to server
    fetch('recordprices.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            closePopup();
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error('Error setting prices:', error));
}


document.getElementById('logoutLink').addEventListener('click', function(event) {
    event.preventDefault();
    window.location.href = 'logout.php';
});
</script>
</body>
</html>

