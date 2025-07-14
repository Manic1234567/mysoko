<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db.php';

$current_user_id = $_SESSION['user_id'];
$message_partner_id = null;
$product_id = null;
$message_partner_username = 'Unknown';
$product_name = 'N/A';
$product_seller_id = null;
$error_message = ''; // For displaying immediate errors

// Determine who the message partner is and what product the chat is about
if (isset($_GET['seller_id'])) {
    $message_partner_id = (int)$_GET['seller_id'];
    if ($message_partner_id === $current_user_id) {
        $_SESSION['error_message'] = "You cannot chat with yourself.";
        header('Location: market.php');
        exit;
    }
}

if (isset($_GET['product_id'])) {
    $product_id = (int)$_GET['product_id'];

    $stmt = $pdo->prepare("SELECT name, user_id FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product_info) {
        $product_name = $product_info['name'];
        $product_seller_id = $product_info['user_id'];

        if (is_null($message_partner_id)) {
            $message_partner_id = $product_seller_id;
        } elseif ($message_partner_id !== $product_seller_id && $message_partner_id !== $current_user_id) {
            $_SESSION['error_message'] = "Invalid chat parameters for this product.";
            header('Location: market.php');
            exit;
        }
    } else {
        $_SESSION['error_message'] = "Product not found.";
        header('Location: market.php');
        exit;
    }
}

if (is_null($message_partner_id)) {
    $_SESSION['error_message'] = "No chat partner specified.";
    header('Location: market.php');
    exit;
}

// Get message partner's username
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$message_partner_id]);
$partner_user = $stmt->fetch(PDO::FETCH_ASSOC);
if ($partner_user) {
    $message_partner_username = $partner_user['username'];
} else {
    $_SESSION['error_message'] = "Chat partner not found.";
    header('Location: market.php');
    exit;
}

// Handle sending messages (text and/or image)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['message_text']) || isset($_FILES['message_image']))) {
    $message_text = trim($_POST['message_text']);
    $image_url = null;

    // Handle image upload
    if (isset($_FILES['message_image']) && $_FILES['message_image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/messages/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $imageFileType = strtolower(pathinfo($_FILES['message_image']['name'], PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowed_types)) {
            $error_message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed for images.";
        } else {
            $unique_filename = uniqid('msg_img_') . '.' . $imageFileType;
            $target_file = $target_dir . $unique_filename;

            if (move_uploaded_file($_FILES['message_image']['tmp_name'], $target_file)) {
                $image_url = $target_file;
            } else {
                $error_message = "Sorry, there was an error uploading your image.";
            }
        }
    }

    if (!empty($message_text) || !is_null($image_url)) {
        if (empty($error_message)) { // Proceed only if no file upload error
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, product_id, message_text, image_url)
                                      VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$current_user_id, $message_partner_id, $product_id, $message_text, $image_url]);

                // Create a notification for the receiver
                $notification_message = "New message from " . htmlspecialchars($username);
                if ($product_id) {
                    $notification_message .= " regarding product: " . htmlspecialchars($product_name);
                }
                $notification_type = 'new_message';
                $notification_details = json_encode([
                    'sender_id' => $current_user_id,
                    'sender_username' => $username,
                    'chat_link' => "chat.php?seller_id={$current_user_id}" . ($product_id ? "&product_id={$product_id}" : "")
                ]);

                // Check if a similar unread 'new_message' notification from this sender already exists for the receiver
                $stmtCheckNotif = $pdo->prepare("SELECT id FROM notifications
                                                WHERE user_id = ?
                                                AND type = 'new_message'
                                                AND details LIKE ?
                                                AND is_read = 0
                                                LIMIT 1");
                $likePattern = '%"sender_id":' . $current_user_id . '%';
                $stmtCheckNotif->execute([$message_partner_id, $likePattern]);
                $existingNotif = $stmtCheckNotif->fetchColumn();

                if (!$existingNotif) { // Only create a new notification if no unread one from this sender exists
                    $stmtNotif = $pdo->prepare("INSERT INTO notifications (user_id, message, type, details, is_read)
                                               VALUES (?, ?, ?, ?, 0)");
                    $stmtNotif->execute([$message_partner_id, $notification_message, $notification_type, $notification_details]);
                }

                $pdo->commit();
                // Redirect to clear POST data and prevent resubmission
                header("Location: chat.php?seller_id={$message_partner_id}" . ($product_id ? "&product_id={$product_id}" : ""));
                exit;
            } catch (PDOException $e) {
                $pdo->rollBack();
                error_log("Failed to send message or create notification: " . $e->getMessage());
                $error_message = "Failed to send message. Please try again. " . $e->getMessage();
            }
        }
    } else {
        $error_message = "Message text or image is required.";
    }
}

