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
        <a href="home.php" onclick="redirectToPage('home.php');"><i class="fas fa-home"></i> Dashboard</a>
        <a href="#" id="logoutLink" onclick="logout();"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    <div id="content">
        <div class="welcome-message" id="welcome-message">
            <?php echo "Welcome to the " . $_SESSION['store_name'] . " System"; ?>
        </div>
        <div class="row">
            <div class="card">
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
<script>
    function toggleUserInfo() {
        var userInfo = document.getElementById('user-info');
        userInfo.style.display = (userInfo.style.display === 'none') ? 'block' : 'none';
    }

    function redirectToPage(page) {
        // Redirect to the specified page without passing user info in the URL
        window.location.href = page;
    }

    document.getElementById('logoutLink').addEventListener('click', function(event) {
        // Prevent the default behavior of the link
        event.preventDefault();

        // Redirect the user to the logout.php file for logout
        window.location.href = 'logout.php';
    });
</script>
</body>
</html>

