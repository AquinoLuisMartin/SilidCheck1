<?php
// delete_quiz.php
session_start();
include 'db_connect.php';

// Verify teacher is logged in
if (!isset($_SESSION['teacher_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Check if quiz_id is provided
if (!isset($_POST['quiz_id'])) {
    echo json_encode(['success' => false, 'message' => 'No quiz ID provided']);
    exit;
}

$quiz_id = intval($_POST['quiz_id']);
$teacher_id = $_SESSION['teacher_id'];
$response = ['success' => false];

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
    
    // Use DeleteTask stored procedure to delete the quiz and related records
    $stmt = $conn->prepare("CALL DeleteTask(?)");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        $row = $result->fetch_assoc();
        $response = ['success' => true, 'rows_deleted' => $row['rows_deleted']];
    } else {
        $response = ['success' => true]; // Assume success even if no rows returned
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
