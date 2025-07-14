<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db.php'; // Ensure db.php includes the new helper functions

// Database connection is now handled within db.php
// No need to redeclare $host, $dbname, etc. here

// IMPORTANT: Ensure the 'notifications' table has a 'type' column.
// If not, run this SQL: ALTER TABLE notifications ADD COLUMN type VARCHAR(50) DEFAULT 'general' AFTER message;


// Fetch notifications for current user AND total unread messages
$notifications = [];
$unreadCount = 0; // For general notifications (e.g., 'buy now' type)
$totalUnreadMessages = 0; // For chat messages
$current_user_id = $_SESSION['user_id'];

if (isset($current_user_id)) {
    // Get general notifications
    $stmt = $pdo->prepare("SELECT * FROM notifications
                          WHERE user_id = ?
                          ORDER BY created_at DESC");
    $stmt->execute([$current_user_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get count of general unread notifications
    $unreadCount = getTotalUnreadNotifications($pdo, $current_user_id);

    // Get count of unread chat messages
    $totalUnreadMessages = getTotalUnreadMessages($pdo, $current_user_id);
}

// Mark notifications as read when viewed
// Note: This marks ALL unread notifications as read. You might want to refine this
// to only mark specific types or when a notification is clicked in the dropdown.
if (isset($_GET['view_notifications'])) {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1
                          WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$current_user_id]);
    header("Location: market.php");
    exit;
}

// Handle search functionality
$searchQuery = '';
if (isset($_GET['search'])) {
    $searchQuery = trim($_GET['search']);
}

// Fetch products with search filter
$query = "SELECT p.*, u.username FROM products p JOIN users u ON p.user_id = u.id WHERE 1=1";
$params = [];

if (!empty($searchQuery)) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ? OR u.username LIKE ?)";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

