<?php
require_once(__DIR__ . '/../config/db_config.php');
if (!$conn) {
    $error_log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'error' => 'Database connection failed',
        'mysql_error' => mysqli_connect_error()
    ];
    file_put_contents(__DIR__ . '/connection_test.log', json_encode($error_log, JSON_PRETTY_PRINT));
    die('Database connection failed');
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $log_data = [
        'timestamp' => date('Y-m-d H:i:s'),
        'post' => $_POST,
        'step' => 'initial_post_received'
    ];
    log_voter_event($log_data);

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
    $unique_code = bin2hex(random_bytes(8));

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
        echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
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
        echo json_encode(['success' => false, 'message' => 'Student number must be exactly 12 digits.']);
        exit;
    }

    // Check if student_status exists
    $status_check_sql = "SELECT id FROM student_status WHERE id = ?";
    $status_check_stmt = $conn->prepare($status_check_sql);
    if ($status_check_stmt) {
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
            echo json_encode(['success' => false, 'message' => 'Invalid status ID.']);
            exit;
        }
        $status_check_stmt->close();
    }

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
    if ($group_stmt) {
        $group_stmt->bind_param('ss', $year_level, $section);
        $group_stmt->execute();
        $group_stmt->bind_result($class_group_id);
        if ($group_stmt->fetch()) {
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
            if ($duplicate_check_stmt) {
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
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Student number already exists.']);
                    exit;
                }
                $duplicate_check_stmt->close();
            }

            // Check for duplicate email
            $email_check_sql = "SELECT id FROM student WHERE email = ?";
            $email_check_stmt = $conn->prepare($email_check_sql);
            if ($email_check_stmt) {
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
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Email already exists.']);
                    exit;
                }
                $email_check_stmt->close();
            }

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
            if ($stmt) {
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
                if ($stmt->execute()) {
                    $success_log = [
                        'timestamp' => date('Y-m-d H:i:s'),
                        'step' => 'insert_successful',
                        'student_id' => $stmt->insert_id
                    ];
                    log_voter_event($success_log);
                    echo json_encode(['success' => true, 'message' => 'Voter added successfully.']);
                } else {
                    $error = [
                        'timestamp' => date('Y-m-d H:i:s'),
                        'error' => 'Database insert error',
                        'step' => 'insert_failed',
                        'sql_error' => $stmt->error,
                        'errno' => $stmt->errno,
                        'post' => $_POST
                    ];
                    log_voter_event($error);
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
                }
                $stmt->close();
            } else {
                $error = [
                    'timestamp' => date('Y-m-d H:i:s'),
                    'error' => 'Database prepare error (insert)',
                    'step' => 'prepare_insert_failed',
                    'sql_error' => $conn->error,
                    'post' => $_POST
                ];
                log_voter_event($error);
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
            }
        } else {
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
            echo json_encode(['success' => false, 'message' => 'Invalid year level or section.']);
        }
    } else {
        $error = [
            'timestamp' => date('Y-m-d H:i:s'),
            'error' => 'Database prepare error (lookup)',
            'step' => 'prepare_lookup_failed',
            'sql_error' => $conn->error,
            'post' => $_POST
        ];
        log_voter_event($error);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
    $conn->close();
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
}
?>