<!DOCTYPE html>
<html>
<head>
    <title>Notification Example</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
        }

        button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <?php
    // Database configuration
    $databaseConfig = [
        'host' => 'localhost',
        'dbname' => 'StoreY11',
        'user' => 'nevill',
        'password' => '7683Nev!//'
    ];

    // Connect to the database
    try {
        $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Could not connect to the database: " . $e->getMessage());
    }

    // Fetch notifications where is_sent status is false
    $notifications = [];
    try {
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE is_sent = 0 ORDER BY timestamp DESC");
        $stmt->execute();
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error fetching notifications: " . $e->getMessage());
    }
    ?>

    <button onclick="requestNotificationPermission();">Enable Notifications</button>

    <script>
        // Pass PHP notifications array to JavaScript
        const notifications = <?php echo json_encode($notifications); ?>;

        function showNotification(title, message) {
            if (!('Notification' in window)) {
                alert('This browser does not support desktop notifications.');
                return;
            }

            if (Notification.permission === 'granted') {
                new Notification(title, {
                    body: message,
                    icon: '/path/to/icon.png' // Optional: you can add an icon path here
                });
            }
        }

        function requestNotificationPermission() {
            Notification.requestPermission().then(function(permission) {
                if (permission === 'granted') {
                    notifications.forEach(notification => {
                        showNotification(notification.subject + " 📢", notification.message);
                        // Mark notification as sent in the database
                        markNotificationAsSent(notification.notification_id);
                    });
                }
            });
        }

        function markNotificationAsSent(notificationId) {
            // Send an AJAX request to mark notification as sent
            fetch('mark_notification_sent.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ notificationId: notificationId })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to mark notification as sent');
                }
                return response.json();
            })
            .then(data => {
                console.log('Notification marked as sent:', data);
            })
            .catch(error => {
                console.error('Error marking notification as sent:', error);
            });
        }
    </script>
</body>
</html>

