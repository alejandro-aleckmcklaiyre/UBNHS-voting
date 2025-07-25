<?php
// Set up error handlers first, before anything else
function log_email_attempt($data) {
    $log_file = __DIR__ . '/../../pages/voters_page/email_logs.json';
    $data['timestamp'] = date('Y-m-d H:i:s');
    $logs = [];
    if (file_exists($log_file)) {
        $content = file_get_contents($log_file);
        if ($content !== false) {
            $logs = json_decode($content, true) ?: [];
        }
    }
    $logs[] = $data;
    file_put_contents($log_file, json_encode($logs, JSON_PRETTY_PRINT));
}

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $msg = "[" . date('Y-m-d H:i:s') . "] PHP ERROR $errno at $errfile:$errline - $errstr\n";
    file_put_contents(__DIR__ . '/email.log', $msg, FILE_APPEND);
    
    // Also try to log to JSON if possible
    try {
        log_email_attempt([
            'status' => 'php_error',
            'error_level' => $errno,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline
        ]);
    } catch (Exception $e) {
        // Ignore logging errors in error handler
    }
});

register_shutdown_function(function() {
    $error = error_get_last(); // Fixed typo here
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $msg = "[" . date('Y-m-d H:i:s') . "] PHP FATAL ERROR at {$error['file']}:{$error['line']} - {$error['message']}\n";
        file_put_contents(__DIR__ . '/email.log', $msg, FILE_APPEND);
        
        // Also try to log to JSON if possible
        try {
            log_email_attempt([
                'status' => 'fatal_error',
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line']
            ]);
        } catch (Exception $e) {
            // Ignore logging errors in error handler
        }
    }
});

header('Content-Type: application/json');

// Test if we can write to log file immediately
$test_log = "[" . date('Y-m-d H:i:s') . "] Script started\n";
file_put_contents(__DIR__ . '/email.log', $test_log, FILE_APPEND);

// Try to include required files with error handling
try {
    if (!file_exists('../config/db_config.php')) {
        throw new Exception('db_config.php not found at ../config/db_config.php');
    }
    require_once '../config/db_config.php';
    
    // Load email configuration
    if (!file_exists('../config/email_config.php')) {
        throw new Exception('email_config.php not found at ../config/email_config.php');
    }
    require_once '../config/email_config.php';
    
    if (!file_exists('../../vendor/autoload.php')) {
        throw new Exception('Composer autoload not found at ../../vendor/autoload.php');
    }
    require_once '../../vendor/autoload.php'; // PHPMailer
} catch (Exception $e) {
    $error_msg = "[" . date('Y-m-d H:i:s') . "] REQUIRE ERROR: " . $e->getMessage() . "\n";
    file_put_contents(__DIR__ . '/email.log', $error_msg, FILE_APPEND);
    
    log_email_attempt([
        'status' => 'require_error',
        'message' => $e->getMessage()
    ]);
    
    echo json_encode(['success' => false, 'message' => 'Server configuration error: ' . $e->getMessage()]);
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$student_number = $_POST['student_number'] ?? '';
if (!$student_number) {
    log_email_attempt(['student_number' => $student_number, 'status' => 'fail', 'reason' => 'No student number provided']);
    echo json_encode(['success' => false, 'message' => 'No student number provided.']);
    exit;
}

// Fetch student info with JOIN
$stmt = $conn->prepare("
    SELECT s.id, s.first_name, s.last_name, s.email, s.unique_code, s.student_number,
           s.middle_name, s.suffix, cg.year_level, cg.section
    FROM student s
    LEFT JOIN class_group cg ON s.class_group_id = cg.id
    WHERE s.student_number = ?
");
$stmt->bind_param("s", $student_number);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    log_email_attempt(['student_number' => $student_number, 'status' => 'fail', 'reason' => 'Student not found']);
    echo json_encode(['success' => false, 'message' => 'Student not found.']);
    $stmt->close();
    $conn->close();
    exit;
}
$student = $result->fetch_assoc();
$stmt->close();

if (empty($student['email']) || !filter_var($student['email'], FILTER_VALIDATE_EMAIL)) {
    log_email_attempt(['student_number' => $student['student_number'], 'status' => 'fail', 'reason' => 'Invalid or missing email', 'email' => $student['email']]);
    echo json_encode(['success' => false, 'message' => 'Invalid or missing email address.']);
    $conn->close();
    exit;
}

// Build QR filename
$last_name = preg_replace('/[^a-zA-Z0-9]/', '', substr($student['last_name'], 0, 20));
$year_level = preg_replace('/[^a-zA-Z0-9]/', '', substr($student['year_level'], 0, 10));
$section = preg_replace('/[^a-zA-Z0-9]/', '', substr($student['section'], 0, 10));
$student_number_clean = preg_replace('/[^a-zA-Z0-9]/', '', $student['student_number']);
$qr_filename = "{$last_name}-{$year_level}-{$section}-{$student_number_clean}.png";
$qr_path = "../../assets/qr_code/{$qr_filename}";

if (!file_exists($qr_path)) {
    log_email_attempt(['student_number' => $student['student_number'], 'status' => 'fail', 'reason' => 'QR code image not found', 'qr_path' => $qr_path]);
    echo json_encode(['success' => false, 'message' => 'QR code image not found.']);
    $conn->close();
    exit;
}

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = SMTP_PORT;
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress($student['email'], $student['first_name'] . ' ' . $student['last_name']);
    $mail->Subject = 'Your UBNHS Voting QR Code';
    $mail->isHTML(true);
    $mail->Body = "<p>Dear {$student['first_name']} {$student['last_name']},<br>Attached is your QR code for voting.<br>Student Number: {$student['student_number']}<br>Year Level: {$student['year_level']}<br>Section: {$student['section']}<br>Thank you!</p>";
    $mail->AltBody = "Dear {$student['first_name']} {$student['last_name']},\nAttached is your QR code for voting.\nStudent Number: {$student['student_number']}\nYear Level: {$student['year_level']}\nSection: {$student['section']}\nThank you!";
    $mail->addAttachment($qr_path, 'UBNHS-QR-Code.png');
    $mail->send();

    // Update status in DB
    $update = $conn->prepare("UPDATE student SET qr_email_sent = 1 WHERE id = ?");
    $update->bind_param("i", $student['id']);
    $update->execute();
    $update->close();

    log_email_attempt([
        'student_number' => $student['student_number'], 
        'status' => 'success', 
        'message' => 'Email sent', 
        'email' => $student['email']
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Email sent successfully.']);
} catch (Exception $e) {
    log_email_attempt([
        'student_number' => $student['student_number'], 
        'status' => 'fail', 
        'reason' => 'Mailer Error', 
        'error' => $e->getMessage()
    ]);
    
    echo json_encode(['success' => false, 'message' => 'Failed to send email: ' . $e->getMessage()]);
}

$conn->close();
?>