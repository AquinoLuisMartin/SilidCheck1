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

// Get all students list
$students = [];
$teacher_id = isset($_SESSION['teacher_id']) ? intval($_SESSION['teacher_id']) : 0;
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Use stored procedure to get students
if (!empty($search)) {
    $stmt = $conn->prepare("CALL SearchStudents(?)");
    $stmt->bind_param('s', $search);
} else {
    $stmt = $conn->prepare("CALL GetAllStudents()");
}
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>View Students - Teacher Dashboard</title>
  <link rel="stylesheet" href="dashboard.css"/>
  <style>
    .students-container {
      max-width: 900px;
      margin: 20px auto;
      padding: 0 15px;
    }
    .search-panel {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(44,196,182,0.10);
      padding: 20px;
      margin-bottom: 20px;
      display: flex;
      gap: 15px;
      align-items: center;
    }
    .search-panel input {
      flex: 1;
      padding: 12px;
      border-radius: 6px;
      border: 1px solid #b2dfdb;
      font-size: 16px;
    }
    .search-panel button {
      background: #2ec4b6;
      color: white;
      border: none;
      padding: 12px 20px;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
      font-size: 16px;
    }
    .search-panel button:hover {
      background: #25aaa0;
    }
    .students-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 20px;
    }
    .student-card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(44,196,182,0.10);
      padding: 20px;
      transition: transform 0.2s, box-shadow 0.2s;
      position: relative;
    }
    .student-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 15px rgba(44,196,182,0.15);
    }
    .student-card .avatar {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: #e0f7fa;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 32px;
      color: #2ec4b6;
      margin: 0 auto 15px auto;
    }
    .student-card h3 {
      text-align: center;
      margin: 0 0 5px 0;
      font-size: 18px;
      font-weight: 600;
    }
    .student-card .student-id {
      text-align: center;
      color: #666;
      margin-bottom: 15px;
      font-size: 14px;
    }
    .student-card .details {
      margin-top: 15px;
    }
    .student-card .details p {
      margin: 5px 0;
      display: flex;
      justify-content: space-between;
      font-size: 14px;
    }
    .student-card .details strong {
      color: #333;
    }
    .student-card .view-btn {
      display: block;
      background: #2ec4b6;
      color: white;
      text-align: center;
      padding: 10px;
      border-radius: 6px;
      text-decoration: none;
      margin-top: 15px;
      font-weight: 600;
      cursor: pointer;
    }
    .student-card .view-btn:hover {
      background: #25aaa0;
    }
    .no-results {
      text-align: center;
      padding: 40px 20px;
      background: white;
      border-radius: 12px;
      grid-column: 1 / -1;
      box-shadow: 0 2px 10px rgba(44,196,182,0.10);
    }
    .status-badge {
      position: absolute;
      top: 15px;
      right: 15px;
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
    }
    .status-active {
      background: #e6f9ed;
      color: #2a7a66;
    }
    .status-inactive {
      background: #ffebee;
      color: #d32f2f;
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
    
    /* Student Profile Modal */
    .student-modal {
      display: none;
      position: fixed;
      z-index: 9999;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.4);
      overflow-y: auto;
    }
    .student-modal-content {
      background-color: #fefefe;
      margin: 50px auto;
      padding: 20px;
      border-radius: 12px;
      width: 80%;
      max-width: 700px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }
    .close-modal {
      color: #aaa;
      float: right;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
    }
    .close-modal:hover,
    .close-modal:focus {
      color: black;
      text-decoration: none;
      cursor: pointer;
    }
    .student-profile {
      padding: 20px 0;
    }
    .student-profile-header {
      display: flex;
      align-items: center;
      margin-bottom: 20px;
    }
    .student-profile-avatar {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      background: #e0f7fa;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 40px;
      color: #2ec4b6;
      margin-right: 20px;
    }
    .student-profile-info h2 {
      margin: 0 0 5px 0;
    }
    .student-profile-info p {
      margin: 0;
      color: #666;
    }
    .student-stats {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      margin: 20px 0;
    }
    .student-stat-card {
      flex: 1;
      min-width: 120px;
      background: #f8f9fa;
      border-radius: 10px;
      padding: 15px;
      text-align: center;
    }
    .student-stat-card .value {
      font-size: 24px;
      font-weight: bold;
      color: #2ec4b6;
      margin-bottom: 5px;
    }
    .student-stat-card .label {
      font-size: 14px;
      color: #666;
    }
    .student-scores-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    .student-scores-table th {
      background: #e6f9ed;
      text-align: left;
      padding: 10px;
    }
    .student-scores-table td {
      padding: 10px;
      border-bottom: 1px solid #eee;
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
            <li id="teacher-menu-student-scores" class="menu-item">
              <a href="teacher_student_scores.php">
                <span class="menu-icon">📊</span>
                <span class="menu-text">Students Scores</span>
              </a>
            </li>
            <li id="teacher-menu-view-students" class="menu-item active">
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
            <h1>View Students</h1>
            <p>Welcome back, <?php echo isset($_SESSION['teacher_name']) ? htmlspecialchars($_SESSION['teacher_name']) : (isset($_SESSION['teacher_id']) ? 'Teacher ID: ' . intval($_SESSION['teacher_id']) : 'Teacher'); ?>!</p>
          </div>
        </header>
        
        <!-- View Students Content -->
        <section class="dashboard-body">
          <div class="students-container">
            <!-- Search Panel -->
            <div class="search-panel">
              <input type="text" id="search-input" placeholder="Search by student name or ID" value="<?php echo htmlspecialchars($search); ?>">
              <button id="search-btn">Search</button>
              <button id="reset-search-btn">Reset</button>
            </div>
            
            <!-- Students Grid -->
            <div class="students-grid">
              <?php
              if (!empty($students)) {
                foreach ($students as $student) {
                  // Get initials for avatar
                  $initials = strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1));
                  
                  echo '<div class="student-card">';
                  echo '<span class="status-badge ' . ($student['is_active'] ? 'status-active' : 'status-inactive') . '">' . ($student['is_active'] ? 'Active' : 'Inactive') . '</span>';
                  echo '<div class="avatar">' . $initials . '</div>';
                  echo '<h3>' . htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) . '</h3>';
                  echo '<div class="student-id">ID: ' . htmlspecialchars($student['student_id']) . '</div>';
                  echo '<div class="details">';
                  echo '<p><strong>Email:</strong> ' . htmlspecialchars($student['email']) . '</p>';
                  echo '<p><strong>Year Level:</strong> ' . htmlspecialchars($student['year_level']) . '</p>';
                  echo '<p><strong>Quizzes Taken:</strong> ' . intval($student['quizzes_taken']) . '</p>';
                  echo '<p><strong>Avg. Score:</strong> ' . number_format(floatval($student['avg_score']), 1) . '%</p>';
                  echo '</div>';
                  echo '<a class="view-btn" onclick="viewStudentProfile(' . $student['id'] . ')">View Profile</a>';
                  echo '</div>';
                }
              } else {
                echo '<div class="no-results">';
                echo '<h3>No students found</h3>';
                echo '<p>Try a different search term or view all students.</p>';
                echo '</div>';
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
  
  <!-- Student Profile Modal -->
  <div id="studentProfileModal" class="student-modal">
    <div class="student-modal-content">
      <span class="close-modal" onclick="closeStudentProfileModal()">&times;</span>
      <div id="studentProfileContent" class="student-profile">
        <!-- Content will be loaded here via AJAX -->
        <div style="text-align:center;padding:30px;">Loading student profile...</div>
      </div>
    </div>
  </div>
  
  <script>
  function closeLogoutModal() {
    document.getElementById('logoutModal').style.display = 'none';
  }
  
  function viewStudentProfile(studentId) {
    // Show modal and load content
    document.getElementById('studentProfileModal').style.display = 'block';
    
    // Fetch student profile data
    fetch('get_student_profile.php?id=' + studentId)
      .then(response => response.text())
      .then(html => {
        document.getElementById('studentProfileContent').innerHTML = html;
      })
      .catch(error => {
        console.error('Error fetching student profile:', error);
        document.getElementById('studentProfileContent').innerHTML = '<div style="text-align:center;padding:30px;"><h3>Error</h3><p>Could not load student profile. Please try again.</p></div>';
      });
  }
  
  function closeStudentProfileModal() {
    document.getElementById('studentProfileModal').style.display = 'none';
  }
  
  document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    document.getElementById('search-btn').addEventListener('click', function() {
      const searchTerm = document.getElementById('search-input').value.trim();
      window.location.href = 'teacher_view_students.php?search=' + encodeURIComponent(searchTerm);
    });
    
    // Reset search
    document.getElementById('reset-search-btn').addEventListener('click', function() {
      window.location.href = 'teacher_view_students.php';
    });
    
    // Enter key in search input
    document.getElementById('search-input').addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        document.getElementById('search-btn').click();
      }
    });
    
    // Close modal when clicking outside
    window.onclick = function(event) {
      const modal = document.getElementById('studentProfileModal');
      if (event.target === modal) {
        closeStudentProfileModal();
      }
    };
  });
  </script>
</body>
</html>