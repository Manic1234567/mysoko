<?php
// 1. DATABASE CONNECTION
$host = "localhost";
$user = "root";      // Default XAMPP username
$pass = "";          // Default XAMPP password
$db   = "market";

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. HANDLE FORM SUBMISSIONS
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $location = $_POST['location'];
    
    // Image upload handling
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    
    // Insert into database
    $sql = "INSERT INTO products (name, price, quantity, location, image_path) 
            VALUES ('$name', $price, $quantity, '$location', '$target_file')";
    
    if ($conn->query($sql) {
        echo "<script>alert('Product added!')</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Market Feed</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; }
        .post { background: white; margin: 20px 0; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .post img { width: 100%; height: 300px; object-fit: cover; }
        .price { color: green; font-weight: bold; }
        form { background: white; padding: 20px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <!-- PRODUCT SUBMISSION FORM -->
        <form method="POST" enctype="multipart/form-data">
            <h2>Add New Product</h2>
            <input type="text" name="name" placeholder="Product Name" required>
            <input type="number" step="0.01" name="price" placeholder="Price" required>
            <input type="number" name="quantity" placeholder="Quantity" required>
            <input type="text" name="location" placeholder="Location" required>
            <input type="file" name="image" accept="image/*" required>
            <button type="submit">Post Product</button>
        </form>

        <!-- DISPLAY PRODUCTS FROM DATABASE -->
        <?php
        $sql = "SELECT * FROM products ORDER BY created_at DESC";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo '<div class="post">';
                echo '<img src="' . $row["image_path"] . '">';
                echo '<h3>' . $row["name"] . '</h3>';
                echo '<p class="price">$' . $row["price"] . '</p>';
                echo '<p>Available: ' . $row["quantity"] . '</p>';
                echo '<p>Location: ' . $row["location"] . '</p>';
                echo '</div>';
            }
        } else {
            echo "<p>No products found</p>";
        }
        
        $conn->close();
        ?>
    </div>
</body>
</html>