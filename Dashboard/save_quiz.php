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
// Remove quiz_status handling
$status = 'ongoing'; // Always set to ongoing or use your DB default

if ($title === '' || $subject === '' || !is_array($questions) || count($questions) === 0 || !is_array($answers) || count($answers) !== count($questions)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields or answers.']);
    exit;
}

// Insert quiz
$stmt = $conn->prepare('INSERT INTO quizzes (teacher_id, title, subject, status, created_at) VALUES (?, ?, ?, ?, NOW())');
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}
$stmt->bind_param('isss', $teacher_id, $title, $subject, $status);
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Failed to save quiz: ' . $stmt->error]);
    exit;
}
$quiz_id = $stmt->insert_id;
$stmt->close();

// Insert questions with correct answers
$stmt = $conn->prepare("INSERT INTO quiz_questions (quiz_id, question, correct_answer) VALUES (?, ?, ?)");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed (questions): ' . $conn->error]);
    exit;
}
for ($i = 0; $i < count($questions); $i++) {
    $q = trim($questions[$i]);
    $a = trim($answers[$i]);
    if ($q !== '' && $a !== '') {
        $stmt->bind_param('iss', $quiz_id, $q, $a);
        if (!$stmt->execute()) {
            echo json_encode(['success' => false, 'message' => 'Failed to save question: ' . $stmt->error]);
            exit;
        }
    }
}
$stmt->close();

echo json_encode(['success' => true, 'message' => 'Quiz saved successfully.']);
