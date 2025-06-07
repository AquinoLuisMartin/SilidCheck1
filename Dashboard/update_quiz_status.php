<?php
// update_quiz_status.php
session_start();
include 'db_connect.php';

// Verify teacher is logged in
if (!isset($_SESSION['teacher_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Check if required data is provided
if (!isset($_POST['quiz_id']) || !isset($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit;
}

$quiz_id = intval($_POST['quiz_id']);
$status = $_POST['status'];
$teacher_id = $_SESSION['teacher_id'];
$response = ['success' => false];

// Validate status
$valid_statuses = ['ongoing', 'published', 'closed'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    // First verify this quiz belongs to the logged-in teacher
    $stmt = $conn->prepare("SELECT id FROM quizzes WHERE id = ? AND teacher_id = ?");
    $stmt->bind_param("ii", $quiz_id, $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Quiz not found or access denied']);
        exit;
    }
    
    // Update the quiz status
    $stmt = $conn->prepare("UPDATE quizzes SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $quiz_id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $response = ['success' => true];
    } else {
        $response = ['success' => false, 'message' => 'No changes made'];
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
}

// Close statement if it exists
if (isset($stmt)) {
    $stmt->close();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
