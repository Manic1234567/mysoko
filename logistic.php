<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db.php';

$message = '';
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Fetch all registered vehicles along with owner details
$stmt = $pdo->prepare("SELECT v.*, u.username, u.contact AS owner_contact, u.location AS owner_location
                      FROM vehicles v
                      JOIN users u ON v.user_id = u.id
                      ORDER BY v.created_at DESC");
$stmt->execute();
$vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$username = $_SESSION['username'] ?? 'User';
$unreadCount = 0; // You might want to integrate actual notification count here if applicable
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MySoko | Logistics</title>
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

        /* Main Content */
        .main-container {
            max-width: 1200px;
            margin: 100px auto 2rem; /* Adjusted top margin for fixed header */
            padding: 2rem;
        }

        h2 {
            font-family: 'Montserrat', sans-serif;
            font-size: 2rem;
            color: var(--primary-dark);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .vehicles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .vehicle-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            padding: 1.5rem;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
        }

        .vehicle-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
        }

        .vehicle-card h3 {
            font-size: 1.3rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .vehicle-card p {
            font-size: 0.95rem;
            color: var(--dark-light);
            margin-bottom: 0.8rem;
        }

        .vehicle-card .details-row {
            display: flex;
            align-items: center;
            margin-bottom: 0.4rem;
            font-size: 0.9rem;
            color: var(--dark);
        }

        .vehicle-card .details-row i {
            margin-right: 8px;
            color: var(--primary);
            width: 20px; /* Align icons */
            text-align: center;
        }

        .owner-info {
            border-top: 1px solid #eee;
            padding-top: 1rem;
            margin-top: auto; /* Push to bottom */
            text-align: right;
            font-size: 0.9rem;
            color: var(--dark-light);
        }

        .owner-info strong {
            color: var(--dark);
        }

        .contact-owner-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 15px;
            border-radius: var(--border-radius-sm);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            background: var(--gradient-primary);
            color: white;
            text-decoration: none;
            margin-top: 1rem;
            width: 100%;
        }

        .contact-owner-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.4);
        }

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
            text-decoration: none; /* For the anchor tag */
        }

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

    </style>
</head>
<body>
    <header class="header">
        <a href="market.php" class="logo">
            <div class="logo-icon">
                <i class="fas fa-truck"></i>
            </div>
            <span class="logo-text">MySoko Logistics</span>
        </a>

        <div class="header-actions">
            <a href="market.php" class="header-btn" title="Marketplace">
                <i class="fas fa-store"></i>
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
        <h2>Available Vehicles for Transport</h2>
        <div class="vehicles-grid">
            <?php if (empty($vehicles)): ?>
                <div class="empty-state">
                    <i class="fas fa-truck-loading empty-icon"></i>
                    <h3 class="empty-title">No vehicles registered yet</h3>
                    <p>Be the first to register a vehicle!</p>
                </div>
            <?php else: ?>
                <?php foreach ($vehicles as $vehicle): ?>
                    <div class="vehicle-card">
                        <h3><?= htmlspecialchars($vehicle['make']) ?> <?= htmlspecialchars($vehicle['model']) ?></h3>
                        <p><strong>Type:</strong> <?= htmlspecialchars($vehicle['vehicle_type']) ?></p>
                        <div class="details-row">
                            <i class="fas fa-id-card"></i> <span>Plate No: <strong><?= htmlspecialchars($vehicle['plate_number']) ?></strong></span>
                        </div>
                        <div class="details-row">
                            <i class="fas fa-weight-hanging"></i> <span>Capacity: <?= htmlspecialchars($vehicle['capacity']) ?></span>
                        </div>
                        <div class="details-row">
                            <i class="fas fa-map-marker-alt"></i> <span>Location: <?= htmlspecialchars($vehicle['location']) ?></span>
                        </div>
                        <div class="details-row">
                            <i class="fas fa-calendar-alt"></i> <span>Year: <?= htmlspecialchars($vehicle['year']) ?></span>
                             <div>
        <label for="vehicle_image">Upload Vehicle Image:</label>
        <input type="file" id="vehicle_image" name="vehicle_image" accept="image/*">
        <small>Accepted formats: JPG, JPEG, PNG, GIF (Max size: 5MB)</small>
    </div>
                        </div>
                        <?php if (!empty($vehicle['description'])): ?>
                            <p>Description: <?= htmlspecialchars($vehicle['description']) ?></p>
                        <?php endif; ?>

                        <div class="owner-info">
                            <i class="fas fa-user-circle"></i> Owner: <strong><?= htmlspecialchars($vehicle['username']) ?></strong><br>
                            <i class="fas fa-phone"></i> Contact: <strong><?= htmlspecialchars($vehicle['owner_contact']) ?></strong>
                        </div>
                        <a href="tel:<?= htmlspecialchars($vehicle['owner_contact']) ?>" class="contact-owner-btn">
                            <i class="fas fa-phone-alt"></i> Call Owner
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <a href="register_vehicle.php" class="fab" title="Register New Vehicle">
        <i class="fas fa-plus"></i>
    </a>

    <?php if ($message): ?>
        <div class="
            <?php if (isset($_SESSION['success_message'])) { echo 'success-message'; }
                  else if (isset($_SESSION['error_message'])) { echo 'error-message'; } ?>">
            <i class="fas fa-check-circle"></i>
            <?= $message ?>
        </div>
        <script>
            setTimeout(() => {
                document.querySelector('.success-message, .error-message').style.display = 'none';
            }, 5000);
        </script>
    <?php endif; ?>

</body>
</html>