<?php
// db.php

$host = 'localhost';
$dbname = 'sokoni_market';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Database connection successful!"; // You can keep or remove this line
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Start session if not already started
// It's generally better to start session at the very beginning of the script that needs it (e.g., market.php, chat.php)
// but if you keep it here, ensure db.php is always included first.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// --- ADD THESE FUNCTIONS BELOW YOUR EXISTING CODE ---

function getTotalUnreadMessages($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}

function getTotalUnreadNotifications($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}

?>