<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . '/../config/db_config.php');

// Check if autoload file exists before including
$autoload_path = __DIR__ . '/../../vendor/autoload.php';
if (!file_exists($autoload_path)) {
    $autoload_error = [
        'timestamp' => date('Y-m-d H:i:s'),
        'error' => 'Autoload file not found at: ' . $autoload_path,
        'suggestion' => 'Run: composer install or composer require endroid/qr-code'
    ];
    file_put_contents(__DIR__ . '/autoload_error.log', json_encode($autoload_error, JSON_PRETTY_PRINT));
    // Don't die here - we can still function without QR codes
}

// Load autoloader if it exists and import QR Code classes
$qr_library_available = false;
if (file_exists($autoload_path)) {
    require_once($autoload_path);
    
    // Check if QR Code classes are available
    if (class_exists('Endroid\QrCode\QrCode')) {
        $qr_library_available = true;
    }
}

if (!$conn) {
    $error_log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'error' => 'Database connection failed',
        'mysql_error' => mysqli_connect_error()
    ];
    file_put_contents(__DIR__ . '/connection_test.log', json_encode($error_log, JSON_PRETTY_PRINT));
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
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

// Function to create QR code with student details
function createQRWithDetails($unique_code, $file_path, $student_details) {
    global $qr_library_available;
    
    // First, try to generate QR code (either advanced or simple)
    $temp_qr_path = $file_path . '_temp.png';
    $qr_generated = false;
    
    if ($qr_library_available) {
        $qr_generated = generateQRCodeAdvanced($unique_code, $temp_qr_path);
    }
    
    if (!$qr_generated) {
        $qr_generated = generateSimpleQR($unique_code, $temp_qr_path);
    }
    
    if (!$qr_generated) {
        return false;
    }
    
    // Now create composite image with student details
    $result = addStudentDetailsToQR($temp_qr_path, $file_path, $student_details);
    
    // Clean up temporary QR file
    if (file_exists($temp_qr_path)) {
        unlink($temp_qr_path);
    }
    
    return $result;
}

// Function to add student details to QR code image
function addStudentDetailsToQR($qr_path, $output_path, $student_details) {
    // Check if GD extension is available
    if (!extension_loaded('gd')) {
        // If GD is not available, just copy the QR code
        return copy($qr_path, $output_path);
    }
    
    try {
        // Load the QR code image
        $qr_image = imagecreatefrompng($qr_path);
        if (!$qr_image) {
            return false;
        }
        
        $qr_width = imagesx($qr_image);
        $qr_height = imagesy($qr_image);
        
        // Calculate dimensions for the final image
        $text_height = 80; // Space for text at bottom
        $padding = 10;
        $final_width = max($qr_width, 300) + ($padding * 2);
        $final_height = $qr_height + $text_height + ($padding * 3);
        
        // Create new image with white background
        $final_image = imagecreatetruecolor($final_width, $final_height);
        $white = imagecolorallocate($final_image, 255, 255, 255);
        $black = imagecolorallocate($final_image, 0, 0, 0);
        
        // Fill background with white
        imagefill($final_image, 0, 0, $white);
        
        // Center the QR code horizontally
        $qr_x = ($final_width - $qr_width) / 2;
        $qr_y = $padding;
        
        // Copy QR code to final image
        imagecopy($final_image, $qr_image, $qr_x, $qr_y, 0, 0, $qr_width, $qr_height);
        
        // Prepare text details
        $name_line = trim($student_details['last_name'] . ', ' . $student_details['first_name'] . 
                    ($student_details['middle_name'] ? ' ' . $student_details['middle_name'] : '') .
                    ($student_details['suffix'] ? ' ' . $student_details['suffix'] : ''));
        $class_line = $student_details['year_level'] . ' - ' . $student_details['section'];
        $student_number_line = $student_details['student_number'];
        
        // Text positioning
        $text_start_y = $qr_y + $qr_height + $padding + 15;
        $text_x = $final_width / 2;
        
        // Try to use a built-in font, fall back to default if needed
        $font_size = 3; // Built-in font size (1-5)
        
        // Add text lines (centered)
        $name_width = imagefontwidth($font_size) * strlen($name_line);
        $class_width = imagefontwidth($font_size) * strlen($class_line);
        $number_width = imagefontwidth($font_size) * strlen($student_number_line);
        
        // Center text horizontally
        imagestring($final_image, $font_size, 
                   ($final_width - $name_width) / 2, 
                   $text_start_y, $name_line, $black);
        
        imagestring($final_image, $font_size, 
                   ($final_width - $class_width) / 2, 
                   $text_start_y + 20, $class_line, $black);
        
        imagestring($final_image, $font_size, 
                   ($final_width - $number_width) / 2, 
                   $text_start_y + 40, $student_number_line, $black);
        
        // Save the final image
        $result = imagepng($final_image, $output_path);
        
        // Clean up memory
        imagedestroy($qr_image);
        imagedestroy($final_image);
        
        return $result;
        
    } catch (Exception $e) {
        error_log("QR Details Addition Error: " . $e->getMessage());
        // Fall back to copying just the QR code
        return copy($qr_path, $output_path);
    }
}

