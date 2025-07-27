<?php
header('Content-Type: application/json');
require_once '../config/db_config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['votes']) || !is_array($data['votes'])) {
    echo json_encode(['success' => false, 'message' => 'No votes submitted.']);
    exit;
}

foreach ($data['votes'] as $candidateName) {
    $stmt = $conn->prepare("UPDATE candidate SET votes = votes + 1 WHERE name = ?");
    $stmt->bind_param("s", $candidateName);
    $stmt->execute();
    $stmt->close();
}

echo json_encode(['success' => true, 'message' => 'Votes submitted successfully.']);
$conn->close();
?>