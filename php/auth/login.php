<?php
session_start();
require_once '../config/db_config.php';
require_once '../config/session_config.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');


    if ($username === '' || $password === '') {
        header('Location: ../../index.php?page=admin_login&error=empty');
        exit();
    }


    // Use session_config's loginAdmin function
    if (loginAdmin($username, $password)) {
        header('Location: ../../index.php?page=admin_dashboard');
        exit();
    } else {
        header('Location: ../../index.php?page=admin_login&error=invalid');
        exit();
    }
} else {
    header('Location: ../../index.php?page=admin_login');
    exit();
}



