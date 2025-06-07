<?php
// DEBUG: Show all errors for troubleshooting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db_connect.php';

// Verify user is logged in
if (!isset($_SESSION['teacher_id']) && !isset($_SESSION['student_id'])) {
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
$is_teacher = isset($_SESSION['teacher_id']);
$user_id = $is_teacher ? $_SESSION['teacher_id'] : $_SESSION['student_id'];

try {
    // Get basic quiz info
    $sql = "SELECT title, subject, description, time_limit, status, teacher_id FROM quizzes WHERE id = ?";
    
    // For students, only allow access to published quizzes
    if (!$is_teacher) {
        $sql .= " AND status = 'published'";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $quiz = $result->fetch_assoc();
        
        // If teacher, verify ownership
        if ($is_teacher && $quiz['teacher_id'] != $user_id) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }
        
        // Get question count
        $stmt = $conn->prepare("SELECT COUNT(*) as question_count FROM quiz_questions WHERE quiz_id = ?");
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        $countResult = $stmt->get_result();
        $questionCount = $countResult->fetch_assoc()['question_count'];
        
        // For students taking a quiz, we'd need to get actual questions
        // For teachers/viewing, just return metadata
        $response['success'] = true;
        $response['quiz'] = [
            'id' => $quiz_id,
            'title' => $quiz['title'],
            'subject' => $quiz['subject'],
            'description' => $quiz['description'],
            'time_limit' => $quiz['time_limit'],
            'status' => $quiz['status'],
            'questions' => $questionCount
        ];
        
        // If student is taking quiz, include actual questions
        if (!$is_teacher && $quiz['status'] == 'published' && isset($_GET['take'])) {
            // Use GetQuizQuestions stored procedure
            $stmt = $conn->prepare("CALL GetQuizQuestions(?)");
            $stmt->bind_param("i", $quiz_id);
            $stmt->execute();
            $questionsResult = $stmt->get_result();
            
            $questions = [];
            while ($row = $questionsResult->fetch_assoc()) {
                // For students, don't include correct answer
                $questions[] = [
                    'id' => $row['id'],
                    'question_text' => $row['question_text'],
                    'question_type' => $row['question_type'],
                    'options' => [
                        'A' => $row['option_a'],
                        'B' => $row['option_b'],
                        'C' => $row['option_c'],
                        'D' => $row['option_d']
                    ]
                ];
            }
            
            $response['quiz']['questions'] = $questions;
        }
    } else {
        $response['message'] = 'Quiz not found or access denied';
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
