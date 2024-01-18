<?php
require_once('config.php');

// Establish a database connection
try {
    $dsn = 'mysql:host=' . $databaseConfig['host'] . ';dbname=' . $databaseConfig['dbname'];
    $pdo = new PDO($dsn, $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Query to retrieve the latest notification for each type
$query = "SELECT MAX(notification_id) AS max_id, subject, MAX(message) AS message FROM notifications GROUP BY subject";
$stmt = $pdo->prepare($query);
$stmt->execute();
$latestNotifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Start a session and store the data in a session variable
session_start();
$_SESSION['latestNotifications'] = $latestNotifications;

// Close the database connection
$pdo = null;

// Redirect to notifications.php
header('Location: notifications.php');
exit();
?>

