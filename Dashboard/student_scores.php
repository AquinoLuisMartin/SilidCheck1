<?php
session_start();
include 'db_connect.php';

// Security check - redirect to login if not authenticated
if (!isset($_SESSION['student_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get student name for header display
if (!isset($_SESSION['student_name']) && isset($_SESSION['student_id'])) {
    $student_id = $_SESSION['student_id'];
    $stmt = $conn->prepare("CALL GetStudentById(?)");
    $stmt->bind_param('i', $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        $_SESSION['student_name'] = $row['first_name'] . ' ' . $row['last_name'];
    }
    $stmt->close();
    $conn->next_result(); // Clear the previous result
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Scores - Student Dashboard</title>
  <link rel="stylesheet" href="dashboard.css"/>
  <style>
    .scores-container {
      max-width: 900px;
      margin: auto;
      padding: 20px;
    }
    .score-filters {
      display: flex;
      align-items: center;
      gap: 15px;
      margin-bottom: 20px;
      background: #fff;
      padding: 15px 20px;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(44,196,182,0.08);
    }
    .score-filters label {
      font-weight: 600;
      color: #444;
    }
    .score-filters select {
      padding: 8px 12px;
      border: 1px solid #b2dfdb;
      border-radius: 6px;
      background: #f3fbfa;
    }
    .score-filters button {
      background: #2ec4b6;
      color: #fff;
      border: none;
      border-radius: 6px;
      padding: 8px 16px;
      font-weight: 600;
      cursor: pointer;
    }
    .score-summary {
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 2px 16px rgba(44,196,182,0.10);
      margin-bottom: 20px;
    }
    .scores-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 18px;
      background: #fff;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 2px 16px rgba(44,196,182,0.10);
    }
    .scores-table th {
      padding: 15px 12px;
      background: #e6f9ed;
      border-bottom: 2px solid #b7e4c7;
      text-align: left;
      color: #2a7a66;
    }
    .scores-table td {
      padding: 12px 10px;
      border-bottom: 1px solid #e0f7fa;
    }
    .scores-table tr:last-child td {
      border-bottom: none;
    }
    .score-value {
      font-weight: bold;
    }
    .high-score {
      color: #00a878;
    }
    .medium-score {
      color: #ffb703;
    }
    .low-score {
      color: #ff6f61;
    }
    .pagination {
      display: flex;
      justify-content: center;
      margin-top: 20px;
      gap: 10px;
    }
    .pagination button {
      background: #f3fbfa;
      border: 1px solid #b2dfdb;
      padding: 6px 12px;
      border-radius: 6px;
      cursor: pointer;
    }
    .pagination button.current {
      background: #2ec4b6;
      color: white;
      border-color: #2ec4b6;
    }
    .no-data {
      text-align: center;
      color: #777;
      padding: 20px;
    }
  </style>
</head>
<body>
  <div id="student-dashboard">
    <div class="container">
      <!-- Sidebar with navigation -->
      <aside class="sidebar">
        <div class="logo">
          <img src="../assets/pics/sc_logo.png" alt="SILID Logo" />
        </div>
        <nav>
          <h2>Student Dashboard</h2>
          <ul>
            <li id="menu-home">
              <a href="student_dashboard.php">Home</a>
            </li>
            <li id="menu-exams">
              <a href="student_exams.php">Quiz/Exams</a>
            </li>
            <li id="menu-performance">
              <a href="student_performance.php">Performance</a>
            </li>
            <li id="menu-scores" class="active">
              <a href="student_scores.php">Scores</a>
            </li>
          </ul>
          <div class="settings">
            <h3>Account Settings</h3>
            <ul>
              <li id="logout-btn">
                <a href="javascript:void(0);" onclick="document.getElementById('logoutModal').style.display='block';">Logout</a>
              </li>
            </ul>
            <div id="logoutModal" class="modal" style="display:none;z-index:99999;position:fixed;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.25);">
              <div class="modal-content" style="max-width:350px;text-align:center;position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);background:#fff;padding:32px 24px 24px 24px;border-radius:10px;box-shadow:0 4px 24px rgba(0,0,0,0.15);">
                <h2 style="margin-bottom:24px;">Confirm Logout</h2>
                <p>Are you sure you want to logout?</p>
                <button id="confirmLogoutBtn" style="margin:10px 10px 0 0;">Yes, Logout</button>
                <button onclick="closeLogoutModal()">Cancel</button>
              </div>
            </div>
          </div>
        </nav>
      </aside>
      
      <main class="main-content">
        <header class="header">
          <div class="header-title">
            <h1>My Quiz Scores</h1>
            <p>Welcome back, <?php echo isset($_SESSION['student_name']) ? htmlspecialchars($_SESSION['student_name']) : 'Student'; ?>!</p>
          </div>
        </header>
        
        <div class="scores-container">
          <!-- Score filters -->
          <div class="score-filters">
            <label for="subject-filter">Subject:</label>
            <select id="subject-filter">
              <option value="">All Subjects</option>
              <?php
              // Use stored procedure to get subjects
              $student_id = $_SESSION['student_id']; 
              $stmt = $conn->prepare("CALL GetStudentSubjects(?)");
              $stmt->bind_param('i', $student_id);
              $stmt->execute();
              $result = $stmt->get_result();
              
              if ($result) {
                while ($row = $result->fetch_assoc()) {
                  echo '<option value="' . htmlspecialchars($row['subject']) . '">' . htmlspecialchars($row['subject']) . '</option>';
                }
              }
              $stmt->close();
              $conn->next_result(); // Clear the previous result
              ?>
            </select>
            
            <label for="date-filter">Date Range:</label>
            <select id="date-filter">
              <option value="">All Time</option>
              <option value="week">This Week</option>
              <option value="month">This Month</option>
              <option value="quarter">Last 3 Months</option>
            </select>
            
            <button id="apply-filters">Apply Filters</button>
            <button id="clear-filters" class="clear">Clear</button>
          </div>
          
          <!-- Score summary -->
          <div class="score-summary">
            <?php
            // Use stored procedure for score summary
            $stmt = $conn->prepare("CALL GetStudentScoreSummary(?)");
            $stmt->bind_param('i', $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $row = $result->fetch_assoc()) {
              echo '<div style="display:flex;justify-content:space-between;flex-wrap:wrap;">';
              echo '<div style="margin-right:30px;margin-bottom:10px;"><strong>Total Quizzes:</strong> ' . ($row['total_quizzes'] ?? 0) . '</div>';
              echo '<div style="margin-right:30px;margin-bottom:10px;"><strong>Average Score:</strong> ' . number_format($row['avg_score'] ?? 0, 1) . '%</div>';
              echo '<div style="margin-bottom:10px;"><strong>Highest Score:</strong> ' . ($row['highest_score'] ?? 0) . '%</div>';
              echo '</div>';
            } else {
              echo '<p style="margin:0;text-align:center;">No quiz data available.</p>';
            }
            $stmt->close();
            $conn->next_result(); // Clear the previous result
            ?>
          </div>
          
          <!-- Scores table -->
          <table class="scores-table">
            <thead>
              <tr>
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
            if (isset($_SESSION['student_id'])) {
              $student_id = intval($_SESSION['student_id']);
              
              // Use GetStudentQuizScores stored procedure
              $stmt = $conn->prepare("CALL GetStudentQuizScores(?)");
              $stmt->bind_param('i', $student_id);
              $stmt->execute();
              $result = $stmt->get_result();
              
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
                echo '<tr><td colspan="6" class="no-data">No quiz scores found.</td></tr>';
              }
              
              $stmt->close();
            } else {
              echo '<tr><td colspan="6" class="no-data">Not logged in.</td></tr>';
            }
            ?>
            </tbody>
          </table>
          
          <!-- Pagination will be added by JavaScript if needed -->
          <div class="pagination" id="pagination-container"></div>
        </div>
      </main>
    </div>
  </div>
  
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    // Logout functions
    document.getElementById('confirmLogoutBtn').onclick = function() {
      window.location.href = '../logout.php';
    };
    
    function closeLogoutModal() {
      document.getElementById('logoutModal').style.display = 'none';
    }
    
    document.querySelector('button[onclick="closeLogoutModal()"]').addEventListener('click', closeLogoutModal);
    
    // Filter functionality
    const applyFiltersBtn = document.getElementById('apply-filters');
    const clearFiltersBtn = document.getElementById('clear-filters');
    
    applyFiltersBtn.addEventListener('click', function() {
      applyFilters();
    });
    
    clearFiltersBtn.addEventListener('click', function() {
      document.getElementById('subject-filter').value = '';
      document.getElementById('date-filter').value = '';
      applyFilters();
    });
    
    function applyFilters() {
      const subject = document.getElementById('subject-filter').value;
      const date = document.getElementById('date-filter').value;
      
      // Show loading indicator
      document.getElementById('scores-tbody').innerHTML = '<tr><td colspan="6" style="text-align:center;">Loading...</td></tr>';
      
      // Fetch filtered results
      fetch('filter_scores.php?subject=' + encodeURIComponent(subject) + '&date=' + encodeURIComponent(date))
        .then(response => response.text())
        .then(html => {
          document.getElementById('scores-tbody').innerHTML = html;
        })
        .catch(error => {
          console.error('Error fetching filtered scores:', error);
          document.getElementById('scores-tbody').innerHTML = '<tr><td colspan="6" style="text-align:center;color:red;">Error loading data. Please try again.</td></tr>';
        });
    }
  });
  </script>
</body>
</html>