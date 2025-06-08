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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Create Quiz - Teacher Dashboard</title>
  <link rel="stylesheet" href="dashboard.css"/>
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
            <li id="teacher-menu-create-quiz" class="menu-item active">
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
            <h1>Create Quiz</h1>
            <p>Welcome back, <?php echo isset($_SESSION['teacher_name']) ? htmlspecialchars($_SESSION['teacher_name']) : (isset($_SESSION['teacher_id']) ? 'Teacher ID: ' . intval($_SESSION['teacher_id']) : 'Teacher'); ?>!</p>
          </div>
        </header>
        
        <!-- Create Quiz Content -->
        <section class="dashboard-body">
          <div class="create-quiz-container" style="margin-top:40px; max-width:900px; margin-left:auto; margin-right:auto; background:#fff; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,0.08); padding:32px 24px;">
            <h2>Create Task</h2>
            <form id="create-quiz-form">
              <div class="form-group">
                <label for="quiz-title"> Title</label>
                <input type="text" id="quiz-title" name="quiz_title" required/>
              </div>
              <div class="form-group">
                <label for="quiz-subject">Subject</label>
                <select id="quiz-subject" name="quiz_subject" required>
                  <option value="">Select a subject</option>
                  <option value="Mathematics">Mathematics</option>
                  <option value="Science">Science</option>
                  <option value="English">English</option>
                </select>
              </div>
              <div class="form-group">
                <label for="quiz-questions">Questions</label>
                <div id="questions-list">
                  <div class="question-item">
                    <input type="text" name="question[]" class="question-input" placeholder="Enter question here" required/>
                    <input type="text" name="answer[]" class="answer-input" placeholder="Correct answer" required style="margin-left:8px;"/>
                    <button type="button" class="delete-question-btn" style="margin-left:8px;background:#ffdddd;color:#b71c1c;border:none;padding:6px 12px;border-radius:6px;cursor:pointer;">Delete</button>
                  </div>
                </div>
                <div class="button-group">
                  <button type="button" id="add-question-btn" class="add-button">Add Another Question</button>
                  <button type="submit" class="add-button">Create Quiz</button>
                </div>
              </div>
            </form>
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
    // Add question button functionality
    document.getElementById('add-question-btn').addEventListener('click', function() {
      var questionsList = document.getElementById('questions-list');
      var newQuestion = document.createElement('div');
      newQuestion.className = 'question-item';
      newQuestion.innerHTML = `
        <input type="text" name="question[]" class="question-input" placeholder="Enter question here" required/>
        <input type="text" name="answer[]" class="answer-input" placeholder="Correct answer" required style="margin-left:8px;"/>
        <button type="button" class="delete-question-btn" style="margin-left:8px;background:#ffdddd;color:#b71c1c;border:none;padding:6px 12px;border-radius:6px;cursor:pointer;">Delete</button>
      `;
      questionsList.appendChild(newQuestion);
      
      // Add delete functionality to the new delete button
      newQuestion.querySelector('.delete-question-btn').addEventListener('click', function() {
        if (document.querySelectorAll('.question-item').length > 1) {
          this.parentNode.remove();
        } else {
          alert('You need at least one question!');
        }
      });
    });
    
    // Add delete functionality to the initial delete button
    document.querySelector('.delete-question-btn').addEventListener('click', function() {
      if (document.querySelectorAll('.question-item').length > 1) {
        this.parentNode.remove();
      } else {
        alert('You need at least one question!');
      }
    });
    
    // Form submission
    document.getElementById('create-quiz-form').addEventListener('submit', function(e) {
      e.preventDefault();
      
      var formData = new FormData(this);
      formData.append('teacher_id', <?php echo isset($_SESSION['teacher_id']) ? $_SESSION['teacher_id'] : 0; ?>);
      
      fetch('save_quiz.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Quiz created successfully!');
          this.reset();
          // Keep only one question after reset
          var questionsList = document.getElementById('questions-list');
          questionsList.innerHTML = `
            <div class="question-item">
              <input type="text" name="question[]" class="question-input" placeholder="Enter question here" required/>
              <input type="text" name="answer[]" class="answer-input" placeholder="Correct answer" required style="margin-left:8px;"/>
              <button type="button" class="delete-question-btn" style="margin-left:8px;background:#ffdddd;color:#b71c1c;border:none;padding:6px 12px;border-radius:6px;cursor:pointer;">Delete</button>
            </div>
          `;
          
          // Reattach delete button functionality
          document.querySelector('.delete-question-btn').addEventListener('click', function() {
            if (document.querySelectorAll('.question-item').length > 1) {
              this.parentNode.remove();
            } else {
              alert('You need at least one question!');
            }
          });
        } else {
          alert('Error creating quiz: ' + (data.message || 'Unknown error'));
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error creating quiz. Please try again.');
      });
    });
  });
  </script>
  
  <style>
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
  
  .form-group {
    margin-bottom: 20px;
  }
  
  .form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
  }
  
  .form-group input, .form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 16px;
  }
  
  .question-item {
    display: flex;
    margin-bottom: 12px;
    align-items: center;
  }
  
  .question-input {
    flex: 3;
  }
  
  .answer-input {
    flex: 2;
  }
  
  .button-group {
    margin-top: 20px;
    display: flex;
    gap: 12px;
  }
  
  .add-button {
    background: #2ec4b6;
    color: #fff;
    border: none;
    padding: 10px 16px;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
  }
  
  .add-button:hover {
    background: #25a99c;
  }
  </style>
</body>
</html>