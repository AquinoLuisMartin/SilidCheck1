<?php
session_start();
include 'db_connect.php';

// Security check
if (!isset($_SESSION['teacher_id'])) {
    echo '<div style="text-align:center;padding:30px;"><h3>Error</h3><p>Not authorized.</p></div>';
    exit();
}

// Get student ID from request
$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($student_id <= 0) {
    echo '<div style="text-align:center;padding:30px;"><h3>Error</h3><p>Invalid student ID.</p></div>';
    exit();
}

// Get student profile using stored procedure
$stmt = $conn->prepare("CALL GetStudentProfileById(?)");
$stmt->bind_param('i', $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $student = $result->fetch_assoc()) {
    // Get initials for avatar
    $initials = strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1));
    
    // Output student profile
    echo '<div class="student-profile-header">';
    echo '<div class="student-profile-avatar">' . $initials . '</div>';
    echo '<div class="student-profile-info">';
    echo '<h2>' . htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) . '</h2>';
    echo '<p>Student ID: ' . htmlspecialchars($student['student_id']) . '</p>';
    echo '<p>Email: ' . htmlspecialchars($student['email']) . '</p>';
    echo '<p>Year Level: ' . htmlspecialchars($student['year_level']) . '</p>';
    echo '</div>';
    echo '</div>';
    
    // Get student stats
    $stmt->close();
    $conn->next_result();
    
    $stmt = $conn->prepare("CALL GetStudentStats(?)");
    $stmt->bind_param('i', $student_id);
    $stmt->execute();
    $statsResult = $stmt->get_result();
    
    if ($statsResult && $stats = $statsResult->fetch_assoc()) {
        echo '<div class="student-stats">';
        echo '<div class="student-stat-card">';
        echo '<div class="value">' . number_format($stats['avg_score'], 1) . '%</div>';
        echo '<div class="label">Average Score</div>';
        echo '</div>';
        
        echo '<div class="student-stat-card">';
        echo '<div class="value">' . number_format($stats['highest_score'], 1) . '%</div>';
        echo '<div class="label">Highest Score</div>';
        echo '</div>';
        
        echo '<div class="student-stat-card">';
        echo '<div class="value">' . $stats['quizzes_taken'] . '</div>';
        echo '<div class="label">Quizzes Taken</div>';
        echo '</div>';
        
        echo '<div class="student-stat-card">';
        echo '<div class="value">' . date('M j, Y', strtotime($stats['last_active'])) . '</div>';
        echo '<div class="label">Last Active</div>';
        echo '</div>';
        echo '</div>';
    }
    
    // Get recent quiz scores
    $stmt->close();
    $conn->next_result();
    
    $stmt = $conn->prepare("CALL GetStudentRecentScores(?)");
    $stmt->bind_param('i', $student_id);
    $stmt->execute();
    $scoresResult = $stmt->get_result();
    
    echo '<h3 style="margin-top:30px;">Recent Quiz Scores</h3>';
    
    if ($scoresResult && $scoresResult->num_rows > 0) {
        echo '<table class="student-scores-table">';
        echo '<thead><tr>';
        echo '<th>Quiz</th>';
        echo '<th>Subject</th>';
        echo '<th>Score</th>';
        echo '<th>Total</th>';
        echo '<th>Percentage</th>';
        echo '<th>Date</th>';
        echo '</tr></thead><tbody>';
        
        while ($score = $scoresResult->fetch_assoc()) {
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
            echo '<td>' . htmlspecialchars($score['quiz_title']) . '</td>';
            echo '<td>' . htmlspecialchars($score['subject']) . '</td>';
            echo '<td>' . $score['score'] . '</td>';
            echo '<td>' . $score['total_items'] . '</td>';
            echo '<td class="' . $scoreClass . '">' . $percentage . '%</td>';
            echo '<td>' . date('M j, Y', strtotime($score['taken_at'])) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    } else {
        echo '<p>No quiz scores available for this student.</p>';
    }
    
} else {
    echo '<div style="text-align:center;padding:30px;"><h3>Error</h3><p>Student not found.</p></div>';
}

$stmt->close();
?>