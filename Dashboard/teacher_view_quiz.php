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
}

// Get teacher's quizzes
$quizzes = [];
if (isset($_SESSION['teacher_id'])) {
    $teacher_id = intval($_SESSION['teacher_id']);
    $stmt = $conn->prepare("CALL GetTeacherQuizzes(?)");
    $stmt->bind_param('i', $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $quizzes[] = $row;
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
  <title>View Quizzes - Teacher Dashboard</title>
  <link rel="stylesheet" href="dashboard.css"/>
  <style>
    .quiz-card {
      background: #f8f9fa !important;
      border-radius: 14px !important;
      box-shadow: 0 2px 16px rgba(44,196,182,0.10) !important;
      padding: 22px 24px 18px 24px !important;
      margin-bottom: 28px !important;
      transition: box-shadow 0.2s;
      border: 1.5px solid #e0f7fa !important;
    }
    .quiz-card:hover {
      box-shadow: 0 6px 24px rgba(44,196,182,0.16) !important;
    }
    .quiz-card h3 {
      font-size: 1.18em;
      margin: 0 0 10px 0;
      font-weight: 700;
      color: #222;
    }
    .quiz-card .quiz-meta {
      color: #2ec4b6;
      font-weight: 600;
      font-size: 1em;
      margin-left: 4px;
    }
    .quiz-card .quiz-info {
      font-size: 1em;
      color: #444;
      margin-bottom: 10px;
    }
    .quiz-card .quiz-actions {
      display: flex;
      gap: 24px;
      margin-top: 18px;
      align-items: center;
    }
    .quiz-card button, .quiz-card select {
      padding: 14px 38px !important;
      border-radius: 8px !important;
      border: none !important;
      font-size: 1.1em !important;
      font-weight: 600 !important;
      background: #2ec4b6 !important;
      color: #fff !important;
      cursor: pointer !important;
      transition: background 0.2s !important;
      box-shadow: none !important;
      outline: none !important;
    }
    .quiz-card button.delete-quiz-btn {
      background: #ffd6d6 !important;
      color: #d32f2f !important;
      border: none !important;
      margin-right: 8px !important;
    }
    .quiz-card button.delete-quiz-btn:hover {
      background: #ffbdbd !important;
      color: #a31515 !important;
    }
    .quiz-card button:hover, .quiz-card select:hover {
      background: #25b3a6 !important;
    }
    .quiz-card select {
      background: #2ec4b6 !important;
      color: #fff !important;
      border: none !important;
      min-width: 140px !important;
      padding: 14px 38px !important;
    }
    .quiz-card select:focus {
      border: none !important;
      box-shadow: 0 0 0 2px #b2dfdb !important;
    }
    
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
  </style>
</head>
<body>
<div id="teacher-dashboard" >
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
            <li id="teacher-menu-view-quiz" class="menu-item active">
              <a href="teacher_view_quiz.php">
                <span class="menu-icon">👁️</span>
                <span class="menu-text">View</span>
              </a>
            </li>
            <li id="teacher-menu-student-scores" class="menu-item">
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
            <h1>View Quizzes</h1>
            <p>Welcome back, <?php echo isset($_SESSION['teacher_name']) ? htmlspecialchars($_SESSION['teacher_name']) : (isset($_SESSION['teacher_id']) ? 'Teacher ID: ' . intval($_SESSION['teacher_id']) : 'Teacher'); ?>!</p>
          </div>
        </header>
        
        <!-- View Quizzes Content -->
        <section class="dashboard-body">
          <div class="view-quiz-container" style="background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(44, 196, 182, 0.10);padding:36px 32px 32px 32px;margin:40px auto 0 auto;max-width:700px;">
            <h2 style="font-size:1.6em;font-weight:700;margin-bottom:28px;">Your Quizzes</h2>
            <div class="quizzes-list">
              <?php
              // Display quizzes
              if (!empty($quizzes)) {
                foreach ($quizzes as $quiz) {
                  echo '<div class="quiz-card">';
                  echo '<h3>' . htmlspecialchars($quiz["title"]) . ' <span class="quiz-meta">(' . htmlspecialchars($quiz["subject"]) . ')</span></h3>';
                  echo '<div class="quiz-info"><b>Questions:</b> ' . intval($quiz["question_count"]) . '</div>';
                  echo '<div class="quiz-info"><b>Students Taken:</b> ' . intval($quiz["students_taken"]) . '</div>';
                  echo '<div class="quiz-actions">';
                  echo '<button class="delete-quiz-btn" data-quizid="' . $quiz['id'] . '">Delete</button>';
                  echo '<select class="quiz-status-select" data-quizid="' . $quiz['id'] . '">';
                  echo '<option value="ongoing" ' . ($quiz['status'] == 'ongoing' ? 'selected' : '') . '>Ongoing</option>';
                  echo '<option value="closed" ' . ($quiz['status'] == 'closed' ? 'selected' : '') . '>Closed</option>';
                  echo '</select>';
                  echo '</div>';
                  echo '</div>';
                }
              } else {
                echo '<p>No quizzes found. Create your first quiz!</p>';
              }
              ?>
            </div>
          </div>
        </section>
        
        <footer class="footer">
          <p>&copy; 2023 SILID. All rights reserved.</p>
        </footer>
      </main>
    </div>
  </div>
  
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
  function closeLogoutModal() {
    document.getElementById('logoutModal').style.display = 'none';
  }
  
  document.addEventListener('DOMContentLoaded', function() {
    // Delete quiz functionality
    document.querySelectorAll('.delete-quiz-btn').forEach(function(btn) {
      btn.addEventListener('click', function() {
        if (confirm('Are you sure you want to delete this quiz?')) {
          var quizId = this.getAttribute('data-quizid');
          
          fetch('delete_quiz.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'quiz_id=' + quizId
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              this.closest('.quiz-card').remove();
              
              // Check if there are no more quizzes
              if (document.querySelectorAll('.quiz-card').length === 0) {
                document.querySelector('.quizzes-list').innerHTML = '<p>No quizzes found. Create your first quiz!</p>';
              }
            } else {
              alert('Error deleting quiz: ' + (data.message || 'Unknown error'));
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Error deleting quiz. Please try again.');
          });
        }
      });
    });
    
    // Update quiz status functionality
    document.querySelectorAll('.quiz-status-select').forEach(function(select) {
      select.addEventListener('change', function() {
        var quizId = this.getAttribute('data-quizid');
        var status = this.value;
        
        fetch('update_quiz_status.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'quiz_id=' + quizId + '&status=' + status
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Show success notification
            var notification = document.createElement('div');
            notification.style.position = 'fixed';
            notification.style.bottom = '20px';
            notification.style.right = '20px';
            notification.style.background = '#4CAF50';
            notification.style.color = 'white';
            notification.style.padding = '10px 20px';
            notification.style.borderRadius = '5px';
            notification.style.boxShadow = '0 2px 10px rgba(0,0,0,0.2)';
            notification.textContent = 'Quiz status updated successfully!';
            document.body.appendChild(notification);
            
            // Remove notification after 3 seconds
            setTimeout(function() {
              notification.remove();
            }, 3000);
          } else {
            alert('Error updating quiz status: ' + (data.message || 'Unknown error'));
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error updating quiz status. Please try again.');
        });
      });
    });
  });
  </script>
</body>
</html>