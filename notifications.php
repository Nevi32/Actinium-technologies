<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="styles.css"> <!-- Include your stylesheet if not already included -->
</head>
<style>

/* Reset some default styles */
body,
h1,
h2,
p {
    margin: 0;
    padding: 0;
}

/* Apply a background color to the body */
body {
    background-color: #f4f4f4;
    font-family: 'Arial', sans-serif;
}

/* Style the dashboard container */
#dashboard {
    display: flex;
}

/* Style the sidebar */
#sidebar {
    width: 200px;
    height: 100vh; /* Set the height to 100% of the viewport height */
    background-color: #333;
    color: #fff;
    padding: 20px;
}

#sidebar a {
    display: block;
    color: #fff;
    text-decoration: none;
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 5px;
    transition: background-color 0.3s;
}

#sidebar a:hover {
    background-color: #555;
}

/* Style the content area */
#content {
    flex-grow: 1;
    padding: 20px;
}

/* Style the notification container */
#notification-container {
    background-color: #fff;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

#notifications .notification-item {
    border-bottom: 1px solid #ddd;
    margin-bottom: 10px;
    padding-bottom: 10px;
}

/* Style the notification options */
#notification-options {
    background-color: #fff;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

/* Style the Save Preferences button */
#save-preferences {
    background-color: #4caf50;
    color: #fff;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

#save-preferences:hover {
    background-color: #45a049;
}

</style>
<body>
    <div id="dashboard">
        <div id="sidebar">
            <a href="fetchsales.php"><i class="fas fa-chart-line"></i> View Sales</a>
            <a href="notifications.html"><i class="fas fa-bell"></i> Notifications</a>
            <!-- Add other sidebar links as needed -->
        </div>

        <div id="content">
            <div id="notification-container">
                <h2>Notifications</h2>
                <div id="notifications">
                    <?php
                    session_start();

                    // Check if the session variable is set
                    if (isset($_SESSION['latestNotifications'])) {
                        // Access the data
                        $latestNotifications = $_SESSION['latestNotifications'];

                        // Display the notifications
                        foreach ($latestNotifications as $notification) {
                            echo "<div class='notification-item'>";
                            echo "<strong>Subject:</strong> " . $notification['subject'] . "<br>";
                            echo "<strong>Message:</strong> " . $notification['message'] . "<br>";
                            echo "</div>";
                        }

                        // Clear the session variable if needed
                        unset($_SESSION['latestNotifications']);
                    } else {
                        echo "<p>No notifications available.</p>";
                    }
                    ?>
                </div>
            </div>

            <div id="notification-options">
                <h2>Notification Preferences</h2>
                <label for="email-checkbox">
                    <input type="checkbox" id="email-checkbox"> Receive Email Notifications
                </label>
                <br>
                <label for="whatsapp-checkbox">
                    <input type="checkbox" id="whatsapp-checkbox"> Receive WhatsApp Notifications
                </label>
                <br>
                <button id="save-preferences" onclick="savePreferences()">Save Preferences</button>
            </div>
        </div>
    </div>
</body>

</html>

