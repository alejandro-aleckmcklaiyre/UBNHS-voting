<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/login_error.log');
error_reporting(E_ALL);

$baseUrl = (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'localhost')
    ? '/ubnhs-voting'
    : '';

session_start();
require_once '../config/db_config.php';
require_once '../config/session_config.php';

function log_custom_error($message) {
    $logfile = __DIR__ . '/login_error.log';
    $entry = "[" . date('Y-m-d H:i:s') . "] " . $message . "\n";
    file_put_contents($logfile, $entry, FILE_APPEND);
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');


        if ($username === '' || $password === '') {
            log_custom_error(
                "Empty username or password. " .
                "POST: " . json_encode($_POST) .
                " | IP: {$_SERVER['REMOTE_ADDR']}" .
                " | Agent: {$_SERVER['HTTP_USER_AGENT']}" .
                " | Script: {$_SERVER['SCRIPT_NAME']}" .
                " | URI: {$_SERVER['REQUEST_URI']}" .
                (isset($_SERVER['HTTP_REFERER']) ? " | Referrer: {$_SERVER['HTTP_REFERER']}" : "")
            );
            header("Location: $baseUrl/index.php?page=admin_login&error=empty");
            exit();
        }


        // Use session_config's loginAdmin function
        if (loginAdmin($username, $password)) {
            log_custom_error(
                "Login success for user: $username" .
                " | IP: {$_SERVER['REMOTE_ADDR']}" .
                " | Agent: {$_SERVER['HTTP_USER_AGENT']}" .
                " | Script: {$_SERVER['SCRIPT_NAME']}" .
                " | URI: {$_SERVER['REQUEST_URI']}"
            );
            header("Location: $baseUrl/index.php?page=admin_dashboard");
            exit();
        } else {
            $post_data = $_POST;
            unset($post_data['password']); // Don't log password
            log_custom_error(
                "Login failed for user: $username" .
                " | IP: {$_SERVER['REMOTE_ADDR']}" .
                " | Agent: {$_SERVER['HTTP_USER_AGENT']}" .
                " | Script: {$_SERVER['SCRIPT_NAME']}" .
                " | URI: {$_SERVER['REQUEST_URI']}" .
                (isset($_SERVER['HTTP_REFERER']) ? " | Referrer: {$_SERVER['HTTP_REFERER']}" : "") .
                " | POST: " . json_encode($post_data)
            );
            header("Location: $baseUrl/index.php?page=admin_login&error=invalid");
            exit();
        }
    } else {
        log_custom_error(
            "Invalid request method: " . $_SERVER['REQUEST_METHOD'] .
            " | IP: {$_SERVER['REMOTE_ADDR']}" .
            " | Agent: {$_SERVER['HTTP_USER_AGENT']}" .
            " | Script: {$_SERVER['SCRIPT_NAME']}" .
            " | URI: {$_SERVER['REQUEST_URI']}" .
            (isset($_SERVER['HTTP_REFERER']) ? " | Referrer: {$_SERVER['HTTP_REFERER']}" : "")
        );
        header("Location: $baseUrl/index.php?page=admin_login");
        exit();
    }
} catch (Throwable $e) {
    log_custom_error(
        "Exception: " . $e->getMessage() .
        " in " . $e->getFile() .
        " line " . $e->getLine() .
        " | IP: {$_SERVER['REMOTE_ADDR']}" .
        " | Agent: {$_SERVER['HTTP_USER_AGENT']}" .
        " | Script: {$_SERVER['SCRIPT_NAME']}" .
        " | URI: {$_SERVER['REQUEST_URI']}" .
        (isset($_SERVER['HTTP_REFERER']) ? " | Referrer: {$_SERVER['HTTP_REFERER']}" : "")
    );
    header("Location: $baseUrl/index.php?page=admin_login&error=server");
    exit();
}



