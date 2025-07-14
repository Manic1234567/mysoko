<?php
header('Content-Type: application/json');
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    if ($action === 'create_post') {
        // Log received data for debugging
        file_put_contents('debug.log', print_r($_POST, true) . print_r($_FILES, true), FILE_APPEND);
        
        // Simulate success for testing
        echo json_encode([
            'success' => true,
            'message' => 'Test mode - data received',
            'received_data' => $_POST,
            'files' => $_FILES
        ]);
    } else {
        throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>