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
            <div class="card">
                <h3>Manage Suppliers</h3>
                <p>Manage your store's suppliers and vendors.</p>
            </div>
        </div>
        <div class="row">
            <div class="card">
                <h3>Expenses</h3>
                <p>Track and manage your store's expenses.</p>
            </div>
            <div class="card">
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
        <!-- Suppliers details here -->
    </div>
</div>

<div id="expenses-popup" class="popup">
    <div class="popup-content">
        <!-- Expenses details here -->
    </div>
</div>

<div id="prices-popup" class="popup">
    <div class="popup-content">
        <!-- Prices details here -->
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

function toggleUserInfo() {
    var userInfo = document.getElementById('user-info');
    userInfo.style.display = (userInfo.style.display === 'none') ? 'block' : 'none';
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

function redirectToPage(page) {
    window.location.href = page;
}

document.getElementById('logoutLink').addEventListener('click', function(event) {
    event.preventDefault();
    window.location.href = 'logout.php';
});

</script>
</body>
</html>

