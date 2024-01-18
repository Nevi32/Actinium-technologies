<?php
// Include the configuration file
require_once 'config.php';

try {
    // Use the database configuration from the included file
    $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // If there is an error connecting to the database, terminate the script and display an error message
    die("Connection failed: " . $e->getMessage());
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

function generateUniqueID() {
    // Generate a unique numeric ID within the range of INT (adjust the range as needed)
    return rand(100000, 999999);
}

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $fullName = $_POST['full-name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phoneNumber = $_POST['phone-number'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm-password'];
    $storeName = $_POST['store-name'];
    $role = $_POST['role'];
    $gender = $_POST['gender'];

    // Check if any required fields are empty
    if (empty($fullName) || empty($username) || empty($email) || empty($phoneNumber) || empty($password) || empty($confirmPassword) || empty($storeName) || empty($role) || empty($gender)) {
        $response['message'] = "All fields are required.";
    } else {
        // Check if passwords match
        if ($password !== $confirmPassword) {
            $response['message'] = "Passwords do not match.";
        } else {
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $response['message'] = "Invalid email address.";
            } else {
                // Implement additional validation as needed
                // Hash the password
                $hashedPassword = hashPassword($password);

                // Check if the user is an owner and the store already exists    
                if ($role === 'owner') {
                    $checkStoreStmt = $pdo->prepare("SELECT * FROM stores WHERE store_name = ? AND location_name = ?");
                    $checkStoreStmt->execute([$storeName, $_POST['store-location']]);

                    if ($checkStoreStmt->rowCount() == 0) {
                        // If the store does not exist, generate a unique ID for the main store
                        $storeID = generateUniqueID();

                        // Insert the main store
                        $insertMainStoreStmt = $pdo->prepare("INSERT INTO stores (store_id, store_name, location_name, location_type) VALUES (?, ?, ?, 'main_store')");
                        $insertMainStoreStmt->execute([$storeID, $storeName, $_POST['store-location']]);
                    } else {
                        // If the store already exists, get the store ID
                        $storeData = $checkStoreStmt->fetch(PDO::FETCH_ASSOC);
                        $storeID = $storeData['store_id'];
                    }
                } else {
                    // For staff, find or insert the satellite store
                    $checkStoreStmt = $pdo->prepare("SELECT * FROM stores WHERE store_name = ? AND location_name = ?");
                    $checkStoreStmt->execute([$storeName, $_POST['store-location']]);

                    if ($checkStoreStmt->rowCount() == 0) {
                        // If the store does not exist, generate a unique ID for the satellite store
                        $storeID = generateUniqueID();

                        // Insert the satellite store
                        $insertSatelliteStoreStmt = $pdo->prepare("INSERT INTO stores (store_id, store_name, location_name, location_type) VALUES (?, ?, ?, 'satellite')");
                        $insertSatelliteStoreStmt->execute([$storeID, $storeName, $_POST['store-location']]);
                    } else {
                        // If the store already exists, get the store ID
                        $storeData = $checkStoreStmt->fetch(PDO::FETCH_ASSOC);
                        $storeID = $storeData['store_id'];
                    }
                }

                // Check if comp_staff is set and assign a value accordingly
                $compStaff = isset($_POST['comp-staff']) ? 1 : 0;

                // Prepare and execute the SQL query to insert user data into the users table
                $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, phone_number, password_hash, role, store_id, gender, comp_staff) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

                try {
                    // Insert user data into the users table
                    $stmt->execute([$fullName, $username, $email, $phoneNumber, $hashedPassword, $role, $storeID, $gender, $compStaff]);

                    $response['success'] = true;
                    $response['message'] = "Registration successful!";
                } catch (PDOException $e) {  // Log the error details (in a production environment, do not expose details to the user)
                    error_log("Error: " . $e->getMessage(), 0);
                    $response['message'] = "An error occurred. Please try again later.";
                }
            }
        }
    }
}

// Return the response as JSON
echo json_encode($response);
?>

