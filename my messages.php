<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db.php';

$current_user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

// Fetch all unique users that the current user has chatted with
// This query gets both people the current user sent messages to, and people who sent messages to the current user
$stmt = $pdo->prepare("
    SELECT
        CASE
            WHEN m.sender_id = ? THEN m.receiver_id
            ELSE m.sender_id
        END AS partner_id,
        u.username AS partner_username,
        MAX(m.sent_at) AS last_message_time,
        COUNT(CASE WHEN m.is_read = 0 AND m.receiver_id = ? THEN 1 END) AS unread_count,
        (SELECT message_text FROM messages WHERE (sender_id = partner_id OR receiver_id = partner_id) AND (sender_id = ? OR receiver_id = ?) ORDER BY sent_at DESC LIMIT 1) as last_message_content
    FROM
        messages m
    JOIN
        users u ON u.id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END
    WHERE
        m.sender_id = ? OR m.receiver_id = ?
    GROUP BY
        partner_id, partner_username
    ORDER BY
        last_message_time DESC
");

$stmt->execute([$current_user_id, $current_user_id, $current_user_id, $current_user_id, $current_user_id, $current_user_id, $current_user_id]);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// You might also want to display general notifications here, or integrate them more seamlessly
$unreadNotificationCount = 0; // Placeholder
// For a real implementation, you'd fetch this from your 'notifications' table
// $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
// $stmt->execute([$current_user_id]);
// $unreadNotificationCount = $stmt->fetchColumn();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MySoko | My Messages</title>
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

        /* Header Styles (copy from your existing market.php/logistic.php) */
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
        .notification-container {
            position: relative;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--danger);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }


        /* Main Content */
        .main-container {
            max-width: 900px;
            margin: 100px auto 2rem;
            padding: 2rem;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        h2 {
            font-family: 'Montserrat', sans-serif;
            font-size: 2rem;
            color: var(--primary-dark);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .conversation-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .conversation-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: var(--border-radius-sm);
            text-decoration: none;
            color: var(--dark);
            transition: background-color 0.3s ease;
        }

        .conversation-item:hover {
            background-color: #f0f0f0;
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .conversation-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: var(--primary-light);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .conversation-details {
            flex-grow: 1;
        }

        .conversation-details h3 {
            margin: 0 0 5px 0;
            font-size: 1.1rem;
            color: var(--primary-dark);
        }

        .conversation-details p {
            margin: 0;
            font-size: 0.9rem;
            color: var(--dark-light);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .conversation-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 5px;
            font-size: 0.85rem;
        }

        .last-message-time {
            color: #999;
        }

        .unread-badge {
            background-color: var(--danger);
            color: white;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.8rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--dark-light);
            font-style: italic;
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #ccc;
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="market.php" class="logo">
            <div class="logo-icon">
                <i class="fas fa-comments"></i>
            </div>
            <span class="logo-text">MySoko Messages</span>
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
            <a href="logout.php" class="header-btn" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </a>
            <div class="user-avatar" title="<?= htmlspecialchars($username) ?>">
                <?= strtoupper(substr($username, 0, 1)) ?>
            </div>
        </div>
    </header>

    <main class="main-container">
        <h2>My Conversations</h2>
        <div class="conversation-list">
            <?php if (empty($conversations)): ?>
                <div class="empty-state">
                    <i class="fas fa-comment-alt empty-icon"></i>
                    <p>No conversations yet. Start by contacting a supplier on the marketplace!</p>
                </div>
            <?php else: ?>
                <?php foreach ($conversations as $conversation): ?>
                    <a href="chat.php?seller_id=<?= htmlspecialchars($conversation['partner_id']) ?>" class="conversation-item">
                        <div class="conversation-avatar">
                            <?= strtoupper(substr($conversation['partner_username'], 0, 1)) ?>
                        </div>
                        <div class="conversation-details">
                            <h3><?= htmlspecialchars($conversation['partner_username']) ?></h3>
                            <p><?= htmlspecialchars($conversation['last_message_content']) ?></p>
                        </div>
                        <div class="conversation-meta">
                            <span class="last-message-time">
                                <?= date('M j, Y H:i', strtotime($conversation['last_message_time'])) ?>
                            </span>
                            <?php if ($conversation['unread_count'] > 0): ?>
                                <span class="unread-badge"><?= $conversation['unread_count'] ?></span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>