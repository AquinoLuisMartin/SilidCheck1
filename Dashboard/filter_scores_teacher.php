<?php
session_start();
include 'db_connect.php';

// Security check - redirect if not logged in
if (!isset($_SESSION['teacher_id'])) {
    echo '<tr><td colspan="7" style="text-align:center;padding:20px;">Not logged in.</td></tr>';
    exit();
}

$teacher_id = intval($_SESSION['teacher_id']);

// Get filter parameters
$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
$student_name = isset($_GET['student']) ? trim($_GET['student']) : '';
$date_filter = isset($_GET['date']) ? trim($_GET['date']) : '';

// Call stored procedure based on provided filters
if ($quiz_id > 0 && !empty($student_name) && !empty($date_filter)) {
    // All three filters
    $stmt = $conn->prepare("CALL FilterScoresByAllParams(?, ?, ?, ?)");
    $stmt->bind_param('iiss', $teacher_id, $quiz_id, $student_name, $date_filter);
} elseif ($quiz_id > 0 && !empty($student_name)) {
    // Quiz and student filters
    $stmt = $conn->prepare("CALL FilterScoresByQuizAndStudent(?, ?, ?)");
    $stmt->bind_param('iis', $teacher_id, $quiz_id, $student_name);
} elseif ($quiz_id > 0 && !empty($date_filter)) {
    // Quiz and date filters
    $stmt = $conn->prepare("CALL FilterScoresByQuizAndDate(?, ?, ?)");
    $stmt->bind_param('iis', $teacher_id, $quiz_id, $date_filter);
} elseif (!empty($student_name) && !empty($date_filter)) {
    // Student and date filters
    $stmt = $conn->prepare("CALL FilterScoresByStudentAndDate(?, ?, ?)");
    $stmt->bind_param('iss', $teacher_id, $student_name, $date_filter);
} elseif ($quiz_id > 0) {
    // Only quiz filter
    $stmt = $conn->prepare("CALL FilterScoresByQuiz(?, ?)");
    $stmt->bind_param('ii', $teacher_id, $quiz_id);
} elseif (!empty($student_name)) {
    // Only student filter
    $stmt = $conn->prepare("CALL FilterScoresByStudent(?, ?)");
    $stmt->bind_param('is', $teacher_id, $student_name);
} elseif (!empty($date_filter)) {
    // Only date filter
    $stmt = $conn->prepare("CALL FilterScoresByDate(?, ?)");
    $stmt->bind_param('is', $teacher_id, $date_filter);
} else {
    // No filters - default view
    $stmt = $conn->prepare("CALL GetRecentQuizScores(?)");
    $stmt->bind_param('i', $teacher_id);
}

$stmt->execute();
$result = $stmt->get_result();

// Generate HTML output
if ($result && $result->num_rows > 0) {
    while ($score = $result->fetch_assoc()) {
        $percentage = round(($score['score'] / $score['total_items']) * 100);
        $scoreClass = '';
        
        if ($percentage >= 80) {
            $scoreClass = 'score-high';
        } else if ($percentage >= 60) {
            $scoreClass = 'score-medium';
        } else {
            $scoreClass = 'score-low';
        }
        
        echo '<tr>';
        echo '<td>' . htmlspecialchars($score['student_name']) . '</td>';
        echo '<td>' . htmlspecialchars($score['quiz_title']) . '</td>';
        echo '<td>' . htmlspecialchars($score['subject']) . '</td>';
        echo '<td>' . $score['score'] . '</td>';
        echo '<td>' . $score['total_items'] . '</td>';
        echo '<td class="' . $scoreClass . '">' . $percentage . '%</td>';
        echo '<td>' . date('M j, Y g:i A', strtotime($score['taken_at'])) . '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="7" style="text-align:center;padding:20px;">No matching quiz scores found.</td></tr>';
}

$stmt->close();
?>