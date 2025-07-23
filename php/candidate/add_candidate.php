<?php
header('Content-Type: application/json');
require_once '../config/db_config.php';

// Get POST data
$committee = $_POST['committee'] ?? '';
$name = $_POST['name'] ?? '';
$picture = $_FILES['picture'] ?? null;

// Validate input
if (empty($committee) || empty($name) || !$picture) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

// Handle picture upload
$upload_dir = '../../assets/ubnhs-candidates/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}
$ext = pathinfo($picture['name'], PATHINFO_EXTENSION);
$filename = uniqid('cand_', true) . '.' . $ext;
$target_file = $upload_dir . $filename;

if (!move_uploaded_file($picture['tmp_name'], $target_file)) {
    echo json_encode(['success' => false, 'message' => 'Failed to upload picture.']);
    exit;
}

// Store relative path for DB
$picture_path = 'assets/ubnhs-candidates/' . $filename;

// Insert into database
$stmt = $conn->prepare("INSERT INTO candidate (committee, name, picture) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $committee, $name, $picture_path);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Candidate added successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add candidate.']);
}

$stmt->close();
$conn->close();
?>