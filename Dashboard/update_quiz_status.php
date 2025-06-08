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
    // Use UpdateQuizStatus stored procedure to verify ownership and update status in one call
    $stmt = $conn->prepare("CALL UpdateQuizStatus(?, ?, ?)");
    $stmt->bind_param("isi", $quiz_id, $status, $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        $row = $result->fetch_assoc();
        
        // The stored procedure returns success status and message
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
