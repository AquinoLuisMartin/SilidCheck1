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
    if ($is_teacher) {
        // Get quizzes for teacher using stored procedure
        $stmt = $conn->prepare("CALL GetQuizzes(?, NULL, NULL)");
        $stmt->bind_param("i", $user_id);
    } else {
        // Get quizzes for student using stored procedure
        $stmt = $conn->prepare("CALL GetQuizzes(NULL, NULL, ?)");
        $stmt->bind_param("i", $user_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Find the specific quiz in the results
    $found_quiz = null;
    while ($row = $result->fetch_assoc()) {
        if ($row['quiz_id'] == $quiz_id) {
            $found_quiz = $row;
            break;
        }
    }
    
    // Close the statement to free resources before creating a new one
    $stmt->close();
    
    if ($found_quiz) {
        // If teacher, verify ownership
        if ($is_teacher && $found_quiz['teacher_id'] != $user_id) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }
        
        // Get question count using GetQuestions stored procedure
        $stmt = $conn->prepare("CALL GetQuestions(?)");
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        $questionResult = $stmt->get_result();
        $questionCount = $questionResult->num_rows;
        
        // Close the statement again
        $stmt->close();
        
        // Build response with quiz data
        $response['success'] = true;
        $response['quiz'] = [
            'id' => $quiz_id,
            'title' => $found_quiz['title'],
            'subject' => $found_quiz['subject_name'],
            'description' => $found_quiz['description'],
            'time_limit' => $found_quiz['time_limit'],
            'total_points' => $found_quiz['total_points'],
            'due_date' => $found_quiz['due_date'],
            'questions' => $questionCount
        ];
        
        // If student is taking quiz, include actual questions
        if (!$is_teacher && isset($_GET['take'])) {
            // Use GetQuizQuestions stored procedure to get questions
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
if (isset($stmt) && $stmt instanceof mysqli_stmt) {
    $stmt->close();
}

// Close connection
$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