$query .= " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$userRole = $_SESSION['role'] ?? 'user';
$username = $_SESSION['username'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MySoko | Marketplace</title>
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

        /* Header Styles */
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

        /* Search Bar Styles */
        .search-container {
            display: flex;
            max-width: 500px;
            margin: 0 auto;
            padding: 1rem;
        }

        .search-input {
            flex: 1;
            padding: 0.8rem 1.2rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius-sm) 0 0 var(--border-radius-sm);
            font-size: 1rem;
            outline: none;
            transition: var(--transition);
        }

        .search-input:focus {
            border-color: var(--primary);
        }

        .search-btn {
            padding: 0 1.5rem;
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: 0 var(--border-radius-sm) var(--border-radius-sm) 0;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .search-btn:hover {
            background: var(--primary-dark);
        }

        /* Notification Styles */
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

        .notifications-dropdown {
            position: absolute;
            top: 50px;
            right: 0;
            width: 350px;
            background: white;
            border-radius: var(--border-radius-sm);
            box-shadow: var(--shadow-lg);
            z-index: 1000;
            display: none;
            max-height: 500px;
            overflow-y: auto;
        }

        .notifications-dropdown.show {
            display: block;
        }

        .notification-header {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Modified .notification-item to be a link */
        .notification-item {
            padding: 15px;
            border-bottom: 1px solid #f1f1f1;
            /* cursor: pointer; */ /* Removed as it's a link now */
            transition: background 0.2s;
            display: block; /* Make it block level to take full width */
            text-decoration: none; /* Remove underline */
            color: inherit; /* Inherit text color */
        }

        .notification-item.unread {
            background-color: #f8f9fa;
        }

        .notification-item:hover {
            background-color: #f1f1f1;
        }

        .notification-message {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .notification-details {
            font-size: 13px;
            color: #666;
        }

        .notification-contact {
            margin-top: 8px;
            padding: 8px;
            background: #f1f1f1;
            border-radius: 5px;
            font-size: 13px;
        }

        .notification-time {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }

        .view-all {
            display: block;
            text-align: center;
            padding: 10px;
            color: var(--primary);
            font-weight: 500;
        }

        /* Main Content */
        .main-container {
            max-width: 1400px;
            margin: 80px auto 0;
            padding: 2rem;
        }

        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 3rem;
        }

        .product-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
        }

        .product-image-container {
            height: 200px;
            overflow: hidden;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-content {
            padding: 1.5rem;
        }

        .product-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .product-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .product-meta {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
            font-size: 0.9rem;
            color: var(--dark-light);
        }

        .product-location {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: 1.5rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: var(--border-radius-sm);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: none;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            flex: 1;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.4);
        }

        /* Floating Action Button */
        .fab {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--gradient-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 10px 30px rgba(108, 92, 231, 0.5);
            cursor: pointer;
            z-index: 100;
        }

        /* Success Message */
        .success-message {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 15px 25px;
            background: var(--success);
            color: white;
            border-radius: var(--border-radius-sm);
            box-shadow: var(--shadow);
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            grid-column: 1 / -1;
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            color: #ddd;
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark-light);
        }
        /* Contact Supplier Styles */
        .contact-supplier-container {
            position: relative;
            width: 100%;
        }

        .contact-supplier-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .contact-options {
            position: absolute;
            bottom: 100%; /* Position above the button */
            left: 0;
            width: 100%;
            background: white;
            border-radius: var(--border-radius-sm);
            box-shadow: var(--shadow);
            display: none; /* Hidden by default */
            flex-direction: column;
            overflow: hidden;
            z-index: 10;
            margin-bottom: 10px; /* Space between button and dropdown */
        }

        .contact-supplier-container:hover .contact-options {
            display: flex; /* Show on hover */
        }

        .contact-option {
            padding: 12px 20px;
            text-decoration: none;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background 0.2s;
        }

        .contact-option:hover {
            background: #f5f5f5;
        }

        .chat-option {
            border-bottom: 1px solid #eee;
        }

        .call-option {
            color: var(--success);
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="market.php" class="logo">
            <div class="logo-icon">
                <i class="fas fa-store"></i>
            </div>
            <span class="logo-text">MySoko</span>
        </a>

        <div class="search-container">
            <form method="GET" action="market.php" class="search-form">
                <input type="text" name="search" class="search-input" placeholder="Search products..." value="<?= htmlspecialchars($searchQuery) ?>">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <div class="header-actions">
            <a href="logistic.php" class="header-btn" title="Logistics">
                <i class="fas fa-truck"></i>
            </a>
            <a href="Learning.php" class="header-btn" title="Learning">
                <i class="fas fa-graduation-cap"></i>
            </a>
            <a href="my messages.php" class="header-btn" title="My Messages">
                <i class="fas fa-comments"></i>
                <?php if ($totalUnreadMessages > 0): ?>
                    <span class="notification-badge"><?= $totalUnreadMessages ?></span>
                <?php endif; ?>
            </a>

            <div class="notification-container">
                <button class="header-btn notification-btn" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <?php if ($unreadCount > 0): ?>
                        <span class="notification-badge"><?= $unreadCount ?></span>
                    <?php endif; ?>
                </button>

                <div class="notifications-dropdown" id="notificationsDropdown">
                    <div class="notification-header">
                        <h4>Notifications</h4>
                        <a href="market.php?view_notifications=1" class="mark-read">Mark all as read</a>
                    </div>

                    <?php if (empty($notifications)): ?>
                        <div class="notification-item">
                            <div class="notification-message">No notifications yet</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($notifications as $notification):
                            $details = json_decode($notification['details'], true);
                            $link = '#'; // Default link
                            // Determine the correct link based on notification type
                            if ($notification['type'] === 'new_message' && isset($details['chat_link'])) {
                                $link = htmlspecialchars($details['chat_link']);
                            } else if (isset($details['product_id'])) {
                                // For 'buy now' type notifications, you might link to a specific product detail page
                                // Or a transaction management page if you build one.
                                // For now, we'll keep it simple, maybe just link to market.php or the product itself if you have a product_detail.php
                                // $link = "product_detail.php?id=" . htmlspecialchars($details['product_id']);
                                $link = "market.php"; // Or a more specific page if you have one
                            }
                        ?>
                            <a href="<?= $link ?>" class="notification-item <?= $notification['is_read'] ? '' : 'unread' ?>">
                                <div class="notification-message"><?= htmlspecialchars($notification['message']) ?></div>
                                <?php if ($notification['type'] === 'new_message' && isset($details['sender_username'])): ?>
                                    <div class="notification-details">
                                        From: <strong><?= htmlspecialchars($details['sender_username']) ?></strong>
                                    </div>
                                <?php elseif (isset($details['buyer_contact'])): // Existing product inquiry notification ?>
                                    <div class="notification-details">
                                        <div><strong>Contact:</strong> <?= htmlspecialchars($details['buyer_contact']) ?></div>
                                        <div><strong>Location:</strong> <?= htmlspecialchars($details['buyer_location']) ?></div>
                                    </div>
                                    <div class="notification-contact">
                                        <i class="fas fa-phone"></i> <?= htmlspecialchars($details['buyer_contact']) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="notification-time">
                                    <?= date('M j, g:i a', strtotime($notification['created_at'])) ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <a href="market.php?view_notifications=1" class="view-all">Mark all as read</a>
                </div>
            </div>

            <a href="logout.php" class="header-btn" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </a>
            <div class="user-avatar" title="<?= htmlspecialchars($username) ?>">
                <?= strtoupper(substr($username, 0, 1)) ?>
            </div>
        </div>
    </header>

    <main class="main-container">
        <section>
            <h2>Available Products</h2>
            <div class="products-grid">
                <?php if (empty($products)): ?>
                    <div class="empty-state">
                        <i class="fas fa-box-open empty-icon"></i>
                        <h3 class="empty-title"><?= empty($searchQuery) ? 'No products available yet' : 'No products match your search' ?></h3>
                        <p><?= empty($searchQuery) ? 'Be the first to post a product!' : 'Try a different search term' ?></p>
                    </div>
                <?php else:
                    foreach ($products as $product):
                        $hasExistingChat = false;
                        if ($current_user_id !== $product['user_id']) { // Don't check for self-chats
                            $stmtCheckChat = $pdo->prepare("SELECT COUNT(*) FROM messages
                                                            WHERE (sender_id = ? AND receiver_id = ?)
                                                            OR (sender_id = ? AND receiver_id = ?)
                                                            AND product_id = ?");
                            $stmtCheckChat->execute([$current_user_id, $product['user_id'], $product['user_id'], $current_user_id, $product['id']]);
                            if ($stmtCheckChat->fetchColumn() > 0) {
                                $hasExistingChat = true;
                            }
                        }
                    ?>
                        <div class="product-card">
                            <div class="product-image-container">
                                <?php if ($product['image_url']): ?>
                                    <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
                                <?php else: ?>
                                    <div style="background-color: #f0f0f0; height: 100%; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-image" style="font-size: 3rem; color: #ccc;"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="product-content">
                                <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                                <div class="product-price">Tsh <?= number_format($product['price'], 2) ?></div>
                                <p><?= htmlspecialchars(substr($product['description'], 0, 100)) ?><?= strlen($product['description']) > 100 ? '...' : '' ?></p>
                                <div class="product-meta">
                                    <span class="product-location">
                                        <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($product['location']) ?>
                                    </span>
                                      <span class="product-contact">
                                        <i class="fas fa-phone-alt"></i> <?= htmlspecialchars($product['contact']) ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-user"></i> <?= htmlspecialchars($product['username']) ?>
                                    </span>
                                </div>
                                <div class="product-actions">
                                    <div class="contact-supplier-container">
                                        <?php if ($current_user_id === $product['user_id']): // If user is the product owner ?>
                                            <button class="btn btn-primary" disabled style="opacity: 0.7; cursor: not-allowed;">
                                                <i class="fas fa-user-tag"></i> Your Product
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-primary contact-supplier-btn">
                                                <i class="fas fa-headset"></i> <?= $hasExistingChat ? 'Continue Chat' : 'Contact Supplier' ?>
                                            </button>
                                            <div class="contact-options">
                                                <a href="chat.php?seller_id=<?= $product['user_id'] ?>&product_id=<?= $product['id'] ?>" class="contact-option chat-option">
                                                    <i class="fas fa-comment-dots"></i> <?= $hasExistingChat ? 'Go to Chat' : 'Chat Now' ?>
                                                </a>
                                                <a href="tel:<?= htmlspecialchars($product['contact']) ?>" class="contact-option call-option">
                                                    <i class="fas fa-phone-alt"></i> Call Seller
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach;
                endif; ?>
            </div>
        </section>
    </main>

    <a href="create_product.php" class="fab">
        <i class="fas fa-plus"></i>
    </a>

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

    <script>
        // Toggle notifications dropdown
        document.querySelector('.notification-btn').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('notificationsDropdown').classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.notification-container')) {
                document.getElementById('notificationsDropdown').classList.remove('show');
            }
        });
    </script>
</body>
</html>