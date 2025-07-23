<?php
require_once(__DIR__ . '/../config/db_config.php');
header('Content-Type: application/json');

$sql = "SELECT s.student_number, s.first_name, s.middle_name, s.last_name, s.suffix, s.email, cg.year_level, cg.section
        FROM student s
        LEFT JOIN class_group cg ON s.class_group_id = cg.id
        WHERE s.deleted = 0
        ORDER BY s.id DESC";

$result = $conn->query($sql);
$students = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}
$conn->close();
echo json_encode(['success' => true, 'students' => $students]);
?>
