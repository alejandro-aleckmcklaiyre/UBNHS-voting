<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once(__DIR__ . '/db_config.php');

/**
 * Check DB connection
 */
function checkDatabaseConnection() {
    global $conn;
    return isset($conn) && $conn && !$conn->connect_error;
}

/**
 * Log session activity to DB
 */
function logSessionActivity($action, $userId, $userType, $username, $success = true, $errorMessage = null) {
    global $conn;
    if (!checkDatabaseConnection()) return false;
    $sessionId = session_id();
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $stmt = $conn->prepare("INSERT INTO session_logs (session_id, user_id, user_type, username, action, ip_address, user_agent, success, error_message) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sisssssis", $sessionId, $userId, $userType, $username, $action, $ipAddress, $userAgent, $success, $errorMessage);
    $stmt->execute();
    $stmt->close();
}

/**
 * Admin Login
 */
function loginAdmin($username, $password) {
    global $conn;
    if (!checkDatabaseConnection()) return false;
    $stmt = $conn->prepare("SELECT id, username FROM admin WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['user_type'] = 'admin';
        logSessionActivity('login', $admin['id'], 'admin', $admin['username'], true);
        $stmt->close();
        return true;
    }
    logSessionActivity('login_failed', null, 'admin', $username, false, 'Invalid credentials');
    $stmt->close();
    return false;
}

/**
 * Student Login
 */
function loginStudent($token) {
    global $conn;
    if (!checkDatabaseConnection()) return false;
    $lastPlusPos = strrpos($token, '+');
    if ($lastPlusPos === false) {
        logSessionActivity('login_failed', null, 'student', $token, false, 'Invalid token format');
        return false;
    }
    $surname = substr($token, 0, $lastPlusPos);
    $uniqueCode = substr($token, $lastPlusPos + 1);
    $stmt = $conn->prepare("SELECT s.id, s.first_name, s.last_name, s.email FROM student s JOIN student_status ss ON s.status_id = ss.id WHERE s.last_name = ? AND s.unique_code = ? AND ss.status_name = 'Active'");
    $stmt->bind_param("ss", $surname, $uniqueCode);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $student = $result->fetch_assoc();
        $_SESSION['student_logged_in'] = true;
        $_SESSION['student_id'] = $student['id'];
        $_SESSION['student_name'] = $student['first_name'] . ' ' . $student['last_name'];
        $_SESSION['student_email'] = $student['email'];
        $_SESSION['user_type'] = 'student';
        logSessionActivity('login', $student['id'], 'student', $_SESSION['student_name'], true);
        $stmt->close();
        return true;
    }
    logSessionActivity('login_failed', null, 'student', $token, false, 'Invalid token or inactive student');
    $stmt->close();
    return false;
}

/**
 * Check login status
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}
function isStudentLoggedIn() {
    return isset($_SESSION['student_logged_in']) && $_SESSION['student_logged_in'] === true;
}
function isLoggedIn() {
    return isAdminLoggedIn() || isStudentLoggedIn();
}

/**
 * Logout
 */
function logout() {
    session_destroy();
    session_start();
}