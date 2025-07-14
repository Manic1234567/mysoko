<?php
require_once 'db.php';

function sendMessage($senderId, $receiverId, $productId, $message) {
    global $pdo;
    
    try {
        // Insert message
        $stmt = $pdo->prepare("INSERT INTO messages 
                              (product_id, sender_id, receiver_id, message) 
                              VALUES (?, ?, ?, ?)");
        $stmt->execute([$productId, $senderId, $receiverId, $message]);
        
        // Update or create conversation
        $userId1 = min($senderId, $receiverId);
        $userId2 = max($senderId, $receiverId);
        
        $stmt = $pdo->prepare("INSERT INTO conversations 
                              (user1_id, user2_id, product_id, last_message_at) 
                              VALUES (?, ?, ?, NOW())
                              ON DUPLICATE KEY UPDATE last_message_at = NOW()");
        $stmt->execute([$userId1, $userId2, $productId]);
        
        return true;
    } catch (PDOException $e) {
        error_log("Message send error: " . $e->getMessage());
        return false;
    }
}

function getMessages($userId, $otherUserId, $productId, $limit = 100) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM messages 
                              WHERE ((sender_id = ? AND receiver_id = ?) OR 
                                    (sender_id = ? AND receiver_id = ?))
                              AND product_id = ?
                              ORDER BY created_at DESC
                              LIMIT ?");
        $stmt->execute([$userId, $otherUserId, $otherUserId, $userId, $productId, $limit]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Mark messages as read
        $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 
                              WHERE receiver_id = ? AND sender_id = ? 
                              AND product_id = ? AND is_read = 0");
        $stmt->execute([$userId, $otherUserId, $productId]);
        
        return array_reverse($messages); // Return in chronological order
    } catch (PDOException $e) {
        error_log("Message fetch error: " . $e->getMessage());
        return [];
    }
}

function getConversations($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT c.*, 
                              u.username as other_username,
                              p.name as product_name,
                              (SELECT COUNT(*) FROM messages m 
                               WHERE ((m.sender_id = c.user1_id AND m.receiver_id = c.user2_id) OR 
                                     (m.sender_id = c.user2_id AND m.receiver_id = c.user1_id))
                               AND m.product_id = c.product_id
                               AND m.receiver_id = ? AND m.is_read = 0) as unread_count
                              FROM conversations c
                              JOIN users u ON (u.id = CASE WHEN c.user1_id = ? THEN c.user2_id ELSE c.user1_id END)
                              LEFT JOIN products p ON c.product_id = p.id
                              WHERE c.user1_id = ? OR c.user2_id = ?
                              ORDER BY c.last_message_at DESC");
        $stmt->execute([$userId, $userId, $userId, $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Conversations fetch error: " . $e->getMessage());
        return [];
    }
}
?>