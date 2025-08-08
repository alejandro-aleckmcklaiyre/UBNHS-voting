<?php
session_start();
require_once '../config/db_config.php';
require_once '../config/session_config.php';

// Response headers for JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin', '*');
header('Access-Control-Allow-Methods', 'POST');
header('Access-Control-Allow-Headers', 'Content-Type');

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Error logging function
function logStudentError($message, $details = null) {
    $log_path = __DIR__ . '/student_logs.json';
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'error' => $message,
        'details' => $details
    ];
    $logs = [];
    if (file_exists($log_path)) {
        $logs = json_decode(file_get_contents($log_path), true);
        if (!is_array($logs)) $logs = [];
    }
    $logs[] = $log_entry;
    file_put_contents($log_path, json_encode($logs, JSON_PRETTY_PRINT));
}

// Add this function near the top, after logStudentError()
function logStudentErrorTxt($message, $details = null) {
    $log_path = __DIR__ . '/student_error.log';
    $log_entry = "[" . date('Y-m-d H:i:s') . "] " . $message;
    if ($details) $log_entry .= " | Details: " . (is_string($details) ? $details : json_encode($details));
    $log_entry .= "\n";
    file_put_contents($log_path, $log_entry, FILE_APPEND);
}

// Global error handler
set_exception_handler(function($e) {
    logStudentError('Uncaught Exception', $e->getMessage());
    logStudentErrorTxt('Uncaught Exception', $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Server error. Check student_logs.json or student_error.log for details.']);
    exit;
});
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    logStudentError('PHP Error', "$errstr in $errfile on line $errline");
    logStudentErrorTxt('PHP Error', "$errstr in $errfile on line $errline");
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Server error. Check student_logs.json or student_error.log for details.']);
    exit;
});

function sendResponse($success, $message, $data = null, $redirect = null) {
    if (!$success) {
        logStudentError($message, $data);
        logStudentErrorTxt($message, $data);
    }
    $response = [
        'success' => $success,
        'message' => $message
    ];
    if ($data !== null) $response['data'] = $data;
    if ($redirect !== null) $response['redirect'] = $redirect;
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Only POST requests are allowed');
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['qr_code_data'])) {
    sendResponse(false, 'Invalid request format. QR code data is required.');
}

$qr_code_data = trim($data['qr_code_data']);
if (empty($qr_code_data)) {
    sendResponse(false, 'QR code data cannot be empty');
}

// Get all surnames from the database
$surnames = [];
$surname_query = $conn->query("SELECT DISTINCT last_name FROM student");
while ($row = $surname_query->fetch_assoc()) {
    $surnames[] = $row['last_name'];
}
$surname_query->close();

// Find which surname matches the start of the QR code
$surname = null;
foreach ($surnames as $possible_surname) {
    if (strpos($qr_code_data, $possible_surname) === 0) {
        $surname = $possible_surname;
        break;
    }
}

if (!$surname) {
    sendResponse(false, 'Invalid QR code format (surname not found)');
}

$unique_code = $qr_code_data; // Use the whole string for unique_code

// Debug log
logStudentError('Debug QR', ['surname' => $surname, 'unique_code' => $unique_code]);

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    sendResponse(false, 'Database connection error', $conn->connect_error);
}

$stmt = $conn->prepare("
    SELECT s.id, s.student_number, s.first_name, s.middle_name, s.last_name, s.suffix, s.email, s.status_id, ss.status_name, s.class_group_id
    FROM student s
    JOIN student_status ss ON s.status_id = ss.id
    WHERE s.last_name = ? AND s.unique_code = ? AND ss.status_name = 'Active'
    LIMIT 1
");
$stmt->bind_param("ss", $surname, $unique_code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    sendResponse(false, 'Invalid or already used QR code.');
}

$student = $result->fetch_assoc();
$stmt->close();

// Get year_level from class_group
$year_level = null;
$class_group_id = $student['class_group_id'] ?? null;
if ($class_group_id) {
    $year_stmt = $conn->prepare("SELECT year_level FROM class_group WHERE id = ?");
    $year_stmt->bind_param("i", $class_group_id);
    $year_stmt->execute();
    $year_stmt->bind_result($year_level);
    $year_stmt->fetch();
    $year_stmt->close();
}

// Mark QR code as used (change status_id to 'Used')
$used_status_id = 2;
$update_stmt = $conn->prepare("UPDATE student SET status_id = ? WHERE id = ?");
$update_stmt->bind_param("ii", $used_status_id, $student['id']);
if (!$update_stmt->execute()) {
    sendResponse(false, 'Failed to update QR code status.', $conn->error);
}
$update_stmt->close();

// Log QR scan
$insert_scan_log = $conn->prepare("INSERT INTO qr_scan_logs (student_id, unique_code) VALUES (?, ?)");
$insert_scan_log->bind_param("is", $student['id'], $unique_code);
$insert_scan_log->execute();
$insert_scan_log->close();

// Log session activity
logSessionActivity('login', $student['id'], 'student', $student['student_number'], true);

// Store student info in session
$_SESSION['student_logged_in'] = true;
$_SESSION['student_id'] = $student['id'];
$_SESSION['student_number'] = $student['student_number'];
$_SESSION['student_name'] = trim($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name'] . ($student['suffix'] ? ' ' . $student['suffix'] : ''));
$_SESSION['login_time'] = time();
$_SESSION['last_activity'] = time();
$_SESSION['year_level'] = $year_level; // <-- Add this line

$student_data = [
    'id' => $student['id'],
    'student_number' => $student['student_number'],
    'name' => $_SESSION['student_name'],
    'email' => $student['email']
];

sendResponse(
    true,
    'Login successful! Welcome, ' . $_SESSION['student_name'],
    $student_data,
    'index.php?page=voting_page' // <-- use this for redirect
);

$conn->close();
?>