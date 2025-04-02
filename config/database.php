<?php
// Database configuration for XAMPP (MySQLi)
$host = 'localhost';
$dbname = 'event_management';
$username = 'root';
$password = '';

// Create MySQLi connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // If database doesn't exist, redirect to install script
    if ($conn->connect_errno == 1049) {
        header("Location: install.php");
        exit;
    }
    die("Connection failed: " . $conn->connect_error);
}
?>

