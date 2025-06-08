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
  <title>Teacher Dashboard</title>
  <link rel="stylesheet" href="dashboard.css"/>
</head>
<body>
<div id="teacher-dashboard" >
    <div class="container">
      <!-- Include sidebar with proper links -->
      <aside class="sidebar">
        <div class="logo">
          <img src="../assets/pics/sc_logo.png" alt="SILID Logo" />
        </div>
        <nav>
          <h2>Teacher Dashboard</h2>
          <ul>
            <li id="teacher-menu-home" class="menu-item active">
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
            <!-- Improved Logout Confirmation Modal -->
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
            <h1>Teacher Dashboard</h1>
            <p>Welcome back, <?php echo isset($_SESSION['teacher_name']) ? htmlspecialchars($_SESSION['teacher_name']) : (isset($_SESSION['teacher_id']) ? 'Teacher ID: ' . intval($_SESSION['teacher_id']) : 'Teacher'); ?>!</p>
          </div>
          <!-- Search bar removed from header -->
        </header>
        
        <!-- Home Content -->
        <section class="dashboard-body">
          <div class="teacher-main">
            <div class="today-classes">
              <div class="today-classes-container">
                <h2>Quizzes/Exam to Release</h2>
                <table class="classes-table">
                  <tr>
                    <td>Math Quiz - Algebra</td>
                    <td>8:00 - 9:00 AM</td>
                  </tr>
                  <tr>
                    <td>Science Quiz - Scientific Method</td>
                    <td>10:00 - 11:00 AM</td>
                  </tr>
                  <tr>
                    <td>English Quiz - Reading Comprehension</td>
                    <td>1:30 - 2:30 PM</td>
                  </tr>
                </table>
              </div>
            </div>
            <div class="calendar-widget">
              <h2>Calendar</h2>
              <div class="calendar" style="background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,0.08);padding:12px 8px;margin-bottom:16px;display:flex;justify-content:center;align-items:center;min-height:420px;max-width:420px;width:100%;overflow:auto;">
                <!-- Google Calendar Embed -->
                <iframe src="https://calendar.google.com/calendar/embed?src=en.philippines%23holiday%40group.v.calendar.google.com&ctz=Asia%2FManila&hl=en" style="border:0;width:100%;min-width:320px;max-width:400px;height:400px;" frameborder="0" scrolling="no"></iframe>
              </div>
              <!-- Sticky Note To-Do Widget -->
              <div class="todo-note-widget" style="margin-top:24px;max-width:420px;width:100%;background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(46,196,182,0.10);padding:18px 16px;border:1.5px solid #b2dfdb;margin-left:-20px;">
                <h3 style="margin:0 0 10px 0;color:#2ec4b6;font-size:1.1em;font-weight:bold;letter-spacing:0.5px;">To-Do</h3>
                <textarea id="sticky-note-text" style="width:100%;min-height:70px;border-radius:8px;border:1.5px solid #b2dfdb;padding:10px;font-size:1.05em;resize:vertical;background:#f3fbfa;color:#222;"></textarea>
                <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:8px;">
                  <button id="save-sticky-note" style="background:#2ec4b6;color:#fff;border:none;border-radius:6px;padding:6px 16px;font-weight:600;cursor:pointer;">Save</button>
                  <button id="clear-sticky-note" style="background:#f3fbfa;color:#2ec4b6;border:none;border-radius:6px;padding:6px 16px;font-weight:600;cursor:pointer;">Clear</button>
                </div>
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
  
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
  function closeLogoutModal() {
    document.getElementById('logoutModal').style.display = 'none';
  }
  
  document.addEventListener('DOMContentLoaded', function() {
    // Load saved sticky note content from local storage
    const savedNote = localStorage.getItem('teacherStickyNote');
    if (savedNote) {
      document.getElementById('sticky-note-text').value = savedNote;
    }
    
    // Save sticky note
    document.getElementById('save-sticky-note').addEventListener('click', function() {
      const noteText = document.getElementById('sticky-note-text').value;
      localStorage.setItem('teacherStickyNote', noteText);
      
      // Show save confirmation
      const notification = document.createElement('div');
      notification.textContent = 'Note saved!';
      notification.style.position = 'fixed';
      notification.style.bottom = '20px';
      notification.style.right = '20px';
      notification.style.background = '#4CAF50';
      notification.style.color = 'white';
      notification.style.padding = '10px 20px';
      notification.style.borderRadius = '5px';
      notification.style.boxShadow = '0 2px 10px rgba(0,0,0,0.2)';
      document.body.appendChild(notification);
      
      setTimeout(function() {
        notification.remove();
      }, 2000);
    });
    
    // Clear sticky note
    document.getElementById('clear-sticky-note').addEventListener('click', function() {
      document.getElementById('sticky-note-text').value = '';
      localStorage.removeItem('teacherStickyNote');
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
  </style>
</body>
</html>
