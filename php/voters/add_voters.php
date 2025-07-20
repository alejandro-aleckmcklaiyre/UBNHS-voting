<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . '/../config/db_config.php');
require_once(__DIR__ . '/generate_qr.php');

// Set proper headers for JSON response
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

if (!$conn) {
    $error_log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'error' => 'Database connection failed',
        'mysql_error' => mysqli_connect_error()
    ];
    file_put_contents(__DIR__ . '/connection_test.log', json_encode($error_log, JSON_PRETTY_PRINT));
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Helper function to sanitize input
function sanitize($conn, $str) {
    return mysqli_real_escape_string($conn, trim($str));
}

function log_voter_event($data) {
    $log_path = __DIR__ . '/../../pages/voters_page/voters_logs.json';
    $logfile = __DIR__ . '/voters.log';
    $existing_logs = [];
    
    if (file_exists($log_path)) {
        $existing_logs = json_decode(file_get_contents($log_path), true);
        if (!is_array($existing_logs)) $existing_logs = [];
    }
    
    $existing_logs[] = $data;
    file_put_contents($log_path, json_encode($existing_logs, JSON_PRETTY_PRINT));
    
    // Also log errors to voters.log
    if (isset($data['error'])) {
        $log_entry = '[' . $data['timestamp'] . '] ERROR: ' . $data['error'];
        if (isset($data['sql_error'])) {
            $log_entry .= ' | SQL: ' . $data['sql_error'];
        }
        if (isset($data['post'])) {
            $log_entry .= ' | POST: ' . json_encode($data['post']);
        }
        file_put_contents($logfile, $log_entry . "\n", FILE_APPEND);
    }
}

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check if request is AJAX
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

