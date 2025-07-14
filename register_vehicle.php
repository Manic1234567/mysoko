<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $make = trim($_POST['make'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $year = (int)($_POST['year'] ?? 0);
    $plateNumber = trim($_POST['plate_number'] ?? '');
    $vehicleType = trim($_POST['vehicle_type'] ?? '');
    $capacity = trim($_POST['capacity'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Basic validation
    if (empty($make) || empty($model) || empty($plateNumber) || empty($vehicleType) || empty($capacity) || empty($location)) {
        $message = '<div class="error-message"><i class="fas fa-exclamation-triangle"></i> Please fill in all required fields.</div>';
    } elseif ($year <= 1900 || $year > date('Y') + 1) { // Basic year validation
        $message = '<div class="error-message"><i class="fas fa-exclamation-triangle"></i> Please enter a valid year.</div>';
    } else {
        try {
            // Check if plate number already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM vehicles WHERE plate_number = ?");
            $stmt->execute([$plateNumber]);
            if ($stmt->fetchColumn() > 0) {
                $message = '<div class="error-message"><i class="fas fa-exclamation-triangle"></i> This plate number is already registered.</div>';
            } else {
                $stmt = $pdo->prepare("INSERT INTO vehicles (user_id, make, model, year, plate_number, vehicle_type, capacity, location, description)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$userId, $make, $model, $year, $plateNumber, $vehicleType, $capacity, $location, $description]);

                $_SESSION['success_message'] = "Vehicle registered successfully!";
                header('Location: logistic.php');
                exit;
            }
        } catch (PDOException $e) {
            error_log("Vehicle registration failed: " . $e->getMessage());
            $message = '<div class="error-message"><i class="fas fa-times-circle"></i> An error occurred while registering your vehicle. Please try again.</div>';
        }
    }
}

$username = $_SESSION['username'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MySoko | Register Vehicle</title>
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

        /* Header (reuse from logistic.php if desired, or simplify) */
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

        /* Form Styles */
        .form-container {
            max-width: 600px;
            margin: 100px auto 2rem; /* Adjusted top margin */
            background: white;
            padding: 2.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .form-container h2 {
            font-family: 'Montserrat', sans-serif;
            font-size: 2rem;
            color: var(--primary-dark);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.6rem;
            font-weight: 500;
            color: var(--dark);
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius-sm);
            font-size: 1rem;
            outline: none;
            transition: var(--transition);
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.2);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .btn-submit {
            display: block;
            width: 100%;
            padding: 1rem;
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: var(--border-radius-sm);
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.4);
        }

        .message-container {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .success-message, .error-message {
            padding: 15px 25px;
            color: white;
            border-radius: var(--border-radius-sm);
            box-shadow: var(--shadow-sm);
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .success-message { background: var(--success); }
        .error-message { background: var(--danger); }
    </style>
</head>
<body>
    <header class="header">
        <a href="logistic.php" class="logo">
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

    <main class="form-container">
        <h2>Register Your Vehicle</h2>
        <div class="message-container">
            <?= $message ?>
        </div>
        <form action="register_vehicle.php" method="POST">
            <div class="form-group">
                <label for="make">Make:</label>
                <input type="text" id="make" name="make" required value="<?= htmlspecialchars($_POST['make'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="model">Model:</label>
                <input type="text" id="model" name="model" required value="<?= htmlspecialchars($_POST['model'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="year">Year:</label>
                <input type="number" id="year" name="year" min="1900" max="<?= date('Y') + 1 ?>" value="<?= htmlspecialchars($_POST['year'] ?? date('Y')) ?>" required>
            </div>
            <div class="form-group">
                <label for="plate_number">Plate Number:</label>
                <input type="text" id="plate_number" name="plate_number" required value="<?= htmlspecialchars($_POST['plate_number'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="vehicle_type">Vehicle Type:</label>
                <select id="vehicle_type" name="vehicle_type" required>
                    <option value="">Select Type</option>
                    <option value="Truck" <?= (($_POST['vehicle_type'] ?? '') == 'Truck') ? 'selected' : '' ?>>Truck</option>
                    <option value="Van" <?= (($_POST['vehicle_type'] ?? '') == 'Van') ? 'selected' : '' ?>>Van</option>
                    <option value="Motorcycle" <?= (($_POST['vehicle_type'] ?? '') == 'Motorcycle') ? 'selected' : '' ?>>Motorcycle</option>
                    <option value="Car" <?= (($_POST['vehicle_type'] ?? '') == 'Car') ? 'selected' : '' ?>>Car</option>
                    <option value="Bus" <?= (($_POST['vehicle_type'] ?? '') == 'Bus') ? 'selected' : '' ?>>Bus</option>
                    <option value="Other" <?= (($_POST['vehicle_type'] ?? '') == 'Other') ? 'selected' : '' ?>>Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="capacity">Capacity (e.g., '5 tons', '20 passengers', 'Small'):</label>
                <input type="text" id="capacity" name="capacity" required value="<?= htmlspecialchars($_POST['capacity'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="location">Location (where vehicle is based):</label>
                <input type="text" id="location" name="location" required value="<?= htmlspecialchars($_POST['location'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="description">Description (Optional):</label>
                <textarea id="description" name="description"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn-submit">Register Vehicle</button>
        </form>
    </main>

</body>
</html>