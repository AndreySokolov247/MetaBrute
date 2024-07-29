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
    // Prepare the SQL query to fetch the task
    $fetchSql = "SELECT id, data, iv, salt, Iterations, passwords, brute 
                 FROM brute 
                 WHERE brute = 1 
                 ORDER BY id ASC 
                 LIMIT 1";

    try {
        $stmt = $pdo->prepare($fetchSql);
        $stmt->execute();

        // Fetch the first record
        $task = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($task) {
            // Prepare the SQL query to update the brute column
            $updateSql = "UPDATE brute 
                          SET brute = 0 
                          WHERE id = :id";

            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute(['id' => $task['id']]);

            // Construct the JSON response with the id included
            $response = [
                'id' => $task['id'], // Include task ID
                'data' => $task['data'], // Assuming data is base64 encoded
                'iv' => $task['iv'], // Assuming iv is base64 encoded
                'salt' => $task['salt'], // Assuming salt is base64 encoded
                'iterations' => (int)$task['Iterations'], // Convert to integer
                'passwords' => $task['passwords'] // Passwords are already separated by '|'
            ];

            // Return the result as JSON
            echo json_encode($response);
        } else {
            // No record found
            echo json_encode(['error' => 'No task found with brute = 1']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}
?>