// Improved QR Code generation with version compatibility
function generateQRCodeAdvanced($unique_code, $file_path) {
    global $qr_library_available;
    
    if (!$qr_library_available) {
        return false;
    }
    
    try {
        // Use fully qualified class names to avoid use statement issues
        $qr = null;
        
        // Method 1: Try static create method (v4+)
        if (method_exists('Endroid\QrCode\QrCode', 'create')) {
            $qr = \Endroid\QrCode\QrCode::create($unique_code);
        } 
        // Method 2: Try constructor (v3)
        else {
            $qr = new \Endroid\QrCode\QrCode($unique_code);
        }
        
        if ($qr === null) {
            throw new Exception('Could not create QrCode object');
        }
        
        // Set properties (handle different versions)
        try {
            if (method_exists($qr, 'setSize')) {
                $qr->setSize(250);
            }
            if (method_exists($qr, 'setEncoding') && class_exists('Endroid\QrCode\Encoding\Encoding')) {
                $qr->setEncoding(new \Endroid\QrCode\Encoding\Encoding('UTF-8'));
            }
            if (method_exists($qr, 'setErrorCorrectionLevel') && class_exists('Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh')) {
                $qr->setErrorCorrectionLevel(new \Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh());
            }
        } catch (Exception $e) {
            // Continue with basic settings
        }
        
        // Create writer
        if (!class_exists('Endroid\QrCode\Writer\PngWriter')) {
            throw new Exception('PngWriter class not found');
        }
        
        $writer = new \Endroid\QrCode\Writer\PngWriter();
        
        // Generate QR code
        $result = $writer->write($qr);
        
        // Save to file
        $result->saveToFile($file_path);
        
        // Verify file was created
        if (file_exists($file_path) && filesize($file_path) > 0) {
            return true;
        }
        
        return false;
        
    } catch (Exception $e) {
        error_log("Advanced QR Generation Error: " . $e->getMessage());
        return false;
    } catch (Error $e) {
        error_log("Advanced QR Fatal Error: " . $e->getMessage());
        return false;
    }
}

// Improved fallback QR Code generation using Google Charts API
function generateSimpleQR($unique_code, $file_path) {
    try {
        // Google Charts QR Code API with better error handling
        $qr_url = "https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=" . urlencode($unique_code);
        
        // Create context for the request with timeout
        $context = stream_context_create([
            'http' => [
                'timeout' => 15, // 15 seconds timeout
                'method' => 'GET',
                'header' => 'User-Agent: Mozilla/5.0 (compatible; PHP QR Generator)\r\n'
            ]
        ]);
        
        // Get QR code image data
        $qr_data = @file_get_contents($qr_url, false, $context);
        
        if ($qr_data === false) {
            return false;
        }
        
        // Validate that we got actual image data
        if (strlen($qr_data) < 100) {
            return false;
        }
        
        // Save to file
        $result = file_put_contents($file_path, $qr_data);
        
        // Verify file was created and has reasonable size
        if ($result !== false && file_exists($file_path) && filesize($file_path) > 100) {
            return true;
        }
        
        return false;
        
    } catch (Exception $e) {
        error_log("Simple QR Generation Error: " . $e->getMessage());
        return false;
    }
}

