<?php
session_start();
include 'db_connect.php';
header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['teacher_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit;
}

$teacher_id = $_SESSION['teacher_id'];
$title = isset($_POST['quiz_title']) ? trim($_POST['quiz_title']) : '';
$subject = isset($_POST['quiz_subject']) ? trim($_POST['quiz_subject']) : '';
$questions = isset($_POST['question']) ? $_POST['question'] : [];
$answers = isset($_POST['answer']) ? $_POST['answer'] : [];
$status = 'ongoing'; // Always set to ongoing or use your DB default

if ($title === '' || $subject === '' || !is_array($questions) || count($questions) === 0 || !is_array($answers) || count($answers) !== count($questions)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields or answers.']);
    exit;
}

// Use transaction to ensure all operations succeed or fail together
$conn->begin_transaction();

try {
    // Use AddTask stored procedure to create the quiz
    $stmt = $conn->prepare("CALL AddTask(?, ?, NULL, ?, NULL, ?)");
    $stmt->bind_param('ssis', $title, $subject, $teacher_id, $status);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result) {
        throw new Exception("Failed to create quiz: " . $conn->error);
    }
    
    $row = $result->fetch_assoc();
    $quiz_id = $row['quiz_id'];
    $stmt->close();
    
    // Use CreateQuestion stored procedure to add each question
    foreach ($questions as $i => $question_text) {
        $q = trim($question_text);
        $a = trim($answers[$i]);
        
        if ($q !== '' && $a !== '') {
            // Create empty options JSON structure for multiple choice questions
            $options = json_encode([
                'A' => '',
                'B' => '',
                'C' => '',
                'D' => ''
            ]);
            
            // Using CreateQuestion procedure which handles simple text questions
            $stmt = $conn->prepare("CALL CreateQuestion(?, ?, 'text', ?, ?)");
            $stmt->bind_param('isss', $quiz_id, $q, $options, $a);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    // If we got this far, commit the transaction
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Quiz saved successfully.', 'quiz_id' => $quiz_id]);
    
} catch (Exception $e) {
    // Roll back the transaction if any part failed
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} finally {
    // Make sure to close any open statements
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    
    // Close the database connection
    $conn->close();
}
?>
