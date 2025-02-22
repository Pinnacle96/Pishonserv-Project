<?php
$host = 'localhost';
$dbname = 'real_estate_db';
$username = 'root';
$password = ''; // Default for XAMPP

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("❌ Database Connection Failed: " . $conn->connect_error);
}
?>