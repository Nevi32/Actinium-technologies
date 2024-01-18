<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $rememberMe = isset($_POST['remember-me']);

    if (empty($username) || empty($password)) {
        header("Location: login.html?message=Username%20and%20password%20are%20required.&type=error");
        exit();
    }

    try {
        $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Perform a LEFT JOIN to retrieve store information for the user
        $stmt = $pdo->prepare("SELECT u.user_id, u.username, u.role, u.store_id, s.store_name, s.location_name, u.comp_staff, u.password_hash, u.remember_token FROM users u LEFT JOIN stores s ON u.store_id = s.store_id WHERE u.username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Check if the user is allowed to login based on their role
            if ($user['role'] === 'owner' || ($user['role'] === 'staff' && $user['comp_staff'] == 1)) {
                // Start the session
                session_start();

                // Store user information in session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['comp_staff'] = $user['comp_staff'];

                // If the user is an owner or comp staff, store additional information in session
                if ($user['role'] === 'owner' || $user['comp_staff'] == 1) {
                    $_SESSION['store_name'] = $user['store_name'];
                    $_SESSION['location_name'] = $user['location_name'];
                }

                // Redirect to home.html
                header("Location: home.php");
                exit();
            } else {
                header("Location: login.html?message=Access%20denied.%20Only%20comp%20staff%20can%20log%20in.&type=error");
                exit();
            }
        } else {
            header("Location: login.html?message=Invalid%20username%20or%20password.&type=error");
            exit();
        }
    } catch (PDOException $e) {
        // Log the error details
        error_log("Database Error: " . $e->getMessage(), 0);
        header("Location: login.html?message=An%20error%20occurred.%20Please%20try%20again%20later.&type=error");
        exit();
    }
}
?>

