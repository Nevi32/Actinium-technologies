<?php
include('config.php');

try {
    $dsn = 'mysql:host=' . $databaseConfig['host'] . ';dbname=' . $databaseConfig['dbname'];
    $pdo = new PDO($dsn, $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = 'SELECT user_id, full_name FROM users WHERE role = "staff"';
    $stmt = $pdo->query($sql);
    $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $_SESSION['staff_info'] = json_encode($staff);

    header('Content-Type: application/json');
    echo $_SESSION['staff_info'];
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error fetching staff information: ' . $e->getMessage()]);
}
?>

