<?php
session_start();
include 'db_connect.php';

// Security check - redirect to login if not authenticated
if (!isset($_SESSION['teacher_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch teacher name from DB if not set in session
if (!isset($_SESSION['teacher_name']) && isset($_SESSION['teacher_id'])) {
    $teacher_id = $_SESSION['teacher_id'];
    
    $stmt = $conn->prepare("CALL GetTeacherById(?)");
    $stmt->bind_param('i', $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        $_SESSION['teacher_name'] = $row['name'];
    }
    $stmt->close();
    $conn->next_result(); // Clear the previous result
}

// Get list of quizzes for filter
$quizzes = [];
if (isset($_SESSION['teacher_id'])) {
    $teacher_id = intval($_SESSION['teacher_id']);
    $stmt = $conn->prepare("CALL GetTeacherQuizzesForFilter(?)");
    $stmt->bind_param('i', $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $quizzes[] = $row;
        }
    }
    $stmt->close();
    $conn->next_result(); // Clear the previous result
}

// Get recent quiz scores (default view)
$scores = [];
if (isset($_SESSION['teacher_id'])) {
    $teacher_id = intval($_SESSION['teacher_id']);
    
    // Using stored procedure instead of direct SQL
    $stmt = $conn->prepare("CALL GetRecentQuizScores(?)");
    $stmt->bind_param('i', $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $scores[] = $row;
        }
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Student Scores - Teacher Dashboard</title>
  <link rel="stylesheet" href="dashboard.css"/>
  <style>
    .scores-container {
      max-width: 900px;
      margin: 20px auto;
      padding: 0 15px;
    }
    .filters-panel {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(44,196,182,0.10);
      padding: 20px;
      margin-bottom: 20px;
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      align-items: center;
    }
    .filters-panel select, .filters-panel input {
      padding: 10px;
      border-radius: 6px;
      border: 1px solid #b2dfdb;
      background: #f5f5f5;
      min-width: 180px;
    }
    .filters-panel button {
      background: #2ec4b6;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
    }
    .filters-panel button:hover {
      background: #25aaa0;
    }
    .scores-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(44,196,182,0.10);
    }
    .scores-table th {
      background: #e6f9ed;
      color: #2a7a66;
      padding: 15px;
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
    .score-high {
      color: #00a878;
      font-weight: bold;
    }
    .score-medium {
      color: #ffb703;
      font-weight: bold;
    }
    .score-low {
      color: #ff6f61;
      font-weight: bold;
    }
    .statistics-cards {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      margin-bottom: 20px;
    }
    .stat-card {
      flex: 1;
      min-width: 200px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(44,196,182,0.10);
      padding: 20px;
      text-align: center;
    }
    .stat-card h3 {
      margin: 0 0 10px 0;
      color: #333;
      font-size: 16px;
    }
    .stat-card .value {
      font-size: 28px;
      font-weight: bold;
      color: #2ec4b6;
    }
    .export-btn {
      background: #4d8076;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
      margin-right: 10px;
    }
    .export-btn:hover {
      background: #3a6158;
    }
    
    /* Sidebar styling */
    .sidebar ul li.menu-item {
      cursor: pointer;
      transition: all 0.2s ease;
      padding: 0;
      margin-bottom: 5px;
      border-radius: 4px;
      border-left: 4px solid transparent;
    }
    
    .sidebar ul li.menu-item a {
      padding: 12px 15px;
      display: flex;
      align-items: center;
      text-decoration: none;
      color: inherit;
      width: 100%;
    }
    
    .sidebar ul li.active {
      background-color: rgba(46, 196, 182, 0.15);
      color: #2ec4b6;
      font-weight: 600;
      border-left: 4px solid #2ec4b6;
    }
    
    .sidebar ul li:hover {
      background-color: rgba(46, 196, 182, 0.08);
    }
    
    .menu-icon {
      margin-right: 10px;
      font-size: 16px;
    }
    
    /* Pagination */
    .pagination {
      display: flex;
      justify-content: center;
      margin-top: 20px;
      gap: 5px;
    }
    .pagination button {
      padding: 8px 12px;
      border: 1px solid #ddd;
      background: white;
      border-radius: 4px;
      cursor: pointer;
    }
    .pagination button.active {
      background: #2ec4b6;
      color: white;
      border-color: #2ec4b6;
    }
  </style>
</head>
<body>
<div id="teacher-dashboard">
    <div class="container">
      <!-- Include sidebar -->
      <aside class="sidebar">
        <div class="logo">
          <img src="../assets/pics/sc_logo.png" alt="SILID Logo" />
        </div>
        <nav>
          <h2>Teacher Dashboard</h2>
          <ul>
            <li id="teacher-menu-home" class="menu-item">
              <a href="teacher_dashboard.php">
                <span class="menu-icon">🏠</span>
                <span class="menu-text">Home</span>
              </a>
            </li>
            <li id="teacher-menu-create-quiz" class="menu-item">
              <a href="teacher_create_quiz.php">
                <span class="menu-icon">✏️</span>
                <span class="menu-text">Create</span>
              </a>
            </li>
            <li id="teacher-menu-view-quiz" class="menu-item">
              <a href="teacher_view_quiz.php">
                <span class="menu-icon">👁️</span>
                <span class="menu-text">View</span>
              </a>
            </li>
            <li id="teacher-menu-student-scores" class="menu-item active">
              <a href="teacher_student_scores.php">
                <span class="menu-icon">📊</span>
                <span class="menu-text">Students Scores</span>
              </a>
            </li>
            <li id="teacher-menu-view-students" class="menu-item">
              <a href="teacher_view_students.php">
                <span class="menu-icon">👥</span>
                <span class="menu-text">View Students</span>
              </a>
            </li>
          </ul>
          <div class="settings">
            <h3>Account Settings</h3>
            <ul>
              <li id="logout-btn-teacher" class="menu-item">
                <a href="javascript:void(0);" onclick="document.getElementById('logoutModal').style.display='block';">
                  <span class="menu-icon">🚪</span>
                  <span class="menu-text">Logout</span>
                </a>
              </li>
            </ul>
            <!-- Logout Modal -->
            <div id="logoutModal" class="modal" style="display:none;z-index:99999;position:fixed;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.25);">
              <div class="modal-content" style="max-width:350px;text-align:center;position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);background:#fff;padding:32px 24px 24px 24px;border-radius:10px;box-shadow:0 4px 24px rgba(0,0,0,0.15);">
                <h2 style="margin-bottom:24px;">Confirm Logout</h2>
                <p>Are you sure you want to logout?</p>
                <div style="margin-top:20px;">
                  <a href="../logout.php" class="btn" style="background:#2ec4b6;color:#fff;text-decoration:none;padding:10px 20px;border-radius:6px;margin-right:10px;display:inline-block;">Yes, Logout</a>
                  <button onclick="closeLogoutModal()" style="background:#eee;color:#333;border:none;padding:10px 20px;border-radius:6px;cursor:pointer;">Cancel</button>
                </div>
              </div>
            </div>
          </div>
        </nav>
      </aside>
      
      <main class="main-content">
        <header class="header">
          <div class="header-title middle-left">
            <h1>Student Scores</h1>
            <p>Welcome back, <?php echo isset($_SESSION['teacher_name']) ? htmlspecialchars($_SESSION['teacher_name']) : (isset($_SESSION['teacher_id']) ? 'Teacher ID: ' . intval($_SESSION['teacher_id']) : 'Teacher'); ?>!</p>
          </div>
        </header>
        
        <!-- Student Scores Content -->
        <section class="dashboard-body">
          <div class="scores-container">
            <!-- Aggregate Statistics -->
            <div class="statistics-cards">
              <?php
              if (isset($_SESSION['teacher_id'])) {
                $teacher_id = intval($_SESSION['teacher_id']);
                $stmt = $conn->prepare("CALL GetScoreStatistics(?)");
                $stmt->bind_param('i', $teacher_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result && $row = $result->fetch_assoc()) {
                  echo '<div class="stat-card">';
                  echo '<h3>Average Score</h3>';
                  echo '<div class="value">' . number_format($row['avg_score'], 1) . '%</div>';
                  echo '</div>';
                  
                  echo '<div class="stat-card">';
                  echo '<h3>Highest Score</h3>';
                  echo '<div class="value">' . number_format($row['max_score'], 1) . '%</div>';
                  echo '</div>';
                  
                  echo '<div class="stat-card">';
                  echo '<h3>Total Students</h3>';
                  echo '<div class="value">' . $row['total_students'] . '</div>';
                  echo '</div>';
                  
                  echo '<div class="stat-card">';
                  echo '<h3>Total Quizzes Taken</h3>';
                  echo '<div class="value">' . $row['total_attempts'] . '</div>';
                  echo '</div>';
                }
                $stmt->close();
                $conn->next_result(); // Clear the previous result
              }
              ?>
            </div>
            
            <!-- Filters Panel -->
            <div class="filters-panel">
              <div>
                <label for="quiz-filter">Filter by Quiz:</label>
                <select id="quiz-filter">
                  <option value="">All Quizzes</option>
                  <?php
                  foreach ($quizzes as $quiz) {
                    echo '<option value="' . $quiz['id'] . '">' . htmlspecialchars($quiz['title']) . '</option>';
                  }
                  ?>
                </select>
              </div>
              <div>
                <label for="student-filter">Filter by Student:</label>
                <input type="text" id="student-filter" placeholder="Enter student name">
              </div>
              <div>
                <label for="date-filter">Filter by Date:</label>
                <select id="date-filter">
                  <option value="">All Time</option>
                  <option value="week">Last Week</option>
                  <option value="month">Last Month</option>
                  <option value="quarter">Last 3 Months</option>
                </select>
              </div>
              <div>
                <button id="apply-filter-btn">Apply Filters</button>
                <button id="reset-filter-btn">Reset</button>
              </div>
              <div style="margin-left:auto;">
                <button class="export-btn" id="export-csv-btn">Export CSV</button>
              </div>
            </div>
            
            <!-- Scores Table -->
            <div id="scores-table-container">
              <table class="scores-table">
                <thead>
                  <tr>
                    <th>Student Name</th>
                    <th>Quiz Title</th>
                    <th>Subject</th>
                    <th>Score</th>
                    <th>Total Items</th>
                    <th>Percentage</th>
                    <th>Date Taken</th>
                  </tr>
                </thead>
                <tbody id="scores-tbody">
                <?php
                if (!empty($scores)) {
                  foreach ($scores as $score) {
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
                  echo '<tr><td colspan="7" style="text-align:center;padding:20px;">No quiz scores available.</td></tr>';
                }
                ?>
                </tbody>
              </table>
              
              <!-- Pagination -->
              <div class="pagination" id="pagination-container">
                <!-- Pagination buttons will be added by JavaScript -->
              </div>
            </div>
          </div>
        </section>
        
        <footer class="footer">
          <p>&copy; 2023 SILID. All rights reserved.</p>
        </footer>
      </main>
    </div>
  </div>
  
  <script>
  function closeLogoutModal() {
    document.getElementById('logoutModal').style.display = 'none';
  }
  
  document.addEventListener('DOMContentLoaded', function() {
    // Apply filters
    document.getElementById('apply-filter-btn').addEventListener('click', function() {
      applyFilters();
    });
    
    // Reset filters
    document.getElementById('reset-filter-btn').addEventListener('click', function() {
      document.getElementById('quiz-filter').value = '';
      document.getElementById('student-filter').value = '';
      document.getElementById('date-filter').value = '';
      applyFilters();
    });
    
    // Export to CSV
    document.getElementById('export-csv-btn').addEventListener('click', function() {
      const quizId = document.getElementById('quiz-filter').value;
      const studentName = document.getElementById('student-filter').value;
      const dateFilter = document.getElementById('date-filter').value;
      
      window.location.href = `export_scores.php?quiz_id=${quizId}&student=${encodeURIComponent(studentName)}&date=${dateFilter}`;
    });
    
    // Function to apply filters
    function applyFilters() {
      const quizId = document.getElementById('quiz-filter').value;
      const studentName = document.getElementById('student-filter').value;
      const dateFilter = document.getElementById('date-filter').value;
      
      // Show loading indicator
      document.getElementById('scores-tbody').innerHTML = '<tr><td colspan="7" style="text-align:center;padding:20px;">Loading...</td></tr>';
      
      // Fetch filtered results
      fetch(`filter_scores_teacher.php?quiz_id=${quizId}&student=${encodeURIComponent(studentName)}&date=${dateFilter}`)
        .then(response => response.text())
        .then(html => {
          document.getElementById('scores-tbody').innerHTML = html;
        })
        .catch(error => {
          console.error('Error fetching filtered scores:', error);
          document.getElementById('scores-tbody').innerHTML = '<tr><td colspan="7" style="text-align:center;padding:20px;color:red;">Error loading data. Please try again.</td></tr>';
        });
    }
  });
  </script>
</body>
</html>