<?php
// submit_quiz.php
session_start();
include 'db_connect.php';

// Verify student is logged in
if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Check if required data is provided
if (!isset($_POST['quiz_id']) || !isset($_POST['answer'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit;
}

$quiz_id = intval($_POST['quiz_id']);
$student_id = $_SESSION['student_id'];
$answers = $_POST['answer'];
$response = ['success' => false];

try {
    // Use GetQuizQuestions stored procedure to get correct answers for grading
    $stmt = $conn->prepare("CALL GetQuizQuestions(?)");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $questions = [];
    while ($row = $result->fetch_assoc()) {
        $questions[$row['id']] = $row;
    }
    
    // Close the statement before executing another query
    $stmt->close();
    
    // Grade the quiz
    $score = 0;
    $total = count($questions);
    
    if ($total > 0) {
        $question_ids = array_keys($questions);
        foreach ($answers as $index => $answer) {
            // If answer matches correct_answer, increment score
            if (isset($question_ids[$index]) && 
                strtolower(trim($answer)) === strtolower(trim($questions[$question_ids[$index]]['correct_answer']))) {
                $score++;
            }
        }
        
        // Calculate time taken (would need to be tracked client-side and sent)
        $time_taken = isset($_POST['time_taken']) ? intval($_POST['time_taken']) : 0;
        
        // Save JSON encoded answers for reference
        $answers_json = json_encode($answers);
        
        // Use AddResult stored procedure to save the result
        $stmt = $conn->prepare("CALL AddResult(?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiii", $quiz_id, $student_id, $score, $total, $time_taken);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result) {
            $row = $result->fetch_assoc();
            $result_id = $row['result_id'];
        }
        
        $response['success'] = true;
        $response['score'] = $score;
        $response['total'] = $total;
        $response['percentage'] = round(($score / $total) * 100);
    } else {
        $response['message'] = 'No questions found for this quiz';
    }
} catch (Exception $e) {
    $response['message'] = "Error: " . $e->getMessage();
}

// Close statement if it exists
if (isset($stmt)) {
    $stmt->close();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
