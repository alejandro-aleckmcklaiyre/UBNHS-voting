<?php
header('Content-Type: application/json');
require_once '../config/db_config.php';

$result = $conn->query("SELECT id, committee, name, picture 
                FROM candidate 
                WHERE deleted = 0 
                ORDER BY committee, name");
$candidates = [];
while ($row = $result->fetch_assoc()) {
    $candidates[] = $row;
}
echo json_encode(['success' => true, 'candidates' => $candidates]);
$conn->close();
?>