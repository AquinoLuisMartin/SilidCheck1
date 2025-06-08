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

try {
    // Use stored procedure to get students
    if (!empty($search)) {
        $stmt = $conn->prepare("CALL SearchStudentsForTeacher(?, ?)");
        $stmt->bind_param('is', $teacher_id, $search);
    } else {
        $stmt = $conn->prepare("CALL GetAllStudentsForTeacher(?)");
        $stmt->bind_param('i', $teacher_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
    }
    $stmt->close();
    $conn->next_result(); // Clear the previous result
} catch (Exception $e) {
    // Log error but don't display it
    error_log("Error in teacher_view_students.php: " . $e->getMessage());
    // Don't do anything else - we'll just display an empty students array
}
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
      flex-wrap: wrap;
      gap: 15px;
      align-items: center;
    }
    .search-panel input {
      padding: 10px;
      border-radius: 6px;
      border: 1px solid #b2dfdb;
      background: #f5f5f5;
      flex-grow: 1;
      min-width: 200px;
    }
    .search-panel button {
      background: #2ec4b6;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
    }
    .search-panel button:hover {
      background: #25aaa0;
    }
    .students-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(44,196,182,0.10);
    }
    .students-table th {
      background: #e6f9ed;
      color: #2a7a66;
      padding: 15px;
      text-align: left;
      font-weight: 600;
      border-bottom: 2px solid #b7e4c7;
    }
    .students-table td {
      padding: 12px 15px;
      border-bottom: 1px solid #e0f7fa;
    }
    .students-table tr:last-child td {
      border-bottom: none;
    }
    .view-button {
      background: #2ec4b6;
      color: white;
      border: none;
      padding: 8px 15px;
      border-radius: 4px;
      font-weight: 500;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      font-size: 14px;
    }
    .view-button:hover {
      background: #25aaa0;
    }
    .no-students {
      text-align: center;
      padding: 30px;
      background: white;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(44,196,182,0.10);
      color: #666;
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
    
    /* Student stats */
    .student-stats {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      margin-bottom: 20px;
    }
    .stat-card {
      flex: 1;
      min-width: 180px;
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
            <!-- Student Statistics -->
            <div class="student-stats">
              <?php
              try {
                if (isset($_SESSION['teacher_id'])) {
                    $teacher_id = intval($_SESSION['teacher_id']);
                    $stmt = $conn->prepare("CALL GetStudentStatistics(?)");
                    $stmt->bind_param('i', $teacher_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result && $row = $result->fetch_assoc()) {
                      echo '<div class="stat-card">';
                      echo '<h3>Total Students</h3>';
                      echo '<div class="value">' . $row['total_students'] . '</div>';
                      echo '</div>';
                      
                      echo '<div class="stat-card">';
                      echo '<h3>Active Students</h3>';
                      echo '<div class="value">' . $row['active_students'] . '</div>';
                      echo '</div>';
                      
                      echo '<div class="stat-card">';
                      echo '<h3>Average Quiz Participation</h3>';
                      echo '<div class="value">' . number_format($row['avg_participation'], 1) . '</div>';
                      echo '</div>';
                    }
                    $stmt->close();
                    $conn->next_result(); // Clear the previous result
                }
              } catch (Exception $e) {
                // If stored procedure fails, show default values
                echo '<div class="stat-card">';
                echo '<h3>Total Students</h3>';
                echo '<div class="value">' . count($students) . '</div>';
                echo '</div>';
                
                echo '<div class="stat-card">';
                echo '<h3>Active Students</h3>';
                echo '<div class="value">' . count($students) . '</div>';
                echo '</div>';
                
                echo '<div class="stat-card">';
                echo '<h3>Average Quiz Participation</h3>';
                echo '<div class="value">-</div>';
                echo '</div>';
              }
              ?>
            </div>
            
            <!-- Search Panel -->
            <div class="search-panel">
              <form method="get" action="" style="display:flex;width:100%;gap:15px;">
                <input type="text" name="search" placeholder="Search by student name or grade" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
                <?php if (!empty($search)): ?>
                  <a href="teacher_view_students.php" style="padding:10px;text-decoration:none;color:#555;">Clear</a>
                <?php endif; ?>
              </form>
            </div>
            
            <!-- Students Table -->
            <?php if (!empty($students)): ?>
              <table class="students-table">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Grade</th>
                    <th>Email</th>
                    <th>Quizzes Taken</th>
                    <th>Avg. Score</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($students as $student): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($student['name']); ?></td>
                      <td><?php echo htmlspecialchars($student['grade']); ?></td>
                      <td><?php echo htmlspecialchars($student['email']); ?></td>
                      <td><?php echo $student['quizzes_taken']; ?></td>
                      <td>
                        <?php 
                        if ($student['avg_score'] !== null) {
                          echo number_format($student['avg_score'], 1) . '%';
                        } else {
                          echo 'N/A';
                        }
                        ?>
                      </td>
                      <td>
                        <a href="get_student_profile.php?id=<?php echo $student['id']; ?>" class="view-button">View Profile</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php else: ?>
              <div class="no-students">
                <?php if (!empty($search)): ?>
                  <p>No students found matching "<?php echo htmlspecialchars($search); ?>". Please try a different search term.</p>
                <?php else: ?>
                  <p>No students available in the system yet. Students will appear here once they register and take quizzes.</p>
                <?php endif; ?>
              </div>
            <?php endif; ?>
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
    // You can add more JavaScript functionality here if needed
  });
  </script>
</body>
</html>