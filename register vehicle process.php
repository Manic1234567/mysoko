<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'db.php'; // Contains your $pdo connection

// Define upload directory
$upload_dir = 'uploads/vehicles/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true); // Create directory if it doesn't exist (ensure proper permissions)
}

$user_id = $_SESSION['user_id'];
$vehicle_name = $_POST['vehicle_name'] ?? '';
$vehicle_model = $_POST['vehicle_model'] ?? '';
$capacity = $_POST['capacity'] ?? ''; // Assuming you have other fields

$image_url = null; // Default to null if no image is uploaded or if upload fails
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic validation for required text fields
    if (empty($vehicle_name) || empty($vehicle_model) || empty($capacity)) {
        $error_message = "All vehicle details are required.";
    } else {
        // Handle image upload
        if (isset($_FILES['vehicle_image']) && $_FILES['vehicle_image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp_name = $_FILES['vehicle_image']['tmp_name'];
            $file_name = $_FILES['vehicle_image']['name'];
            $file_size = $_FILES['vehicle_image']['size'];
            $file_type = $_FILES['vehicle_image']['type'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $max_file_size = 5 * 1024 * 1024; // 5 MB

            if (!in_array($file_ext, $allowed_extensions)) {
                $error_message = "Only JPG, JPEG, PNG, and GIF files are allowed.";
            } elseif ($file_size > $max_file_size) {
                $error_message = "File size exceeds 5MB limit.";
            } else {
                // Generate a unique filename to prevent conflicts
                $new_file_name = uniqid('vehicle_') . '.' . $file_ext;
                $target_path = $upload_dir . $new_file_name;

                if (move_uploaded_file($file_tmp_name, $target_path)) {
                    $image_url = $target_path; // Store the relative path in the database
                } else {
                    $error_message = "Failed to upload image.";
                }
            }
        }
    }

    if (empty($error_message)) {
        try {
            // Insert vehicle details into the database
            $stmt = $pdo->prepare("INSERT INTO vehicles (user_id, vehicle_name, vehicle_model, capacity, image_url, registered_at)
                                  VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$user_id, $vehicle_name, $vehicle_model, $capacity, $image_url]);

            $_SESSION['success_message'] = "Vehicle registered successfully!";
            header('Location: logistic.php'); // Redirect to logistics page or vehicle list
            exit;
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
            error_log("Vehicle registration failed: " . $e->getMessage()); // Log error for debugging
        }
    }

    // If there's an error, store it in session and redirect back to the form
    if (!empty($error_message)) {
        $_SESSION['error_message'] = $error_message;
        header('Location: register_vehicle.php'); // Or wherever your form is
        exit;
    }
} else {
    // If accessed directly without POST, redirect or show error
    header('Location: register_vehicle.php');
    exit;
}
?>