<?php
require_once(__DIR__ . '/../config/db_config.php');
header('Content-Type: application/json');

// Require PHPSpreadsheet for Excel support
require_once(__DIR__ . '/../../vendor/autoload.php');
use PhpOffice\PhpSpreadsheet\IOFactory;

function sanitize($conn, $str) {
    return mysqli_real_escape_string($conn, trim($str));
}

function log_csv_event($data) {
    $logfile = __DIR__ . '/csv_upload.log';
    file_put_contents($logfile, json_encode($data, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error.']);
        exit;
    }

    $file = $_FILES['file'];
    $filename = $file['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $tmpPath = $file['tmp_name'];
    $students = [];

    if ($ext === 'csv') {
        $handle = fopen($tmpPath, 'r');
        if ($handle) {
            $header = fgetcsv($handle);
            while (($row = fgetcsv($handle)) !== false) {
                $students[] = array_combine($header, $row);
            }
            fclose($handle);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to open CSV file.']);
            exit;
        }
    } elseif ($ext === 'xlsx' || $ext === 'xls') {
        try {
            $spreadsheet = IOFactory::load($tmpPath);
            $sheet = $spreadsheet->getActiveSheet();
            $header = [];
            foreach ($sheet->getRowIterator() as $i => $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                $cells = [];
                foreach ($cellIterator as $cell) {
                    $cells[] = $cell->getValue();
                }
                if ($i === 1) {
                    $header = $cells;
                } else {
                    $students[] = array_combine($header, $cells);
                }
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to read Excel file: ' . $e->getMessage()]);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Unsupported file type.']);
        exit;
    }

    $inserted = 0;
    $errors = [];
    foreach ($students as $student) {
        // Map columns (adjust as needed)
        $student_number = sanitize($conn, $student['student_number'] ?? '');
        $first_name = sanitize($conn, $student['first_name'] ?? '');
        $middle_name = sanitize($conn, $student['middle_name'] ?? NULL);
        $last_name = sanitize($conn, $student['last_name'] ?? '');
        $suffix = sanitize($conn, $student['suffix'] ?? NULL);
        $email = sanitize($conn, $student['email'] ?? '');
        $year_level = sanitize($conn, $student['year_level'] ?? '');
        $section = sanitize($conn, $student['section'] ?? '');
        $status_id = intval($student['status_id'] ?? 1);
        $unique_code = bin2hex(random_bytes(8));
        if (empty($middle_name)) $middle_name = NULL;
        if (empty($suffix)) $suffix = NULL;

        // Lookup class_group_id
        $group_sql = "SELECT id FROM class_group WHERE year_level = ? AND section = ? LIMIT 1";
        $group_stmt = $conn->prepare($group_sql);
        $class_group_id = NULL;
        if ($group_stmt) {
            $group_stmt->bind_param('ss', $year_level, $section);
            $group_stmt->execute();
            $group_stmt->bind_result($class_group_id);
            $group_stmt->fetch();
            $group_stmt->close();
        }
        if (!$class_group_id) {
            $errors[] = ['student_number' => $student_number, 'error' => 'Invalid year level or section'];
            continue;
        }

        // Check for duplicate student number/email
        $dup_sql = "SELECT id FROM student WHERE student_number = ? OR email = ?";
        $dup_stmt = $conn->prepare($dup_sql);
        $exists = false;
        if ($dup_stmt) {
            $dup_stmt->bind_param('ss', $student_number, $email);
            $dup_stmt->execute();
            $dup_stmt->store_result();
            if ($dup_stmt->num_rows > 0) $exists = true;
            $dup_stmt->close();
        }
        if ($exists) {
            $errors[] = ['student_number' => $student_number, 'error' => 'Duplicate student number or email'];
            continue;
        }

        // Insert
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
                $inserted++;
            } else {
                $errors[] = ['student_number' => $student_number, 'error' => $stmt->error];
            }
            $stmt->close();
        } else {
            $errors[] = ['student_number' => $student_number, 'error' => $conn->error];
        }
    }
    log_csv_event(['timestamp' => date('Y-m-d H:i:s'), 'inserted' => $inserted, 'errors' => $errors]);
    echo json_encode(['success' => true, 'inserted' => $inserted, 'errors' => $errors]);
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
}
?>
