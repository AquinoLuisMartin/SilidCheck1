<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    echo '<tr><td colspan="6" style="text-align:center;">Not logged in.</td></tr>';
    exit();
}

$student_id = intval($_SESSION['student_id']);
$subject = isset($_GET['subject']) ? $_GET['subject'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';

// Select the appropriate stored procedure based on filters
if (!empty($subject) && !empty($date_filter)) {
    // Both subject and date filter
    $stmt = $conn->prepare("CALL FilterStudentScoresBySubjectAndDate(?, ?, ?)");
    $stmt->bind_param('iss', $student_id, $subject, $date_filter);
} else if (!empty($subject)) {
    // Just subject filter
    $stmt = $conn->prepare("CALL FilterStudentScoresBySubject(?, ?)");
    $stmt->bind_param('is', $student_id, $subject);
} else if (!empty($date_filter)) {
    // Just date filter
    $stmt = $conn->prepare("CALL FilterStudentScoresByDate(?, ?)");
    $stmt->bind_param('is', $student_id, $date_filter);
} else {
    // No filters - show all scores
    $stmt = $conn->prepare("CALL GetStudentQuizScores(?)");
    $stmt->bind_param('i', $student_id);
}

$stmt->execute();
$result = $stmt->get_result();

// Generate HTML for results
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Calculate percentage score
        $total_items = $row['total_items'] ?? 1; // Prevent division by zero
        $percentage = round(($row['score'] / $total_items) * 100);
        
        // Determine score class for styling
        $scoreClass = '';
        if ($percentage >= 80) {
            $scoreClass = 'high-score';
        } else if ($percentage >= 60) {
            $scoreClass = 'medium-score';
        } else {
            $scoreClass = 'low-score';
        }
        
        // Format the date
        $date = date('M j, Y g:i A', strtotime($row['taken_at']));
        
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['title']) . '</td>';
        echo '<td>' . htmlspecialchars($row['subject']) . '</td>';
        echo '<td>' . htmlspecialchars($row['score']) . '</td>';
        echo '<td>' . htmlspecialchars($total_items) . '</td>';
        echo '<td><span class="score-value ' . $scoreClass . '">' . $percentage . '%</span></td>';
        echo '<td>' . htmlspecialchars($date) . '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="6" style="text-align:center;">No matching quiz scores found.</td></tr>';
}

$stmt->close();
?>