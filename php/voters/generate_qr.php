<?php
// generate_qr.php - QR Code Generation Module

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

/**
 * Log QR generation events
 */
function log_qr_event($data) {
    $log_path = __DIR__ . '/../../pages/voters_page/qr_logs.json';
    $logfile = __DIR__ . '/qr_generation.log';
    $existing_logs = [];
    
    if (file_exists($log_path)) {
        $existing_logs = json_decode(file_get_contents($log_path), true);
        if (!is_array($existing_logs)) $existing_logs = [];
    }
    
    $existing_logs[] = $data;
    file_put_contents($log_path, json_encode($existing_logs, JSON_PRETTY_PRINT));
    
    // Also log errors to qr_generation.log
    if (isset($data['error'])) {
        $log_entry = '[' . $data['timestamp'] . '] ERROR: ' . $data['error'];
        if (isset($data['details'])) {
            $log_entry .= ' | Details: ' . json_encode($data['details']);
        }
        file_put_contents($logfile, $log_entry . "\n", FILE_APPEND);
    }
}

/**
 * Create QR code with student details overlay
 */
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

/**
 * Add student details to QR code image
 */
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
        log_qr_event([
            'timestamp' => date('Y-m-d H:i:s'),
            'error' => 'QR Details Addition Error',
            'details' => $e->getMessage()
        ]);
        // Fall back to copying just the QR code
        return copy($qr_path, $output_path);
    }
}

/**
 * Improved QR Code generation with version compatibility
 */
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
        log_qr_event([
            'timestamp' => date('Y-m-d H:i:s'),
            'error' => 'Advanced QR Generation Error',
            'details' => $e->getMessage()
        ]);
        return false;
    } catch (Error $e) {
        error_log("Advanced QR Fatal Error: " . $e->getMessage());
        log_qr_event([
            'timestamp' => date('Y-m-d H:i:s'),
            'error' => 'Advanced QR Fatal Error',
            'details' => $e->getMessage()
        ]);
        return false;
    }
}

/**
 * Improved fallback QR Code generation using Google Charts API
 */
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
        log_qr_event([
            'timestamp' => date('Y-m-d H:i:s'),
            'error' => 'Simple QR Generation Error',
            'details' => $e->getMessage()
        ]);
        return false;
    }
}

/**
 * Main QR generation function with multiple fallbacks
 */
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
    
    // Log QR generation start
    log_qr_event([
        'timestamp' => date('Y-m-d H:i:s'),
        'step' => 'qr_generation_start',
        'file_path' => $file_path,
        'qr_library_available' => $GLOBALS['qr_library_available'],
        'gd_extension_loaded' => extension_loaded('gd')
    ]);
    
    // If student details are provided, create QR with details
    if ($student_details !== null) {
        $result = createQRWithDetails($unique_code, $file_path, $student_details);
    } else {
        // Otherwise, use the original method
        // Try advanced QR generation first
        if (generateQRCodeAdvanced($unique_code, $file_path)) {
            $result = true;
        } else {
            // Try simple fallback
            $result = generateSimpleQR($unique_code, $file_path);
        }
    }
    
    if ($result) {
        log_qr_event([
            'timestamp' => date('Y-m-d H:i:s'),
            'step' => 'qr_generation_successful',
            'file_path' => $file_path,
            'file_size' => file_exists($file_path) ? filesize($file_path) : 0
        ]);
    } else {
        log_qr_event([
            'timestamp' => date('Y-m-d H:i:s'),
            'step' => 'qr_generation_failed',
            'error' => 'All QR generation methods failed'
        ]);
    }
    
    return $result;
}

/**
 * Generate QR code file name based on student data
 */
function generateQRFileName($last_name, $year_level, $section, $student_number) {
    return sprintf(
        '%s-%s-%s-%s.png',
        preg_replace('/[^a-zA-Z0-9]/', '', substr($last_name, 0, 20)),
        preg_replace('/[^a-zA-Z0-9]/', '', substr($year_level, 0, 10)),
        preg_replace('/[^a-zA-Z0-9]/', '', substr($section, 0, 10)),
        preg_replace('/[^a-zA-Z0-9]/', '', $student_number)
    );
}

/**
 * Get QR code directory path
 */
function getQRDirectory() {
    return __DIR__ . '/../../assets/qr_code';
}

/**
 * Main function to generate QR code for a student
 */
function generateStudentQR($unique_code, $student_data) {
    $file_name = generateQRFileName(
        $student_data['last_name'],
        $student_data['year_level'],
        $student_data['section'],
        $student_data['student_number']
    );
    
    $qr_dir_path = getQRDirectory();
    $file_path = $qr_dir_path . DIRECTORY_SEPARATOR . $file_name;
    
    // Prepare student details for QR code
    $student_details = [
        'first_name' => $student_data['first_name'],
        'middle_name' => $student_data['middle_name'],
        'last_name' => $student_data['last_name'],
        'suffix' => $student_data['suffix'],
        'year_level' => $student_data['year_level'],
        'section' => $student_data['section'],
        'student_number' => $student_data['student_number']
    ];
    
    return generateQRCode($unique_code, $file_path, $student_details);
}
?>