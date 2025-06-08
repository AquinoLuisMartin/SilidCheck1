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
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Performance - Student Dashboard</title>
  <link rel="stylesheet" href="dashboard.css"/>
  <style>
    .performance-container {
      max-width: 700px;
      margin: auto;
      background: #fff;
      padding: 30px;
      border-radius: 18px;
      box-shadow: 0 2px 16px rgba(44,196,182,0.10);
    }
    .chart-container {
      margin-top: 20px;
      padding: 10px;
      background: #fff;
      border-radius: 12px;
    }
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      margin-top: 30px;
    }
    .stat-card {
      background: #f3fbfa;
      border-radius: 12px;
      padding: 20px;
      text-align: center;
    }
    .stat-card h3 {
      color: #2ec4b6;
      margin-bottom: 10px;
      font-size: 1.1em;
    }
    .stat-card .value {
      font-size: 1.8em;
      font-weight: bold;
      color: #333;
    }
  </style>
</head>
<body>
  <div id="student-dashboard">
    <div class="container">
      <!-- Include sidebar with the same structure but updated active classes -->
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
            <li id="menu-performance" class="active">
              <a href="student_performance.php">Performance</a>
            </li>
            <li id="menu-scores">
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
            <h1>Performance Analytics</h1>
            <p>Welcome back, <?php echo isset($_SESSION['student_name']) ? htmlspecialchars($_SESSION['student_name']) : 'Student'; ?>!</p>
          </div>
        </header>
        
        <div class="performance-container">
          <h2 style="font-size:1.3em;font-weight:700;margin-bottom:18px;">Quiz Performance Overview</h2>
          
          <!-- Performance statistics -->
          <div class="stats-grid">
            <?php
            // Use stored procedure to get performance statistics
            $student_id = $_SESSION['student_id'];
            $stmt = $conn->prepare("CALL GetStudentPerformanceStats(?)");
            $stmt->bind_param('i', $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $row = $result->fetch_assoc()) {
                $average = number_format($row['average_score'], 1);
                $highest = $row['highest_score'];
                $quizzes_taken = $row['quizzes_taken'];
            } else {
                $average = 0;
                $highest = 0;
                $quizzes_taken = 0;
            }
            $stmt->close();
            $conn->next_result(); // Clear the previous result
            ?>
            
            <div class="stat-card">
              <h3>Average Score</h3>
              <div class="value"><?php echo $average; ?>%</div>
            </div>
            <div class="stat-card">
              <h3>Highest Score</h3>
              <div class="value"><?php echo $highest; ?>%</div>
            </div>
            <div class="stat-card">
              <h3>Quizzes Taken</h3>
              <div class="value"><?php echo $quizzes_taken; ?></div>
            </div>
          </div>
          
          <!-- Performance chart -->
          <div class="chart-container">
            <canvas id="performanceChart" width="600" height="300" style="display:block;margin:auto;"></canvas>
          </div>
          
          <!-- Subject breakdown -->
          <h3 style="font-size:1.1em;margin:30px 0 15px 0;color:#2ec4b6;">Performance by Subject</h3>
          <div id="subject-breakdown">
            <table style="width:100%;border-collapse:collapse;margin-top:10px;">
              <thead>
                <tr style="background:#e6f9ed;">
                  <th style="padding:12px 8px;border-bottom:2px solid #b7e4c7;text-align:left;">Subject</th>
                  <th style="padding:12px 8px;border-bottom:2px solid #b7e4c7;text-align:left;">Avg. Score</th>
                  <th style="padding:12px 8px;border-bottom:2px solid #b7e4c7;text-align:left;">Quizzes</th>
                </tr>
              </thead>
              <tbody>
              <?php
              // Use stored procedure to get subject performance
              $stmt = $conn->prepare("CALL GetStudentSubjectPerformance(?)");
              $stmt->bind_param('i', $student_id);
              $stmt->execute();
              $result = $stmt->get_result();
              
              if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                  echo '<tr style="border-bottom:1px solid #e0f7fa;">';
                  echo '<td style="padding:10px 8px;">' . htmlspecialchars($row['subject']) . '</td>';
                  echo '<td style="padding:10px 8px;">' . number_format($row['avg_score'], 1) . '%</td>';
                  echo '<td style="padding:10px 8px;">' . htmlspecialchars($row['quiz_count']) . '</td>';
                  echo '</tr>';
                }
              } else {
                echo '<tr><td colspan="3" style="padding:12px 8px;text-align:center;">No performance data available.</td></tr>';
              }
              $stmt->close();
              ?>
              </tbody>
            </table>
          </div>
        </div>
      </main>
    </div>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    
    // Performance chart with better error handling
    fetch('load_scores.php?performance=1')
      .then(response => response.json())
      .then(function(data) {
        var ctx = document.getElementById('performanceChart').getContext('2d');
        if (window.performanceChartInstance) window.performanceChartInstance.destroy();
        
        // Handle empty data gracefully
        const labels = data.labels && data.labels.length > 0 ? data.labels : ['No Quiz Data'];
        const scores = data.scores && data.scores.length > 0 ? data.scores : [0];
        
        window.performanceChartInstance = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: labels,
            datasets: [{
              label: 'Quiz Scores',
              data: scores,
              backgroundColor: ['#2ec4b6', '#ffb703', '#ff6f61', '#4361ee', '#7209b7'],
              borderRadius: 6,
              barPercentage: 0.6,
              categoryPercentage: 0.7,
              maxBarThickness: 18
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { display: false },
              tooltip: {
                callbacks: {
                  label: function(context) {
                    return context.parsed.y + '%';
                  }
                }
              }
            },
            scales: {
              x: {
                grid: { display: false },
                ticks: { font: { size: 11 } }
              },
              y: {
                beginAtZero: true,
                max: 100,
                grid: { display: true, color: 'rgba(0,0,0,0.05)' },
                ticks: { font: { size: 11 }, stepSize: 20 }
              }
            },
            layout: { padding: 10 }
          }
        });
      })
      .catch(error => {
        console.error('Error loading performance data:', error);
        // Show empty chart on error
        var ctx = document.getElementById('performanceChart').getContext('2d');
        if (window.performanceChartInstance) window.performanceChartInstance.destroy();
        
        window.performanceChartInstance = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: ['Data Unavailable'],
            datasets: [{
              data: [0],
              backgroundColor: '#e0e0e0'
            }]
          },
          options: {
            responsive: true,
            plugins: { 
              legend: { display: false },
              tooltip: { enabled: false }
            },
            scales: {
              y: { 
                beginAtZero: true,
                max: 100,
                ticks: { stepSize: 20 }
              }
            }
          }
        });
      });
  });
  </script>
</body>
</html>