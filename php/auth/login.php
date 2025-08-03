<?php
session_start();
require_once '../config/db_config.php';
require_once '../config/session_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        header('Location: /ubnhs-voting/pages/admin_login/admin_login.html?error=empty');
        exit();
    }

    // Use session_config's loginAdmin function
    if (loginAdmin($username, $password)) {
        header('Locatio n: /ubnhs-voting/pages/admin_dashboard/admin_dashboard.html');
        exit();
    } else {
        header('Location: /ubnhs-voting/pages/admin_login/admin_login.html?error=invalid');
        exit();
    }
} else {
    header('Location: /ubnhs-voting/pages/admin_login/admin_login.html');
    exit();
}