try {
    // Log initial POST request
    $log_data = [
        'timestamp' => date('Y-m-d H:i:s'),
        'post' => $_POST,
        'step' => 'initial_post_received',
        'is_ajax' => $is_ajax
    ];
    log_voter_event($log_data);

    // Sanitize input data
    $student_number = isset($_POST['student_number']) ? sanitize($conn, $_POST['student_number']) : '';
    $first_name = isset($_POST['first_name']) ? sanitize($conn, $_POST['first_name']) : '';
    $middle_name = isset($_POST['middle_name']) ? sanitize($conn, $_POST['middle_name']) : NULL;
    $last_name = isset($_POST['last_name']) ? sanitize($conn, $_POST['last_name']) : '';
    $suffix = isset($_POST['suffix']) ? sanitize($conn, $_POST['suffix']) : NULL;
    $email = isset($_POST['email']) ? sanitize($conn, $_POST['email']) : '';
    $year_level = isset($_POST['year_level']) ? sanitize($conn, $_POST['year_level']) : '';
    $section = isset($_POST['section']) ? sanitize($conn, $_POST['section']) : '';
    $status_id = isset($_POST['status_id']) ? intval($_POST['status_id']) : 1;

    // Handle empty strings as NULL for optional fields
    if (empty($middle_name)) $middle_name = NULL;
    if (empty($suffix)) $suffix = NULL;

    // Generate a unique code for the student
    $random_code = bin2hex(random_bytes(8));
    $unique_code = $last_name . $random_code;

    // Log sanitized data
    $log_data = [
        'timestamp' => date('Y-m-d H:i:s'),
        'step' => 'data_sanitized',
        'sanitized_data' => [
            'student_number' => $student_number,
            'first_name' => $first_name,
            'middle_name' => $middle_name,
            'last_name' => $last_name,
            'suffix' => $suffix,
            'email' => $email,
            'year_level' => $year_level,
            'section' => $section,
            'status_id' => $status_id,
            'unique_code' => $unique_code
        ]
    ];
    log_voter_event($log_data);

    // Basic validation
    if (empty($student_number) || empty($first_name) || empty($last_name) || empty($email) || empty($year_level) || empty($section)) {
        $error = [
            'timestamp' => date('Y-m-d H:i:s'),
            'error' => 'Missing required fields',
            'step' => 'validation_failed',
            'validation_check' => [
                'student_number_empty' => empty($student_number),
                'first_name_empty' => empty($first_name),
                'last_name_empty' => empty($last_name),
                'email_empty' => empty($email),
                'year_level_empty' => empty($year_level),
                'section_empty' => empty($section)
            ],
            'post' => $_POST
        ];
        log_voter_event($error);
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    // Student number must be exactly 12 digits
    if (!preg_match('/^\d{12}$/', $student_number)) {
        $error = [
            'timestamp' => date('Y-m-d H:i:s'),
            'error' => 'Student number must be exactly 12 digits',
            'step' => 'student_number_validation_failed',
            'student_number' => $student_number,
            'student_number_length' => strlen($student_number),
            'post' => $_POST
        ];
        log_voter_event($error);
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Student number must be exactly 12 digits']);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = [
            'timestamp' => date('Y-m-d H:i:s'),
            'error' => 'Invalid email format',
            'step' => 'email_validation_failed',
            'email' => $email,
            'post' => $_POST
        ];
        log_voter_event($error);
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }

    // Check if student_status exists
    $status_check_sql = "SELECT id FROM student_status WHERE id = ?";
    $status_check_stmt = $conn->prepare($status_check_sql);
    if (!$status_check_stmt) {
        throw new Exception('Failed to prepare status check query: ' . $conn->error);
    }
    
    $status_check_stmt->bind_param('i', $status_id);
    $status_check_stmt->execute();
    $status_check_stmt->bind_result($found_status_id);
    
    if (!$status_check_stmt->fetch()) {
        $status_check_stmt->close();
        $error = [
            'timestamp' => date('Y-m-d H:i:s'),
            'error' => 'Invalid status_id',
            'step' => 'status_validation_failed',
            'status_id' => $status_id,
            'post' => $_POST
        ];
        log_voter_event($error);
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid status ID']);
        exit;
    }
    $status_check_stmt->close();

    // Log before class group lookup
    $log_data = [
        'timestamp' => date('Y-m-d H:i:s'),
        'step' => 'before_class_group_lookup',
        'lookup_params' => [
            'year_level' => $year_level,
            'section' => $section
        ]
    ];
    log_voter_event($log_data);

    // Look up class_group_id
    $group_sql = "SELECT id FROM class_group WHERE year_level = ? AND section = ? LIMIT 1";
    $group_stmt = $conn->prepare($group_sql);
    if (!$group_stmt) {
        throw new Exception('Failed to prepare class group query: ' . $conn->error);
    }

    $group_stmt->bind_param('ss', $year_level, $section);
    $group_stmt->execute();
    $group_stmt->bind_result($class_group_id);

    if (!$group_stmt->fetch()) {
        $group_stmt->close();
        $error = [
            'timestamp' => date('Y-m-d H:i:s'),
            'error' => 'Invalid year level or section',
            'step' => 'class_group_not_found',
            'year_level' => $year_level,
            'section' => $section,
            'post' => $_POST
        ];
        log_voter_event($error);
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid year level or section']);
        exit;
    }
    $group_stmt->close();

    // Log successful class group lookup
    $log_data = [
        'timestamp' => date('Y-m-d H:i:s'),
        'step' => 'class_group_found',
        'class_group_id' => $class_group_id
    ];
    log_voter_event($log_data);

    // Check for duplicate student number
    $duplicate_check_sql = "SELECT id FROM student WHERE student_number = ?";
    $duplicate_check_stmt = $conn->prepare($duplicate_check_sql);
    if (!$duplicate_check_stmt) {
        throw new Exception('Failed to prepare duplicate check query: ' . $conn->error);
    }
    
    $duplicate_check_stmt->bind_param('s', $student_number);
    $duplicate_check_stmt->execute();
    $duplicate_check_stmt->bind_result($existing_id);
    
    if ($duplicate_check_stmt->fetch()) {
        $duplicate_check_stmt->close();
        $error = [
            'timestamp' => date('Y-m-d H:i:s'),
            'error' => 'Student number already exists',
            'step' => 'duplicate_student_number',
            'student_number' => $student_number,
            'existing_id' => $existing_id,
            'post' => $_POST
        ];
        log_voter_event($error);
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Student number already exists']);
        exit;
    }
    $duplicate_check_stmt->close();

    // Check for duplicate email
    $email_check_sql = "SELECT id FROM student WHERE email = ?";
    $email_check_stmt = $conn->prepare($email_check_sql);
    if (!$email_check_stmt) {
        throw new Exception('Failed to prepare email check query: ' . $conn->error);
    }
    
    $email_check_stmt->bind_param('s', $email);
    $email_check_stmt->execute();
    $email_check_stmt->bind_result($existing_email_id);
    
    if ($email_check_stmt->fetch()) {
        $email_check_stmt->close();
        $error = [
            'timestamp' => date('Y-m-d H:i:s'),
            'error' => 'Email already exists',
            'step' => 'duplicate_email',
            'email' => $email,
            'existing_id' => $existing_email_id,
            'post' => $_POST
        ];
        log_voter_event($error);
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit;
    }
    $email_check_stmt->close();

    // Log before insert
    $log_data = [
        'timestamp' => date('Y-m-d H:i:s'),
        'step' => 'before_insert',
        'insert_data' => [
            'student_number' => $student_number,
            'first_name' => $first_name,
            'middle_name' => $middle_name,
            'last_name' => $last_name,
            'suffix' => $suffix,
            'email' => $email,
            'unique_code' => $unique_code,
            'class_group_id' => $class_group_id,
            'status_id' => $status_id
        ]
    ];
    log_voter_event($log_data);

    // Insert into database
    $sql = "INSERT INTO student (student_number, first_name, middle_name, last_name, suffix, email, unique_code, has_voted, class_group_id, status_id) VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception('Failed to prepare insert query: ' . $conn->error);
    }

    $stmt->bind_param(
        'sssssssii',
        $student_number,
        $first_name,
        $middle_name,
        $last_name,
        $suffix,
        $email,
        $unique_code,
        $class_group_id,
        $status_id
    );

    if (!$stmt->execute()) {
        $error = [
            'timestamp' => date('Y-m-d H:i:s'),
            'error' => 'Database insert error',
            'step' => 'insert_failed',
            'sql_error' => $stmt->error,
            'errno' => $stmt->errno,
            'post' => $_POST
        ];
        log_voter_event($error);
        $stmt->close();
        throw new Exception('Database insert failed: ' . $stmt->error);
    }

    // Student successfully inserted
    $student_id = $stmt->insert_id;
    $stmt->close();

    $success_log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'step' => 'insert_successful',
        'student_id' => $student_id
    ];
    log_voter_event($success_log);

    // QR CODE GENERATION
    $qr_success = false;
    $qr_error_message = '';
    
    try {
        // Prepare student data for QR generation
        $student_data = [
            'first_name' => $first_name,
            'middle_name' => $middle_name,
            'last_name' => $last_name,
            'suffix' => $suffix,
            'year_level' => $year_level,
            'section' => $section,
            'student_number' => $student_number
        ];

        // Try to generate QR code
        $qr_success = generateStudentQR($unique_code, $student_data);
        
        if (!$qr_success) {
            $qr_error_message = 'QR code generation failed but student was added successfully';
        }
    } catch (Exception $e) {
        $qr_error_message = 'QR code generation error: ' . $e->getMessage();
        log_voter_event([
            'timestamp' => date('Y-m-d H:i:s'),
            'error' => 'QR generation exception',
            'details' => $e->getMessage(),
            'student_id' => $student_id
        ]);
    }

    // Prepare response message
    $response_message = 'Voter added successfully!';
    if (!$qr_success) {
        $response_message .= ' (QR code could not be generated - this can be done later)';
    }

    // Log final response
    $final_response = [
        'timestamp' => date('Y-m-d H:i:s'),
        'step' => 'success_response_sent',
        'qr_generated' => $qr_success,
        'message' => $response_message,
        'student_id' => $student_id
    ];
    log_voter_event($final_response);

    // Send success response
    http_response_code(200);
    echo json_encode([
        'success' => true, 
        'message' => $response_message,
        'qr_generated' => $qr_success,
        'student_id' => $student_id,
        'unique_code' => $unique_code,
        'qr_error' => $qr_success ? null : $qr_error_message
    ]);

    // Final completion log
    $completion_log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'step' => 'script_completed_successfully'
    ];
    log_voter_event($completion_log);

} catch (Exception $e) {
    // Log the exception
    $error_log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'error' => 'Uncaught exception',
        'step' => 'exception_caught',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'post' => $_POST
    ];
    log_voter_event($error_log);
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
} finally {
    // Always close connection
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?>