<?php
session_start();
include 'db_connect.php';

// Verify teacher is logged in
if (!isset($_SESSION['teacher_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$teacher_id = $_SESSION['teacher_id'];
$output = '';

try {
    // Call the GetTeacherQuizzes stored procedure
    $stmt = $conn->prepare("CALL GetTeacherQuizzes(?)");
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        while ($quiz = $result->fetch_assoc()) {
            $output .= '<div class="quiz-card">
                <h3>' . htmlspecialchars($quiz['title']) . ' <span class="quiz-meta">(' . htmlspecialchars($quiz['subject']) . ')</span></h3>
                <div class="quiz-info"><b>Questions:</b> ' . intval($quiz['question_count']) . '</div>
                <div class="quiz-info"><b>Students Taken:</b> ' . intval($quiz['students_taken']) . '</div>
                <div class="quiz-info"><b>Created:</b> ' . htmlspecialchars($quiz['created_at']) . '</div>
                <div class="quiz-actions">
                    <button class="delete-quiz-btn" data-quizid="' . intval($quiz['id']) . '">Delete</button>
                    <select class="quiz-status-select" data-quizid="' . intval($quiz['id']) . '">
                        <option value="ongoing"' . ($quiz['status'] == 'ongoing' ? ' selected' : '') . '>Ongoing</option>
                        <option value="published"' . ($quiz['status'] == 'published' ? ' selected' : '') . '>Published</option>
                        <option value="closed"' . ($quiz['status'] == 'closed' ? ' selected' : '') . '>Closed</option>
                    </select>
                    <button class="view-quiz-btn" data-quizid="' . intval($quiz['id']) . '">View</button>
                </div>
            </div>';
        }
    } else {
        $output = '<div class="no-quizzes-message">No quizzes found. Create a quiz to get started.</div>';
    }
} catch (Exception $e) {
    $output = '<div class="error-message">Error loading quizzes: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

// Close statement if it exists
if (isset($stmt)) {
    $stmt->close();
}

// Return HTML output
echo $output;
?>
