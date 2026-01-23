<?php
// config.php - Database configuration file for MAMP

// Database credentials for MAMP
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');  // MAMP default password is 'root'
define('DB_NAME', 'commercial-pool');

// Create database connection
function getDbConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        die(json_encode([
            'success' => false,
            'error' => 'Database connection failed: ' . $conn->connect_error
        ]));
    }
    
    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

// Close database connection
function closeDbConnection($conn) {
    if ($conn) {
        $conn->close();
    }
}
?>