<?php
require 'maconfig.php';
try {
    $stmt = $pdo->query("SELECT 1");
    echo "Database connection OK";
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}