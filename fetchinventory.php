<?php
// Start the session
session_start();

// Include the configuration file
include 'config.php';

try {
    // Create a PDO connection using the configuration from config.php
    $pdo = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}", $databaseConfig['user'], $databaseConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Retrieve store information from query parameters
    $storeName = $_GET['storeName'] ?? '';
    $locationName = $_GET['locationName'] ?? '';

    if (empty($storeName) || empty($locationName)) {
        // If store name or location is not set, handle accordingly (e.g., show an error message)
        echo "Error: Store name or location not set.";
        exit();
    }

    // Check if the store is a main store or satellite
    $storeTypeQuery = "SELECT location_type FROM stores WHERE store_name = ? AND location_name = ?";
    $storeTypeStmt = $pdo->prepare($storeTypeQuery);
    $storeTypeStmt->execute([$storeName, $locationName]);
    $storeType = $storeTypeStmt->fetchColumn();

    if ($storeType === 'main_store') {
        // If it's a main store, fetch main entry and individual inventory data with main_entry_id
        $mainEntryQuery = "SELECT me.main_entry_id, me.product_name, me.category, me.total_quantity, me.quantity_description, me.store_id, MAX(i.record_date) as record_date
                           FROM main_entry me
                           LEFT JOIN inventory i ON me.main_entry_id = i.main_entry_id
                           WHERE me.store_id = (SELECT store_id FROM stores WHERE store_name = ? AND location_name = ?)
                           GROUP BY me.main_entry_id, me.product_name, me.category, me.total_quantity, me.quantity_description, me.store_id";

        $mainEntryStmt = $pdo->prepare($mainEntryQuery);
        $mainEntryStmt->execute([$storeName, $locationName]);
        $mainEntryData = $mainEntryStmt->fetchAll(PDO::FETCH_ASSOC);

        // Set session variables for main store data
        $_SESSION['storeType'] = 'main_store';
        $_SESSION['mainstoreData'] = $mainEntryData;
        $_SESSION['store_id'] = $mainEntryData[0]['store_id']; // Assuming all main entries have the same store_id
        $_SESSION['main_entry_id'] = $mainEntryData[0]['main_entry_id']; // Assuming all main entries have the same main_entry_id

        // Fetch and session individual entry data for the main store
        $individualEntryQuery = "SELECT i.entry_id, i.quantity, i.quantity_description, i.price, i.record_date, i.sale_id, i.store_id, me.main_entry_id
                                 FROM inventory i
                                 LEFT JOIN main_entry me ON i.main_entry_id = me.main_entry_id
                                 WHERE i.store_id = ?";
        $individualEntryStmt = $pdo->prepare($individualEntryQuery);
        $individualEntryStmt->execute([$mainEntryData[0]['store_id']]);
        $_SESSION['mainstore_individual_entry_data'] = $individualEntryStmt->fetchAll(PDO::FETCH_ASSOC);

        // Check if there are satellite stores associated with the main store
        $satelliteStoreQuery = "SELECT store_id, location_name
                                FROM stores
                                WHERE store_name = ? AND location_type = 'satellite'";
        $satelliteStoreStmt = $pdo->prepare($satelliteStoreQuery);
        $satelliteStoreStmt->execute([$storeName]);
        $satelliteStoreData = $satelliteStoreStmt->fetchAll(PDO::FETCH_ASSOC);

        $_SESSION['satelliteData'] = [];
        $_SESSION['satellite_individual_entry_data'] = [];

        foreach ($satelliteStoreData as $satelliteStore) {
            // Fetch main entry and individual inventory data for each satellite store
            $satelliteEntryQuery = "SELECT me.main_entry_id, me.product_name, me.category, me.total_quantity, me.quantity_description, me.store_id, MAX(i.record_date) as record_date
                                    FROM main_entry me
                                    LEFT JOIN inventory i ON me.main_entry_id = i.main_entry_id
                                    WHERE me.store_id = ?
                                    GROUP BY me.main_entry_id, me.product_name, me.category, me.total_quantity, me.quantity_description, me.store_id";

            $satelliteEntryStmt = $pdo->prepare($satelliteEntryQuery);
            $satelliteEntryStmt->execute([$satelliteStore['store_id']]);
            $satelliteEntryData = $satelliteEntryStmt->fetchAll(PDO::FETCH_ASSOC);

            // Set session variables for satellite store data
            $_SESSION['satelliteData'][] = [
                'store_id' => $satelliteStore['store_id'],
                'location_name' => $satelliteStore['location_name'],
                'main_entry_data' => $satelliteEntryData,
            ];

            // Fetch and session individual entry data for the satellite store
            $satelliteIndividualEntryQuery = "SELECT i.entry_id, i.quantity, i.quantity_description, i.price, i.record_date, i.sale_id, i.store_id, me.main_entry_id
                                              FROM inventory i
                                              LEFT JOIN main_entry me ON i.main_entry_id = me.main_entry_id
                                              WHERE i.store_id = ? AND i.main_entry_id = ?";
            $satelliteIndividualEntryStmt = $pdo->prepare($satelliteIndividualEntryQuery);

            foreach ($satelliteEntryData as $satelliteEntry) {
                $satelliteIndividualEntryStmt->execute([$satelliteStore['store_id'], $satelliteEntry['main_entry_id']]);
                $_SESSION['satellite_individual_entry_data'][] = $satelliteIndividualEntryStmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
    } else {
        echo "Error: Unknown store type.";
        exit();
    }

    // Output the data as JSON (for testing purposes)
    echo json_encode([
        'mainstore_data' => $_SESSION['mainstoreData'],
        'mainstore_individual_data' => $_SESSION['mainstore_individual_entry_data'],
        'satellite_data' => $_SESSION['satelliteData'],
        'satellite_individual_data' => $_SESSION['satellite_individual_entry_data']
    ]);

    // Redirect to viewinventory.php if needed
     header("Location: viewinventory.php");
     exit();
} catch (PDOException $e) {
    // Handle database errors
    echo "Error fetching inventory data: " . $e->getMessage();
}
?>

