<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login.html if the session is not active
    header("Location: login.html");
    exit();
}

// Check the user's role and redirect accordingly
if ($_SESSION['role'] === 'owner') {
    // If the user is an owner, redirect to inventory.php
    header("Location: inventory.php");
} elseif ($_SESSION['role'] === 'staff' && $_SESSION['comp_staff'] == 1) {
    // If the user is staff, redirect to inventory2.php
    header("Location: inventory2.php");
} else {
    // Handle other cases or roles (if needed)
    // For example, redirect to a generic page or display an error message
    header("Location: generic_page.php");
}

exit();
?>

