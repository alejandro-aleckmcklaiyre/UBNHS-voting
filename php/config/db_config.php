<?php
// Database configuration
$host = 'localhost';
$db   = 'ubnhs-voting'; // Change to your database name
$user = 'root';     // Change to your database username
$pass = '';     // Change to your database password
$charset = 'utf8mb4';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
?>
