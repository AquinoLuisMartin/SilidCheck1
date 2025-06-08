<?php
session_start();
include 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <style>
        /* Global styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
            background-color: #f5f8fa;
            margin: 0;
            padding: 20px;
        }

        /* Container */
        .student-profile-container {
            max-width: 1000px;
            margin: 0 auto;
        }

        /* Profile Header */
        .student-profile-header {
            display: flex;
            align-items: center;
            gap: 25px;
            background-color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(44,196,182,0.10);
        }

        .student-profile-avatar {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #2ec4b6;
            color: white;
            font-size: 28px;
            font-weight: bold;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .student-profile-info {
            flex-grow: 1;
        }

        .student-profile-info h2 {
            margin-top: 0;
            margin-bottom: 10px;
            color: #2a7a66;
            font-size: 24px;
        }

        .student-profile-info p {
            margin: 8px 0;
            color: #555;
            font-size: 15px;
        }

        /* Stats Section */
        .student-profile-stats {
            background-color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(44,196,182,0.10);
        }

        .student-profile-stats h3 {
            margin-top: 0;
            color: #2a7a66;
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0f7fa;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
        }

        .stat-card {
            text-align: center;
            padding: 15px;
            border-radius: 8px;
            background-color: #f8fdfc;
            border: 1px solid #e0f7fa;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(44,196,182,0.15);
        }

        .stat-title {
            font-size: 14px;
            color: #555;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #2ec4b6;
        }

        /* Recent Quiz Scores */
        .student-recent-scores {
            background-color: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(44,196,182,0.10);
            margin-bottom: 25px;
        }

        .student-recent-scores h3 {
            margin-top: 0;
            color: #2a7a66;
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0f7fa;
        }

        .scores-table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
        }

        .scores-table th {
            background-color: #e6f9ed;
            color: #2a7a66;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #b7e4c7;
        }

        .scores-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0f7fa;
        }

        .scores-table tr:last-child td {
            border-bottom: none;
        }

        .scores-table tr:hover {
            background-color: #f8fdfc;
        }

        /* Score Colors */
        .score-high {
            color: #28a745;
            font-weight: bold;
        }

        .score-medium {
            color: #fd7e14;
            font-weight: bold;
        }

        .score-low {
            color: #dc3545;
            font-weight: bold;
        }

        /* Error Messages */
        .error-message {
            text-align: center;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(44,196,182,0.10);
            color: #666;
        }

        .error-message h3 {
            color: #dc3545;
            margin-top: 0;
        }
        
        /* Message styles */
        .no-data-message {
            text-align: center;
            padding: 20px;
            background-color: #f8fdfc;
            border-radius: 8px;
            color: #666;
            border: 1px dashed #b2dfdb;
        }
        
        /* Print styles */
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            
            .student-profile-header,
            .student-profile-stats,
            .student-recent-scores {
                box-shadow: none;
                border: 1px solid #ddd;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="student-profile-container">
    <?php
    // Security check
    if (!isset($_SESSION['teacher_id'])) {
        echo '<div class="error-message"><h3>Error</h3><p>Not authorized.</p></div>';
        exit();
    }

    // Get student ID from request
    $student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($student_id <= 0) {
        echo '<div class="error-message"><h3>Error</h3><p>Invalid student ID.</p></div>';
        exit();
    }

    try {
        // Get student profile using stored procedure
        $stmt = $conn->prepare("CALL GetStudentProfileById(?)");
        $stmt->bind_param('i', $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $student = $result->fetch_assoc()) {
            // Get initials for avatar
            $initials = strtoupper(substr($student['name'], 0, 2));
            
            // Output student profile
            echo '<div class="student-profile-header">';
            echo '<div class="student-profile-avatar">' . $initials . '</div>';
            echo '<div class="student-profile-info">';
            echo '<h2>' . htmlspecialchars($student['name']) . '</h2>';
            echo '<p>Student ID: ' . htmlspecialchars($student['id']) . '</p>';
            echo '<p>Email: ' . htmlspecialchars($student['email']) . '</p>';
            echo '<p>Grade: ' . htmlspecialchars($student['grade']) . '</p>';
            echo '</div>';
            echo '</div>';
            
            // Clear result
            $stmt->close();
            $conn->next_result();
            
            // Get student stats
            echo '<div class="student-profile-stats">';
            echo '<h3>Student Performance</h3>';
            
            $stmt = $conn->prepare("CALL GetStudentStats(?)");
            $stmt->bind_param('i', $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $stats = $result->fetch_assoc()) {
                echo '<div class="stats-container">';
                
                // Average Score
                echo '<div class="stat-card">';
                echo '<div class="stat-title">Average Score</div>';
                echo '<div class="stat-value">' . number_format($stats['avg_score'], 1) . '%</div>';
                echo '</div>';
                
                // Highest Score
                echo '<div class="stat-card">';
                echo '<div class="stat-title">Highest Score</div>';
                echo '<div class="stat-value">' . number_format($stats['highest_score'], 1) . '%</div>';
                echo '</div>';
                
                // Quizzes Taken
                echo '<div class="stat-card">';
                echo '<div class="stat-title">Quizzes Taken</div>';
                echo '<div class="stat-value">' . $stats['quizzes_taken'] . '</div>';
                echo '</div>';
                
                // Last Activity
                echo '<div class="stat-card">';
                echo '<div class="stat-title">Last Activity</div>';
                echo '<div class="stat-value">' . ($stats['last_active'] ? date('M j, Y', strtotime($stats['last_active'])) : 'N/A') . '</div>';
                echo '</div>';
                
                echo '</div>'; // End stats-container
            } else {
                echo '<div class="no-data-message">No statistics available for this student.</div>';
            }
            echo '</div>'; // End student-profile-stats
            
            // Clear result
            $stmt->close();
            $conn->next_result();
            
            // Get recent quiz scores
            echo '<div class="student-recent-scores">';
            echo '<h3>Recent Quiz Scores</h3>';
            
            $stmt = $conn->prepare("CALL GetStudentRecentScores(?)");
            $stmt->bind_param('i', $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                echo '<table class="scores-table">';
                echo '<thead>';
                echo '<tr>';
                echo '<th>Quiz Title</th>';
                echo '<th>Subject</th>';
                echo '<th>Score</th>';
                echo '<th>Percentage</th>';
                echo '<th>Date Taken</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';
                
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
                    echo '<td>' . htmlspecialchars($score['quiz_title']) . '</td>';
                    echo '<td>' . htmlspecialchars($score['subject']) . '</td>';
                    echo '<td>' . $score['score'] . '/' . $score['total_items'] . '</td>';
                    echo '<td class="' . $scoreClass . '">' . $percentage . '%</td>';
                    echo '<td>' . date('M j, Y g:i A', strtotime($score['taken_at'])) . '</td>';
                    echo '</tr>';
                }
                
                echo '</tbody>';
                echo '</table>';
            } else {
                echo '<div class="no-data-message">No quiz scores available for this student.</div>';
            }
            
            echo '</div>'; // End student-recent-scores
            
        } else {
            echo '<div class="error-message"><h3>Error</h3><p>Student not found.</p></div>';
        }
    } catch (Exception $e) {
        echo '<div class="error-message"><h3>Error</h3><p>Could not load student profile: ' . htmlspecialchars($e->getMessage()) . '</p></div>';
    }
    ?>
    </div>
    <script>
        // Add interactive behavior for a better experience
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effect to table rows
            const tableRows = document.querySelectorAll('.scores-table tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseover', function() {
                    this.style.backgroundColor = '#f0f9f7';
                });
                row.addEventListener('mouseout', function() {
                    this.style.backgroundColor = '';
                });
            });
            
            // Make stats cards interactive
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach(card => {
                card.addEventListener('click', function() {
                    this.classList.toggle('active');
                    if (this.classList.contains('active')) {
                        this.style.transform = 'scale(1.05)';
                        this.style.boxShadow = '0 8px 20px rgba(44,196,182,0.2)';
                    } else {
                        this.style.transform = '';
                        this.style.boxShadow = '';
                    }
                });
            });
        });
    </script>
</body>
</html>