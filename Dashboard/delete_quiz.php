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
    // Use DeleteTitle stored procedure to verify teacher ownership and delete the quiz
    // This procedure handles both verification and deletion in one call
    $stmt = $conn->prepare("CALL DeleteTitle(?, ?)");
    $stmt->bind_param("ii", $quiz_id, $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        $row = $result->fetch_assoc();
        // Check if operation was successful based on stored procedure response
        if ($row['success']) {
            $response = ['success' => true, 'message' => $row['message']];
        } else {
            $response = ['success' => false, 'message' => $row['message']];
        }
    } else {
        $response = ['success' => false, 'message' => 'Error executing stored procedure'];
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
