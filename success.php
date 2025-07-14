<?php
session_start();

// Check if user came from successful registration
if (!isset($_SESSION['registered_user'])) {
    header("Location: sign on.php");
    exit();
}

// Get user data from session
$user = $_SESSION['registered_user'];
unset($_SESSION['registered_user']); // Clear the session data
?>
<!DOCTYPE html>
<html>
<head>
    <title>Registration Successful | My Soko</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .success-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
        }
        .success-icon {
            color: #4CAF50;
            font-size: 50px;
            margin-bottom: 20px;
        }
        .user-details {
            text-align: left;
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .btn {
            background-color: darksalmon;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">✓</div>
        <h2>Registration Successful!</h2>
        
        <div class="user-details">
            <h3>Your Details:</h3>
            <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Role:</strong> <?= htmlspecialchars($user['role']) ?></p>
        </div>

        <p>Thank you for registering with My Soko.</p>
        <a href="home.html" class="btn">Go to Home Page</a>
    </div>
</body>
</html>