// Handle deleting messages
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_message_id'])) {
    $message_id_to_delete = (int)$_POST['delete_message_id'];

    try {
        // Ensure the user is the sender of the message before deleting
        $stmt = $pdo->prepare("SELECT sender_id, image_url FROM messages WHERE id = ?");
        $stmt->execute([$message_id_to_delete]);
        $message_info = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($message_info && $message_info['sender_id'] === $current_user_id) {
            $pdo->beginTransaction();

            // If there's an image, delete the file from the server
            if (!empty($message_info['image_url']) && file_exists($message_info['image_url'])) {
                unlink($message_info['image_url']);
            }

            $stmtDelete = $pdo->prepare("DELETE FROM messages WHERE id = ? AND sender_id = ?");
            $stmtDelete->execute([$message_id_to_delete, $current_user_id]);

            $pdo->commit();
            $_SESSION['success_message'] = "Message deleted.";
        } else {
            $error_message = "You are not authorized to delete this message.";
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Failed to delete message: " . $e->getMessage());
        $error_message = "Failed to delete message. " . $e->getMessage();
    }
    header("Location: chat.php?seller_id={$message_partner_id}" . ($product_id ? "&product_id={$product_id}" : ""));
    exit;
}


// Fetch messages for the conversation
$query = "SELECT m.*, s.username AS sender_username, r.username AS receiver_username
          FROM messages m
          JOIN users s ON m.sender_id = s.id
          JOIN users r ON m.receiver_id = r.id
          WHERE ((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?))";

$params = [$current_user_id, $message_partner_id, $message_partner_id, $current_user_id];

if (!is_null($product_id)) {
    $query .= " AND m.product_id = ?";
    $params[] = $product_id;
} else {
    $query .= " AND m.product_id IS NULL"; // General chat without product context
}

