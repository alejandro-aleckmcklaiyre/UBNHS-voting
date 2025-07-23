<?php
header('Content-Type: application/json');
require_once '../config/db_config.php';

$id = $_POST['id'] ?? '';

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'No candidate ID provided.']);
    exit;
}

$stmt = $conn->prepare("UPDATE candidate SET deleted = 1 WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Candidate soft deleted.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to soft delete candidate.']);
}
$stmt->close();
$conn->close();
?>