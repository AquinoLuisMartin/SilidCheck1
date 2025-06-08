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
    // Call the stored procedure to get quiz statistics
    $stmt = $conn->prepare("CALL GetQuizTakenCount(?)");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response = [
            'success' => true, 
            'count' => $row['taken_count'],
            'quiz_title' => $row['quiz_title'],
            'max_score' => $row['max_possible_score'],
            'average_score' => round($row['average_score'], 1),
            'lowest_score' => $row['lowest_score'],
            'highest_score' => $row['highest_score'],
            'average_time' => round($row['average_time_seconds'], 0)
        ];
    } else {
        // If no results found, return zero counts but success true
        $response = [
            'success' => true, 
            'count' => 0,
            'quiz_title' => null,
            'max_score' => 0,
            'average_score' => 0,
            'lowest_score' => 0,
            'highest_score' => 0,
            'average_time' => 0
        ];
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