$query .= " ORDER BY m.sent_at ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mark fetched messages as read for the current user (if they are the receiver)
if (!empty($messages)) {
    $stmt = $pdo->prepare("UPDATE messages SET is_read = TRUE
                          WHERE receiver_id = ?
                          AND sender_id = ?
                          AND is_read = FALSE");
    $stmt->execute([$current_user_id, $message_partner_id]);
}

// In chat.php, inside the POST handler for sending messages:
// ...
            $notification_type = 'new_message'; // <--- This assumes a 'type' column exists
// ...
            $stmtCheckNotif = $pdo->prepare("SELECT id FROM notifications
                                            WHERE user_id = ?
                                            AND type = 'new_message'  // <--- This is the WHERE clause using 'type'
                                            AND details LIKE ?
                                            AND is_read = 0
                                            LIMIT 1");
// ...
            $stmtNotif = $pdo->prepare("INSERT INTO notifications (user_id, message, type, details, is_read)
                                           VALUES (?, ?, ?, ?, 0)"); // <--- This inserts into 'type'
// ...
$username = $_SESSION['username'] ?? 'User';
// totalUnreadMessages in header handled by db.php function
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with <?= htmlspecialchars($message_partner_username) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6C5CE7;
            --primary-light: #A29BFE;
            --primary-dark: #4834D4;
            --light: #F8F9FA;
            --dark: #2D3436;
            --dark-light: #636E72;
            --success: #00B894;
            --danger: #ff4757;

            --gradient-primary: linear-gradient(135deg, var(--primary), var(--primary-dark));

            --border-radius: 16px;
            --border-radius-sm: 8px;
            --shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            --shadow-sm: 0 4px 15px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 15px 40px rgba(0, 0, 0, 0.15);
            --transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #F5F6FA;
            color: var(--dark);
            line-height: 1.6;
        }

        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            background: white;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .logo-icon {
            width: 36px;
            height: 36px;
            background: var(--gradient-primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .logo-text {
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 1.5rem;
            background: linear-gradient(to right, var(--primary), var(--primary-dark));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .header-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary-light);
            color: var(--primary);
            border: none;
            cursor: pointer;
            transition: var(--transition);
        }

        .header-btn:hover {
            background-color: var(--primary);
            color: white;
            transform: translateY(-3px);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            cursor: pointer;
        }

        /* Chat Container */
        .chat-container {
            display: flex;
            flex-direction: column;
            max-width: 800px;
            height: calc(100vh - 120px); /* Adjust height for header and input */
            margin: 80px auto 20px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .chat-header {
            padding: 1rem 1.5rem;
            background: var(--gradient-primary);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: 'Montserrat', sans-serif;
        }

        .chat-header h3 {
            font-size: 1.4rem;
            margin: 0;
        }

        .chat-header span {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .messages-box {
            flex-grow: 1;
            padding: 1.5rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            scroll-behavior: smooth;
        }

        .message-wrapper {
            display: flex;
            align-items: flex-end; /* Align time/delete icon at bottom of bubble */
        }

        .message {
            max-width: 70%;
            padding: 0.8rem 1.2rem;
            border-radius: var(--border-radius-sm);
            line-height: 1.4;
            word-wrap: break-word;
            position: relative; /* For delete button positioning */
        }

        .message.sent {
            align-self: flex-end;
            background-color: var(--primary-light);
            color: white;
            border-bottom-right-radius: 0;
            margin-left: auto; /* Push to right */
        }

        .message.received {
            align-self: flex-start;
            background-color: #e2e2e2;
            color: var(--dark);
            border-bottom-left-radius: 0;
            margin-right: auto; /* Push to left */
        }

        .message-time {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.7); /* Lighter color for sent messages */
            margin-top: 5px;
            text-align: right;
            display: block;
        }
        .message.received .message-time {
            color: #666; /* Darker color for received messages */
            text-align: left;
        }

        .message-image {
            max-width: 100%;
            height: auto;
            border-radius: var(--border-radius-sm);
            margin-bottom: 8px; /* Space between image and text */
        }

        .chat-input-form {
            padding: 1rem 1.5rem;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
            align-items: center; /* Align items vertically in the middle */
        }

        .chat-input {
            flex-grow: 1;
            padding: 0.8rem 1.2rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius-sm);
            font-size: 1rem;
            outline: none;
            transition: var(--transition);
        }

        .chat-input:focus {
            border-color: var(--primary);
        }

        .send-btn {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 0.8rem 1.2rem;
            border-radius: var(--border-radius-sm);
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            flex-shrink: 0; /* Prevent shrinking */
        }

        .send-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .empty-chat {
            text-align: center;
            padding: 3rem;
            color: var(--dark-light);
            font-style: italic;
        }

        /* Success/Error messages */
        .success-message, .error-message {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 15px 25px;
            color: white;
            border-radius: var(--border-radius-sm);
            box-shadow: var(--shadow);
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .success-message { background: var(--success); }
        .error-message { background: var(--danger); }

        /* File upload button styling */
        .file-upload-label {
            background-color: #6c757d; /* A bit darker grey */
            color: white;
            padding: 0.8rem 1.2rem;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            transition: background-color 0.3s;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .file-upload-label:hover {
            background-color: #5a6268;
        }
        .file-upload-input {
            display: none; /* Hide the default file input */
        }

        /* Delete button for messages */
        .delete-message-btn {
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            cursor: pointer;
            margin-left: 10px;
            transition: color 0.2s;
            position: absolute;
            top: 5px; /* Adjust position */
            right: 5px;
            padding: 2px;
            line-height: 1; /* For better icon alignment */
        }
        .message.received .delete-message-btn {
             color: rgba(0, 0, 0, 0.4);
        }
        .delete-message-btn:hover {
            color: white;
        }
        .message.received .delete-message-btn:hover {
            color: var(--danger); /* Make it red on hover for received */
        }
        /* Make delete button visible only on hover of message for better UX */
        .message-wrapper .delete-message-btn {
            visibility: hidden;
            opacity: 0;
        }
        .message-wrapper:hover .delete-message-btn {
            visibility: visible;
            opacity: 1;
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="market.php" class="logo">
            <div class="logo-icon">
                <i class="fas fa-comments"></i>
            </div>
            <span class="logo-text">MySoko Chat</span>
        </a>

        <div class="header-actions">
            <a href="market.php" class="header-btn" title="Marketplace">
                <i class="fas fa-store"></i>
            </a>
            <a href="logistic.php" class="header-btn" title="Logistics">
                <i class="fas fa-truck"></i>
            </a>
            <a href="Learning.php" class="header-btn" title="Learning">
                <i class="fas fa-graduation-cap"></i>
            </a>
            <a href="my_messages.php" class="header-btn" title="My Messages">
                <i class="fas fa-comments"></i>
            </a>

            <a href="logout.php" class="header-btn" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </a>
            <div class="user-avatar" title="<?= htmlspecialchars($username) ?>">
                <?= strtoupper(substr($username, 0, 1)) ?>
            </div>
        </div>
    </header>

    <div class="chat-container">
        <div class="chat-header">
            <h3>Chat with <?= htmlspecialchars($message_partner_username) ?></h3>
            <?php if ($product_id): ?>
                <span>Regarding: <?= htmlspecialchars($product_name) ?></span>
            <?php endif; ?>
        </div>
        <div class="messages-box" id="messagesBox">
            <?php if (empty($messages)): ?>
                <div class="empty-chat">Start a conversation!</div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="message-wrapper">
                        <div class="message <?= ($msg['sender_id'] === $current_user_id) ? 'sent' : 'received' ?>">
                            <?php if (!empty($msg['image_url'])): ?>
                                <img src="<?= htmlspecialchars($msg['image_url']) ?>" alt="Attached Image" class="message-image">
                            <?php endif; ?>
                            <?php if (!empty($msg['message_text'])): ?>
                                <?= nl2br(htmlspecialchars($msg['message_text'])) ?>
                            <?php endif; ?>
                            <span class="message-time"><?= date('M j, H:i', strtotime($msg['sent_at'])) ?></span>

                            <?php if ($msg['sender_id'] === $current_user_id): // Allow sender to delete their own messages ?>
                                <form action="chat.php?seller_id=<?= htmlspecialchars($message_partner_id) ?><?= ($product_id ? '&product_id=' . htmlspecialchars($product_id) : '') ?>" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                    <input type="hidden" name="delete_message_id" value="<?= $msg['id'] ?>">
                                    <button type="submit" class="delete-message-btn" title="Delete message"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <form action="chat.php?seller_id=<?= htmlspecialchars($message_partner_id) ?><?= ($product_id ? '&product_id=' . htmlspecialchars($product_id) : '') ?>" method="POST" class="chat-input-form" enctype="multipart/form-data">
            <label for="message_image" class="file-upload-label" title="Attach Image">
                <i class="fas fa-paperclip"></i>
            </label>
            <input type="file" name="message_image" id="message_image" class="file-upload-input" accept="image/*">
            <input type="text" name="message_text" class="chat-input" placeholder="Type your message...">
            <button type="submit" class="send-btn">Send <i class="fas fa-paper-plane"></i></button>
        </form>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i>
            <?= $_SESSION['success_message'] ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
        <script>
            setTimeout(() => {
                document.querySelector('.success-message').style.display = 'none';
            }, 5000);
        </script>
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
        <div class="error-message">
            <i class="fas fa-times-circle"></i>
            <?= $error_message ?>
        </div>
        <script>
            setTimeout(() => {
                document.querySelector('.error-message').style.display = 'none';
            }, 5000);
        </script>
    <?php endif; ?>

    <script>
        // Scroll to the bottom of the messages box on load
        const messagesBox = document.getElementById('messagesBox');
        messagesBox.scrollTop = messagesBox.scrollHeight;

        // Optional: Clear file input if text is typed, or vice versa (for better UX)
        // This makes sure user isn't confused if they're sending text OR an image, not both easily.
        // Or keep both, just ensures one is primary.
        document.getElementById('message_image').addEventListener('change', function() {
            if (this.files.length > 0) {
                // Optionally show file name or disable text input
                // document.getElementById('chatInput').placeholder = this.files[0].name;
            } else {
                // document.getElementById('chatInput').placeholder = 'Type your message...';
            }
        });
    </script>
</body>
</html>