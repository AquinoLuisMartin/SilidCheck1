<?php
<?php
session_start();
include 'db_connect.php';

// Verify teacher is logged in
if (!isset($_SESSION['teacher_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$teacher_id = $_SESSION['teacher_id'];

// Check if all required data is present
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['subject'], $_POST['questions'])) {
    $title = trim($_POST['title']);
    $subject = trim($_POST['subject']);
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $time_limit = isset($_POST['time_limit']) ? intval($_POST['time_limit']) : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : 'ongoing';
    $questions = json_decode($_POST['questions'], true);
    
    if (!is_array($questions) || count($questions) === 0) {
        echo json_encode(['success' => false, 'message' => 'No questions provided']);
        exit;
    }
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Use AddTask stored procedure to create the quiz
        $stmt = $conn->prepare("CALL AddTask(?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiis", $title, $subject, $description, $teacher_id, $time_limit, $status);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $quiz_id = $row['quiz_id'];
            $stmt->close();
            
            // Add all questions using AddQuestion stored procedure
            foreach ($questions as $question) {
                if (!isset($question['question']) || !isset($question['answer'])) {
                    throw new Exception("Invalid question format");
                }
                
                $question_text = trim($question['question']);
                $correct_answer = trim($question['answer']);
                $question_type = isset($question['type']) ? $question['type'] : 'text';
                
                // Optional multiple choice options
                $option_a = isset($question['options']['A']) ? $question['options']['A'] : '';
                $option_b = isset($question['options']['B']) ? $question['options']['B'] : '';
                $option_c = isset($question['options']['C']) ? $question['options']['C'] : '';
                $option_d = isset($question['options']['D']) ? $question['options']['D'] : '';
                
                // Use the AddQuestion stored procedure
                $stmt = $conn->prepare("CALL AddQuestion(?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isssssss", $quiz_id, $question_text, $question_type, $option_a, $option_b, $option_c, $option_d, $correct_answer);
                $stmt->execute();
                $stmt->close();
            }
            
            // Commit transaction
            $conn->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Quiz created successfully',
                'quiz_id' => $quiz_id
            ]);
        } else {
            throw new Exception("Failed to create quiz");
        }
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
}
?>