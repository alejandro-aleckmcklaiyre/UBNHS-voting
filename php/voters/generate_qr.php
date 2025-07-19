<?php
require_once(__DIR__ . '/../config/db_config.php');
require_once(__DIR__ . '/../../vendor/autoload.php');

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;

$qr_dir = realpath(__DIR__ . '/../../assets/qr_code');
if (!$qr_dir) {
    mkdir(__DIR__ . '/../../assets/qr_code', 0777, true);
    $qr_dir = realpath(__DIR__ . '/../../assets/qr_code');
}

$sql = "SELECT s.student_number, s.first_name, s.middle_name, s.last_name, s.suffix, s.unique_code, cg.year_level, cg.section
        FROM student s
        LEFT JOIN class_group cg ON s.class_group_id = cg.id";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $unique_code = $row['unique_code'];
        $student_number = $row['student_number'];
        $first_name = $row['first_name'];
        $middle_name = $row['middle_name'];
        $last_name = $row['last_name'];
        $suffix = $row['suffix'];
        $year_level = $row['year_level'];
        $section = $row['section'];

        // Compose display name
        $full_name = $first_name . ' ' . ($middle_name ? $middle_name . ' ' : '') . $last_name . ($suffix ? ' ' . $suffix : '');

        // File name: surname-year_level-section-student_number.png
        $file_name = sprintf(
            '%s-%s-%s-%s.png',
            preg_replace('/[^a-zA-Z0-9]/', '', $last_name),
            preg_replace('/[^a-zA-Z0-9]/', '', $year_level),
            preg_replace('/[^a-zA-Z0-9]/', '', $section),
            preg_replace('/[^a-zA-Z0-9]/', '', $student_number)
        );
        $file_path = $qr_dir . DIRECTORY_SEPARATOR . $file_name;

        // Generate QR code
        $qr = QrCode::create($unique_code)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->setSize(250);

        // Label: Name and Student Number
        $labelText = $full_name . "\n" . $student_number;
        $label = Label::create($labelText)
            ->setFont(new NotoSans(14))
            ->setTextColor(new \Endroid\QrCode\Color\Color(0, 0, 0));

        $writer = new PngWriter();
        $resultImg = $writer->write($qr, null, $label);

        // Save QR code image
        $resultImg->saveToFile($file_path);
    }
    echo json_encode(['success' => true, 'message' => 'QR codes generated successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'No students found.']);
}
$conn->close();
?>