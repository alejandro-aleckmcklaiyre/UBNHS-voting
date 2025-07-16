<?php
session_start();
require_once '../config/db_config.php';

function log_error_json($type, $username, $ip, $extra = '') {
    $log_file = __DIR__ . '/login_logs.json';
    $date = date('Y-m-d H:i:s');
    $entry = [
        'timestamp' => $date,
        'type' => $type,
        'username' => $username,
        'ip' => $ip,
        'extra' => $extra
    ];
    $logs = [];
    if (file_exists($log_file)) {
        $logs = json_decode(file_get_contents($log_file), true);
        if (!is_array($logs)) $logs = [];
    }
    $logs[] = $entry;
    file_put_contents($log_file, json_encode($logs, JSON_PRETTY_PRINT));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        log_error_json('empty_fields', $username, $_SERVER['REMOTE_ADDR']);
        header('Location: /ubnhs-voting/pages/admin_login/admin_login.html?error=empty');
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT password FROM admin WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            if ($admin['password'] === $password || password_verify($password, $admin['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $username;
                header('Location: /ubnhs-voting/pages/admin_dashboard/admin_dashboard.html');
                exit();
            } else {
                log_error_json('failed_login', $username, $_SERVER['REMOTE_ADDR'], 'Username found, password incorrect');
                header('Location: /ubnhs-voting/pages/admin_login/admin_login.html?error=invalid');
                exit();
            }
        } else {
            log_error_json('failed_login', $username, $_SERVER['REMOTE_ADDR'], 'Username not found');
            header('Location: /ubnhs-voting/pages/admin_login/admin_login.html?error=invalid');
            exit();
        }
    } catch (PDOException $e) {
        log_error_json('db_error', $username, $_SERVER['REMOTE_ADDR'], $e->getMessage());
        header('Location: /ubnhs-voting/pages/admin_login/admin_login.html?error=db');
        exit();
    }
} else {
    header('Location: /ubnhs-voting/pages/admin_login/admin_login.html');
    exit();
}
