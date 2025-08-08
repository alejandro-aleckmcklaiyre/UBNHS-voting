<?php
header('Content-Type: application/json');
require_once '../config/db_config.php';
session_start();

$logFile = __DIR__ . '/student_votes.log';

function log_vote_action($message) {
    global $logFile;
    file_put_contents($logFile, $message . PHP_EOL, FILE_APPEND);
}

// Get student info (adjust as needed)
$student_id = $_SESSION['student_id'] ?? null;
$year_level = $_SESSION['year_level'] ?? null;

$data = json_decode(file_get_contents('php://input'), true);

if (!$student_id || !$year_level) {
    $logMsg = date('Y-m-d H:i:s') . " | ERROR | Missing student info | SESSION: " . json_encode($_SESSION) . " | DATA: " . json_encode($data);
    log_vote_action($logMsg);
    echo json_encode(['success' => false, 'message' => 'Student info missing.']);
    exit;
}

if (!isset($data['votes']) || !is_array($data['votes'])) {
    $logMsg = date('Y-m-d H:i:s') . " | ERROR | No votes submitted | STUDENT_ID: $student_id | YEAR_LEVEL: $year_level | DATA: " . json_encode($data);
    log_vote_action($logMsg);
    echo json_encode(['success' => false, 'message' => 'No votes submitted.']);
    exit;
}

$votesLogged = [];
foreach ($data['votes'] as $candidateName) {
    // Get candidate ID and committee
    $stmt = $conn->prepare("SELECT id, committee FROM candidate WHERE name = ? AND deleted = 0 LIMIT 1");
    $stmt->bind_param("s", $candidateName);
    $stmt->execute();
    $stmt->bind_result($candidate_id, $committee);
    if ($stmt->fetch()) {
        $stmt->close();
        // Insert vote
        $insert = $conn->prepare("INSERT INTO votes (student_id, candidate_id, committee, year_level) VALUES (?, ?, ?, ?)");
        $insert->bind_param("iiss", $student_id, $candidate_id, $committee, $year_level);
        $insert->execute();
        $insert->close();
        $votesLogged[] = [
            'candidate_id' => $candidate_id,
            'candidate_name' => $candidateName,
            'committee' => $committee
        ];
    } else {
        $stmt->close();
    }
}

// Log successful vote
$logMsg = date('Y-m-d H:i:s') . " | VOTE | STUDENT_ID: $student_id | YEAR_LEVEL: $year_level | VOTES: " . json_encode($votesLogged);
log_vote_action($logMsg);

echo json_encode(['success' => true, 'message' => 'Votes submitted successfully.']);
$conn->close();
?>