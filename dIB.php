<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'sokoni_db');
define('DB_USER', 'root'); // Change to your database username
define('DB_PASS', ''); // Change to your database password

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>