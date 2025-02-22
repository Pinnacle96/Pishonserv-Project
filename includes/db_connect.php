<?php
// Check if session is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Connection
$host = "localhost";
$username = "root";  // Change if needed
$password = "";       // Change if needed
$database = "real_estate_db";

$conn = new mysqli($host, $username, $password, $database);

// Check Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>