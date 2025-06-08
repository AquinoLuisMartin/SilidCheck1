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
  <title>Quizzes & Exams - Student Dashboard</title>
  <link rel="stylesheet" href="dashboard.css"/>
  <!-- Include your CSS here or copy from student_dashboard.php -->
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
            <li id="menu-exams" class="active">
              <a href="student_exams.php">Quiz/Exams</a>
            </li>
            <li id="menu-performance">
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
            <!-- Logout modal here -->
          </div>
        </nav>
      </aside>
      
      <main class="main-content">
        <header class="header">
          <div class="header-title">
            <h1>Quizzes & Exams</h1>
            <p>Welcome back, <?php echo isset($_SESSION['student_name']) ? htmlspecialchars($_SESSION['student_name']) : 'Student'; ?>!</p>
          </div>
          <div id="student-quiz-status" style="position:absolute;right:48px;top:72px;z-index:2;margin-top:8px;background:#fff;border-radius:16px;padding:6px 18px;font-size:1.05em;box-shadow:0 1px 6px rgba(44,196,182,0.08);font-weight:600;color:#222;min-width:90px;text-align:center;">
            Quizzes: <span id="quiz-count">0</span>
          </div>
        </header>
        
        <!-- Include only the quizzes section content -->
        <div id="quizzes-section" class="dashboard-body">
          <h2 style="font-size:1.5em;font-weight:700;margin-bottom:24px;">Available Quizzes/Exams</h2>
          <!-- Quiz content will be loaded here -->
        </div>
        
        <!-- Quiz modal -->
        <div id="quiz-modal" style="display:none; position:fixed; top:12%; left:50%; transform:translateX(-50%); width:420px; max-width:95vw; background:#fff; border:1px solid #ccc; padding:24px 18px 18px 18px; z-index:1000; border-radius:16px; box-shadow:0 8px 32px rgba(44,196,182,0.18); transition: all 0.35s cubic-bezier(.4,2,.6,1);">
          <h3 id="quiz-title" style="font-size:1.25em;margin-bottom:18px;text-align:center;"></h3>
          <form id="quiz-form"></form>
          <div class="modal-footer" style="margin-top:18px; text-align:right;">
            <button id="submit-quiz" type="button" style="padding:10px 20px; background:#2ec4b6; color:#fff; border:none; border-radius:6px; cursor:pointer;">Submit</button>
            <button id="close-quiz" type="button" style="padding:10px 20px; background:#eee; color:#222; border:none; border-radius:6px; cursor:pointer; margin-left:10px;">Close</button>
          </div>
        </div>
      </main>
    </div>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
  // Load quiz data when page loads
  document.addEventListener('DOMContentLoaded', function() {
    // Fetch and display available quizzes
    fetch('load_quizzes.php?student_ongoing=1')
      .then(response => response.json())
      .then(data => {
        var quizCountSpan = document.getElementById('quiz-count');
        if (quizCountSpan) quizCountSpan.textContent = (data.success && data.quizzes) ? data.quizzes.length : 0;
        
        var quizzesSection = document.getElementById('quizzes-section');
        var ul = document.createElement('ul');
        ul.style.listStyle = 'none';
        ul.style.padding = '0';
        ul.style.maxWidth = '520px';
        ul.style.margin = '0 auto';
        
        if (data.success && data.quizzes && data.quizzes.length > 0) {
          data.quizzes.forEach(function(quiz) {
            var li = document.createElement('li');
            li.style.background = '#fff';
            li.style.borderRadius = '12px';
            li.style.boxShadow = '0 2px 12px rgba(44,196,182,0.10)';
            li.style.padding = '22px 28px 18px 28px';
            li.style.marginBottom = '18px';
            li.style.display = 'flex';
            li.style.alignItems = 'center';
            li.style.justifyContent = 'space-between';
            li.innerHTML = '<div><span style="font-size:1.15em;font-weight:600;color:#2ec4b6;">' + quiz.title + '</span> <span style="color:#888;font-size:1em;">(' + quiz.subject + ')</span></div>' +
              '<button class="take-quiz-btn" data-quizid="' + quiz.id + '" style="background:#2ec4b6;color:#fff;border:none;border-radius:6px;padding:8px 22px;font-weight:600;font-size:1em;cursor:pointer;transition:background 0.2s;">Take Quiz</button>';
            ul.appendChild(li);
          });
        } else {
          var li = document.createElement('li');
          li.textContent = 'No available quizzes/exams.';
          li.style.textAlign = 'center';
          li.style.color = '#888';
          li.style.background = '#fff';
          li.style.borderRadius = '12px';
          li.style.padding = '22px 28px 18px 28px';
          ul.appendChild(li);
        }
        
        quizzesSection.appendChild(ul);
      });
      
    // Quiz modal logic...
    document.addEventListener('click', function(e) {
      if (e.target.classList.contains('take-quiz-btn')) {
        // Your existing quiz modal logic
      }
    });
    
    document.getElementById('close-quiz').onclick = function() {
      document.getElementById('quiz-modal').style.display = 'none';
    };
    
    document.getElementById('submit-quiz').onclick = function() {
      // Your existing submit quiz logic
    };
    
    // Logout functions
    document.getElementById('confirmLogoutBtn').onclick = function() {
      window.location.href = '../logout.php';
    };
    
    function closeLogoutModal() {
      document.getElementById('logoutModal').style.display = 'none';
    }
    
    document.querySelector('button[onclick="closeLogoutModal()"]').addEventListener('click', closeLogoutModal);
  });
  </script>
</body>
</html>