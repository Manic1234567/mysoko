<?php
require_once 'db.php';

// Destroy all session data
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Also delete from user_sessions table if using database sessions
if (isset($_COOKIE[session_name()])) {
    try {
        $session_id = $_COOKIE[session_name()];
        $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE session_id = ?");
        $stmt->execute([$session_id]);
    } catch (PDOException $e) {
        // Log error but don't prevent logout
        error_log("Error deleting session from database: " . $e->getMessage());
    }
}

// Redirect to login page
header("Location: login.php");
exit;
?>