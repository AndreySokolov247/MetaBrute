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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Prepare the SQL query to fetch records where result != 1
        $fetchSql = "SELECT id, result FROM brute WHERE result != 1";

        $stmt = $pdo->prepare($fetchSql);
        $stmt->execute();

        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($tasks)) {
            // Find the first record that does not have result = 1
            $task = $tasks[0];
            $id = $task['id'];
            $resultValue = $task['result'];

            // Output the result value
            echo $resultValue;

            // Update the result column to 1 for the processed record
            $updateSql = "UPDATE brute SET result = 1 WHERE id = :id";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute(['id' => $id]);
        } else {
            // No records with result != 1
            echo ''; // Return an empty response
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}
?>
