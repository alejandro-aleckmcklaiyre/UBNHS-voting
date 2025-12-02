<?php
header('Content-Type: application/json');
require_once '../config/db_config.php';

$yearLevel = isset($_GET['year_level']) && $_GET['year_level'] !== 'all' ? $_GET['year_level'] : null;

$sql = "SELECT c.committee, c.name, c.id AS candidate_id, 
               COUNT(v.id) AS votes
        FROM candidate c
        LEFT JOIN votes v ON c.id = v.candidate_id"
        . ($yearLevel ? " AND v.year_level = '$yearLevel'" : "") . "
        WHERE c.deleted = 0
        GROUP BY c.id
        ORDER BY c.committee, c.name";
$result = $conn->query($sql);

$data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $committee = $row['committee'];
        if (!isset($data[$committee])) {
            $data[$committee] = [];
        }
        $data[$committee][] = [
            'name' => $row['name'],
            'votes' => (int)$row['votes']
        ];
    }
}

// 2. Total votes (all rows in votes table)
$totalVotes = $conn->query("SELECT COUNT(*) AS total FROM votes")->fetch_assoc()['total'];

// 3. Total candidates
$totalCandidates = $conn->query("SELECT COUNT(*) AS total FROM candidate WHERE deleted = 0")->fetch_assoc()['total'];

// 4. Total students (for turnout)
$totalStudents = $conn->query("SELECT COUNT(*) AS total FROM student WHERE deleted = 0")->fetch_assoc()['total'];

// 5. Unique voters (for turnout)
$uniqueVoters = $conn->query("SELECT COUNT(DISTINCT student_id) AS total FROM votes")->fetch_assoc()['total'];

// 6. Turnout percent
$turnoutPercent = $totalStudents > 0 ? round(($uniqueVoters / $totalStudents) * 100, 1) : 0;

// 7. Election status (customize as needed)
$status = "Live";

// 8. Optionally, add year-level breakdown for advanced filtering
$yearLevelVotes = [];
$yearSql = "SELECT v.year_level, COUNT(*) AS votes FROM votes v GROUP BY v.year_level";
$yearRes = $conn->query($yearSql);
if ($yearRes) {
    while ($row = $yearRes->fetch_assoc()) {
        $yearLevelVotes[$row['year_level']] = (int)$row['votes'];
    }
}

echo json_encode([
    'success' => true,
    'results' => $data,
    'totalVotes' => (int)$totalVotes,
    'totalCandidates' => (int)$totalCandidates,
    'turnoutPercent' => $turnoutPercent,
    'status' => $status,
    'yearLevelVotes' => $yearLevelVotes
]);
$conn->close();
?>