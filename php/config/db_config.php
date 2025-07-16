<?php
// Database configuration
$host = 'localhost';
$db   = 'ubnhs-voting'; // Change to your database name
$user = 'root';     // Change to your database username
$pass = '';     // Change to your database password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<h2>Database connection successful!</h2>";
} catch (PDOException $e) {
    echo "<h2>Database connection failed:</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
?>
