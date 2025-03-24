<?php
// Database connection parameters
$hostname = "localhost"; // Change if your MySQL host is different
$username = "root";      // Default XAMPP username
$password = "";         // Default XAMPP password (blank)
$database = "project_desk"; // Your database name

// Create connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to ensure proper handling of special characters
$conn->set_charset("utf8mb4");
?> 