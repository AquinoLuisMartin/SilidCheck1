<?php
session_start();
include 'db_connect.php';

// Check if user is authenticated
if (!isset($_SESSION['teacher_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

// Check if required fields are provided
if (!isset($_POST['quiz_title']) || !isset($_POST['quiz_subject']) || 
    !isset($_POST['question']) || !isset($_POST['answer'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

// Get form data
$teacher_id = $_SESSION['teacher_id'];
$title = trim($_POST['quiz_title']);
$subject = trim($_POST['quiz_subject']);
$questions = $_POST['question'];
$answers = $_POST['answer'];

// Validate input
if (empty($title) || empty($subject) || empty($questions) || empty($answers)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

if (count($questions) !== count($answers)) {
    echo json_encode(['success' => false, 'message' => 'Questions and answers count mismatch']);
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Create quiz using stored procedure
    $stmt = $conn->prepare("CALL CreateQuiz(?, ?, ?)");
    $stmt->bind_param('iss', $teacher_id, $title, $subject);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        $row = $result->fetch_assoc();
        $quiz_id = $row['quiz_id'];
    } else {
        $quiz_id = $conn->insert_id;
    }
    
    $stmt->close();
    $conn->next_result(); // Clear the previous result
    
    // Insert questions using stored procedure
    for ($i = 0; $i < count($questions); $i++) {
        $question_text = trim($questions[$i]);
        $answer_text = trim($answers[$i]);
        
        if (!empty($question_text) && !empty($answer_text)) {
            $stmt = $conn->prepare("CALL AddQuizQuestion(?, ?, ?)");
            $stmt->bind_param('iss', $quiz_id, $question_text, $answer_text);
            $stmt->execute();
            $stmt->close();
            $conn->next_result(); // Clear the previous result
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'quiz_id' => $quiz_id]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error creating quiz: ' . $e->getMessage()]);
}

// Close connection
$conn->close();
?>
