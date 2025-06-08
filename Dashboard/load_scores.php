<?php
session_start();
include 'db_connect.php';
header('Content-Type: text/html; charset=UTF-8');

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    if (isset($_GET['performance'])) {
        header('Content-Type: application/json');
        echo json_encode(['labels' => [], 'scores' => []]);
    } else {
        echo '<tr><td colspan="5" style="text-align:center;">Not logged in.</td></tr>';
    }
    exit();
}

// Performance data for chart
if (isset($_GET['performance'])) {
    $student_id = intval($_SESSION['student_id']);
    
    // Use stored procedure for performance chart data
    $stmt = $conn->prepare("CALL GetStudentPerformanceChartData(?)");
    $stmt->bind_param('i', $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $labels = [];
    $scores = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Calculate percentage score
            $percentage = 0;
            if ($row['total_questions'] > 0) {
                $percentage = round(($row['score'] / $row['total_questions']) * 100);
            }
            
            // Truncate long quiz titles for better display
            $title = strlen($row['title']) > 15 ? substr($row['title'], 0, 15) . '...' : $row['title'];
            
            // Add to arrays
            $labels[] = $title;
            $scores[] = $percentage;
        }
        
        // Reverse arrays to show oldest to newest
        $labels = array_reverse($labels);
        $scores = array_reverse($scores);
    }
    
    // Close statement and clear result
    $stmt->close();
    
    header('Content-Type: application/json');
    echo json_encode(['labels' => $labels, 'scores' => $scores]);
    exit();
}

// Regular scores data for the table
// Your existing code for displaying quiz scores can remain the same or you can
// create another stored procedure for it
?>