// Main QR generation function with multiple fallbacks
function generateQRCode($unique_code, $file_path, $student_details = null) {
    // Create QR code directory if it doesn't exist
    $qr_dir = dirname($file_path);
    if (!file_exists($qr_dir)) {
        if (!mkdir($qr_dir, 0755, true)) {
            return false;
        }
    }
    
    // Check if directory is writable
    if (!is_writable($qr_dir)) {
        return false;
    }
    
    // If student details are provided, create QR with details
    if ($student_details !== null) {
        return createQRWithDetails($unique_code, $file_path, $student_details);
    }
    
    // Otherwise, use the original method
    // Try advanced QR generation first
    if (generateQRCodeAdvanced($unique_code, $file_path)) {
        return true;
    }
    
    // Try simple fallback
    if (generateSimpleQR($unique_code, $file_path)) {
        return true;
    }
    
    return false;
}

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// Log initial POST request
$log_data = [
    'timestamp' => date('Y-m-d H:i:s'),
    'post' => $_POST,
    'step' => 'initial_post_received'
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

// Generate a unique code for the student: surname + random code
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
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
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
if (!$group_stmt) {
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
    exit;
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
    echo json_encode(['success' => false, 'message' => 'Invalid year level or section.']);
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

if (!$stmt) {
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
    exit;
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
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    exit;
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

// --- QR CODE GENERATION START ---
$qr_success = false;
$qr_error_message = '';

// Create file path for QR code
$file_name = sprintf(
    '%s-%s-%s-%s.png',
    preg_replace('/[^a-zA-Z0-9]/', '', substr($last_name, 0, 20)),
    preg_replace('/[^a-zA-Z0-9]/', '', substr($year_level, 0, 10)),
    preg_replace('/[^a-zA-Z0-9]/', '', substr($section, 0, 10)),
    preg_replace('/[^a-zA-Z0-9]/', '', $student_number)
);

$qr_dir_path = __DIR__ . '/../../assets/qr_code';
$file_path = $qr_dir_path . DIRECTORY_SEPARATOR . $file_name;

// Prepare student details for QR code
$student_details = [
    'first_name' => $first_name,
    'middle_name' => $middle_name,
    'last_name' => $last_name,
    'suffix' => $suffix,
    'year_level' => $year_level,
    'section' => $section,
    'student_number' => $student_number
];

// Log QR generation start
$qr_log = [
    'timestamp' => date('Y-m-d H:i:s'),
    'step' => 'qr_generation_start',
    'file_path' => $file_path,
    'qr_library_available' => $qr_library_available,
    'gd_extension_loaded' => extension_loaded('gd')
];
log_voter_event($qr_log);

// Try to generate QR code with student details
$qr_success = generateQRCode($unique_code, $file_path, $student_details);

if ($qr_success) {
    $qr_success_log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'step' => 'qr_generation_successful',
        'file_path' => $file_path,
        'file_size' => filesize($file_path)
    ];
    log_voter_event($qr_success_log);
} else {
    $qr_error_log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'step' => 'qr_generation_failed',
        'error' => 'All QR generation methods failed'
    ];
    log_voter_event($qr_error_log);
}
// --- QR CODE GENERATION END ---

// Prepare response - ALWAYS SUCCESS if database insert worked
$response_message = 'Voter added successfully!';
if (!$qr_success) {
    $response_message .= ' (QR code could not be generated - this can be done later)';
}

$final_response = [
    'timestamp' => date('Y-m-d H:i:s'),
    'step' => 'success_response_sent',
    'qr_generated' => $qr_success,
    'message' => $response_message,
    'student_id' => $student_id
];
log_voter_event($final_response);

// ALWAYS return success if database insert worked
echo json_encode([
    'success' => true, 
    'message' => $response_message,
    'qr_generated' => $qr_success,
    'student_id' => $student_id,
    'unique_code' => $unique_code
]);

// Final completion log
$completion_log = [
    'timestamp' => date('Y-m-d H:i:s'),
    'step' => 'script_completed_successfully'
];
log_voter_event($completion_log);

$conn->close();
?>