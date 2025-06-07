<?php
session_start();
include 'db_connect.php';
header('Content-Type: text/html; charset=UTF-8');

if (isset($_GET['performance']) && $_GET['performance'] == '1') {
    // Dynamically get all subjects the student has taken quizzes in
    header('Content-Type: application/json');
    $student_id = isset($_SESSION['student_id']) ? intval($_SESSION['student_id']) : 0;
    $subjects = [];
    $stmt = $conn->prepare('SELECT DISTINCT q.subject FROM quiz_results qr JOIN quizzes q ON qr.quiz_id = q.id WHERE qr.student_id = ?');
    $stmt->bind_param('i', $student_id);
    $stmt->execute();
    $stmt->bind_result($subject);
    while ($stmt->fetch()) {
        $subjects[] = $subject;
    }
    $stmt->close();
    // Always show all possible subjects, even if the student has no quiz for them
    $allSubjects = [
        'Mathematics',
        'Science',
        'English'
    ];
    $labels = $allSubjects;
    $scores = [];
    foreach ($allSubjects as $subject) {
        $sql = "SELECT AVG(score/total*100) as avg_score FROM quiz_results qr JOIN quizzes q ON qr.quiz_id = q.id WHERE qr.student_id = ? AND q.subject = ? AND qr.total > 0";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('is', $student_id, $subject);
        $stmt->execute();
        $stmt->bind_result($avg_score);
        $stmt->fetch();
        $scores[] = $avg_score !== null ? round($avg_score, 2) : 0;
        $stmt->close();
    }
    echo json_encode(['labels' => $labels, 'scores' => $scores]);
    exit;
}

if (!isset($_SESSION['student_id'])) {
    echo '<tr><td colspan="5" style="padding:12px 8px;text-align:center;">Not logged in.</td></tr>';
    exit;
}
$student_id = intval($_SESSION['student_id']);
$sql = "SELECT qr.*, q.title, q.subject, (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) as total_items FROM quiz_results qr JOIN quizzes q ON qr.quiz_id = q.id WHERE qr.student_id = $student_id ORDER BY qr.taken_at DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<tr style="border-bottom:1px solid #e0f7fa;">';
        echo '<td style="padding:10px 8px;">' . htmlspecialchars($row['title']) . '</td>';
        echo '<td style="padding:10px 8px;">' . htmlspecialchars($row['subject']) . '</td>';
        echo '<td style="padding:10px 8px;">' . htmlspecialchars($row['score']) . '</td>';
        echo '<td style="padding:10px 8px;">' . htmlspecialchars($row['total_items']) . '</td>';
        echo '<td style="padding:10px 8px;">' . htmlspecialchars($row['taken_at']) . '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="5" style="padding:12px 8px;text-align:center;">No quiz scores found.</td></tr>';
}
?>
