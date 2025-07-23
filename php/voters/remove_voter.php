<?php
header('Content-Type: application/json');
require_once '../config/db_config.php';

$student_number = $_POST['student_number'] ?? '';

if (!$student_number) {
    echo json_encode(['success' => false, 'message' => 'No student number provided.']);
    exit;
}

$stmt = $conn->prepare("UPDATE student SET deleted = 1 WHERE student_number = ?");
$stmt->bind_param("s", $student_number);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Voter soft deleted.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to soft delete voter.']);
}
$stmt->close();
$conn->close();