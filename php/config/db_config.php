<?php
// Detect environment
if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'localhost') {
    // Local XAMPP config
    $host = 'localhost';
    $db   = 'ubnhs-voting';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';
} else {
    // Hostinger config (hidden file, not in git)
    require_once(__DIR__ . '/db_config_hostinger.php');
}

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
?>
