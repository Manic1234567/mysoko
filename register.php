<?php
require_once 'db.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $region = $_POST['region'];
    $role = $_POST['role'];
    $nida_no = $_POST['nida_no'];

    // Validate inputs
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    if (empty($region)) {
        $errors[] = "Region is required";
    }

    if (empty($role)) {
        $errors[] = "Role is required";
    }

    if (empty($nida_no)) {
        $errors[] = "NIDA Number is required";
    }

    // Check if email or username already exists
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$email, $username]);
            
            if ($stmt->fetch()) {
                $errors[] = "Email or username already exists";
            } else {
                // Hash password and create user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    INSERT INTO users 
                    (username, email, password, region, role, nida_no) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$username, $email, $hashed_password, $region, $role, $nida_no]);
                
                // Set session variables
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['username'] = $username;
                $_SESSION['logged_in'] = true;
                $_SESSION['role'] = $role;
                
                // Redirect to home page
                header("Location: market.php");
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MY SOKO</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #28a745;
            --primary-dark: #218838;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
            --border-radius: 5px;
            --box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9f9f9;
            color: #333;
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            background-image: url('assets/images/bg-pattern.jpg');
            background-size: cover;
            background-position: center;
        }
        
        .register-container {
            background: white;
            padding: 2.5rem;
            border-radius: 10px;
            box-shadow: var(--box-shadow);
            width: 100%;
            max-width: 600px;
            margin: 20px;
            transition: var(--transition);
        }
        
        .register-container:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .register-header h2 {
            color: var(--primary);
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .register-header p {
            color: #666;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #555;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.2);
        }
        
        .form-row {
            display: flex;
            gap: 1rem;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .btn {
            width: 100%;
            padding: 0.75rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            margin-top: 1rem;
            transition: var(--transition);
        }
        
        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .error-message {
            color: var(--danger);
            margin-bottom: 1.5rem;
            padding: 0.75rem;
            background-color: rgba(220, 53, 69, 0.1);
            border-radius: var(--border-radius);
        }
        
        .error-message p {
            margin-bottom: 0.5rem;
        }
        
        .error-message p:last-child {
            margin-bottom: 0;
        }
        
        .success-message {
            color: var(--primary);
            margin-bottom: 1.5rem;
            padding: 0.75rem;
            background-color: rgba(40, 167, 69, 0.1);
            border-radius: var(--border-radius);
            text-align: center;
        }
        
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #666;
        }
        
        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .register-container {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h2>Create an Account</h2>
            <p>Join MY SOKO to start trading agricultural products</p>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required placeholder="Enter your username">
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="region">Region</label>
                    <select id="region" name="region" required>
                        <option value="">Select your region</option>
                        <option value="Arusha">Arusha</option>
                        <option value="Dar es Salaam">Dar es Salaam</option>
                        <option value="Dodoma">Dodoma</option>
                        <option value="Geita">Geita</option>
                        <option value="Iringa">Iringa</option>
                        <option value="Kagera">Kagera</option>
                        <option value="Katavi">Katavi</option>
                        <option value="Kigoma">Kigoma</option>
                        <option value="Kilimanjaro">Kilimanjaro</option>
                        <option value="Lindi">Lindi</option>
                        <option value="Manyara">Manyara</option>
                        <option value="Mara">Mara</option>
                        <option value="Mbeya">Mbeya</option>
                        <option value="Morogoro">Morogoro</option>
                        <option value="Mtwara">Mtwara</option>
                        <option value="Mwanza">Mwanza</option>
                        <option value="Njombe">Njombe</option>
                        <option value="Pemba North">Pemba North</option>
                        <option value="Pemba South">Pemba South</option>
                        <option value="Pwani">Pwani</option>
                        <option value="Rukwa">Rukwa</option>
                        <option value="Ruvuma">Ruvuma</option>
                        <option value="Shinyanga">Shinyanga</option>
                        <option value="Simiyu">Simiyu</option>
                        <option value="Singida">Singida</option>
                        <option value="Songwe">Songwe</option>
                        <option value="Tabora">Tabora</option>
                        <option value="Tanga">Tanga</option>
                        <option value="Zanzibar North">Zanzibar North</option>
                        <option value="Zanzibar South">Zanzibar South</option>
                        <option value="Zanzibar West">Zanzibar West</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="">Select your role</option>
                        <option value="Driver">Driver</option>
                        <option value="Buyer">Buyer</option>
                        <option value="Seller">Seller</option>
                        <option value="Farmer">Farmer</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="nida_no">NIDA Number</label>
                <input type="text" id="nida_no" name="nida_no" required placeholder="Enter your NIDA number">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Create password">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm password">
                </div>
            </div>
            
            <button type="submit" class="btn">Register Now</button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>