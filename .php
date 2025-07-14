<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Get user data from session
$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];
$user_role = $_SESSION['user_role'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - My Soko</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .user-info {
            background: #f4f4f4;
            padding: 20px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <h1>Welcome to Your Dashboard</h1>
        <a href="logout.php">Logout</a>
    </div>
    
    <div class="user-info">
        <h3>Your Account Information</h3>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user_email); ?></p>
        <p><strong>Role:</strong> <?php echo htmlspecialchars($user_role); ?></p>
    </div>
    
    <!-- Add role-specific content here -->
    <?php if ($user_role == 'Seller'): ?>
        <h2>Seller Tools</h2>
        <!-- Seller-specific content -->
    <?php elseif ($user_role == 'Buyer'): ?>
        <h2>Buyer Dashboard</h2>
        <!-- Buyer-specific content -->
    <?php endif; ?>
</body>
</html>