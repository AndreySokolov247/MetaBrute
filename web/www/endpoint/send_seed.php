<?php
// Database configuration
$host = 'db'; // Change this if your database is on a different host
$dbname = 'metabrute'; // Replace with your database name
$username = 'metabrute'; // Replace with your database username
$password = 'password'; // Replace with your database password

// Create a new PDO instance
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Check for POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capture the POST data
    $id = $_POST['id'] ?? null;
    $result = $_POST['result'] ?? null;

    // Validate the input
    if ($id === null || $result === null) {
        echo json_encode(['error' => 'Missing id or result']);
        exit;
    }

    try {
        // Prepare the SQL query to update the result column
        $updateSql = "UPDATE brute SET result = :result WHERE id = :id";

        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute(['id' => $id, 'result' => $result]);

        // Check if the update was successful
        if ($updateStmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Result updated successfully']);
        } else {
            echo json_encode(['error' => 'No task found with the provided id']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}
?>
