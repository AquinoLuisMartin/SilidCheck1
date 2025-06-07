<?php
// quiz_taken_count.php
session_start();
include 'db_connect.php';

// Verify teacher is logged in
if (!isset($_SESSION['teacher_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Check if quiz_id is provided
if (!isset($_GET['quiz_id'])) {
    echo json_encode(['success' => false, 'message' => 'No quiz ID provided']);
    exit;
}

$quiz_id = intval($_GET['quiz_id']);
$response = ['success' => false];

try {
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT student_id) as taken_count FROM quiz_results WHERE quiz_id = ?");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        $row = $result->fetch_assoc();
        $response = ['success' => true, 'count' => $row['taken_count']];
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => "Error: " . $e->getMessage()];
}

// Close statement if it exists
if (isset($stmt)) {
    $stmt->close();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
