<?php
// This migration adds a quiz_results table if it doesn't exist, for demo/testing purposes.
include 'db_connect.php';
$sql = "CREATE TABLE IF NOT EXISTS quiz_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    student_id INT NOT NULL,
    score INT,
    taken_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);
echo "quiz_results table checked/created.";
