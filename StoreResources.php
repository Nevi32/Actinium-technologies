<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Resources Management</title>
    <link rel="stylesheet" href="SRstyles.css">
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
        <!-- Sidebar content -->
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

<!-- Prices Popup -->
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

function toggleDynamicPrices() {
    var dynamicPricesCheckbox = document.getElementById('dynamicPrices');
    var sellingPriceCells = document.querySelectorAll('.sellingPrice');
    sellingPriceCells.forEach(function(cell) {
        if (dynamicPricesCheckbox.checked) {
            cell.setAttribute('type', 'number');
        } else {
            cell.setAttribute('type', 'text');
        }
    });
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
        var sellingPrice = parseFloat(row.querySelector('.sellingPrice').value);
        var buyingPrice = parseFloat(row.cells[2].textContent); // Buying price from the table cell
        var profit = sellingPrice - buyingPrice;
        var percentageProfit = (profit / buyingPrice) * 100;

        row.querySelector('.profit').value = profit.toFixed(2);
        row.querySelector('.percentageProfit').value = percentageProfit.toFixed(2);
    });
}

function setAllPrices() {
    var formData = new FormData();
    var rows = document.querySelectorAll('#productPricesTable tbody tr');
    rows.forEach(row => {
        var productName = row.cells[0].textContent;
        var sellingPrice = row.querySelector('.sellingPrice').value;
        formData.append(productName + '_sellingPrice', sellingPrice);
    });

    fetch('recordprices.php', {
        method: 'POST',
        body: formData
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

