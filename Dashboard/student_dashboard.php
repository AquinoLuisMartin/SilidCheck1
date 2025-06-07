<?php
session_start();
include 'db_connect.php';
// Fetch student name from DB if not set in session
if (!isset($_SESSION['student_name']) && isset($_SESSION['student_id'])) {
    $student_id = $_SESSION['student_id'];
    $stmt = $conn->prepare('SELECT name FROM students WHERE id = ?');
    $stmt->bind_param('i', $student_id);
    $stmt->execute();
    $stmt->bind_result($student_name);
    if ($stmt->fetch()) {
        $_SESSION['student_name'] = $student_name;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard</title>
  <link rel="stylesheet" href="dashboard.css"/>
  <style>
.student-dashboard-main {
  display: flex;
  flex-wrap: wrap;
  gap: 32px;
  justify-content: center;
  align-items: flex-start;
  margin-top: 40px;
}
.student-main-card {
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 2px 16px rgba(44,196,182,0.10);
  padding: 36px 32px 32px 32px;
  max-width: 600px;
  min-width: 340px;
  flex: 1 1 340px;
}
.student-side-widgets {
  display: flex;
  flex-direction: column;
  gap: 28px;
  min-width: 340px;
  max-width: 420px;
  flex: 1 1 340px;
}
.student-calendar-widget {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 2px 12px rgba(44,196,182,0.10);
  padding: 18px 16px 12px 16px;
  margin-bottom: 0;
}
.student-todo-widget {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 2px 12px rgba(44,196,182,0.10);
  padding: 18px 16px 16px 16px;
  display: flex;
  flex-direction: column;
}
.student-todo-widget h3 {
  margin: 0 0 10px 0;
  color: #2ec4b6;
  font-size: 1.1em;
  font-weight: bold;
  letter-spacing: 0.5px;
}
.student-todo-widget textarea {
  width: 100%;
  min-height: 70px;
  border-radius: 8px;
  border: 1.5px solid #b2dfdb;
  padding: 10px;
  font-size: 1.05em;
  resize: vertical;
  background: #f3fbfa;
  color: #222;
  margin-bottom: 16px;
  box-sizing: border-box;
}
.student-todo-widget .todo-actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-end;
  align-items: center;
  gap: 10px;
  margin-top: 0;
}
.student-todo-widget button {
  background: #2ec4b6;
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 6px 16px;
  font-weight: 600;
  cursor: pointer;
  margin-top: 0;
}
.student-todo-widget button.clear {
  background: #f3fbfa;
  color: #2ec4b6;
}
#student-quiz-status {
  position: absolute;
  right: 48px;
  top: 72px;
  z-index: 2;
  margin-top: 8px;
  background: #fff;
  border-radius: 16px;
  padding: 6px 18px;
  font-size: 1.05em;
  box-shadow: 0 1px 6px rgba(44,196,182,0.08);
  font-weight: 600;
  color: #222;
  min-width: 90px;
  text-align: center;
}
@media (max-width: 900px) {
  .student-header-search {
    right: 12px;
    top: 16px;
    max-width: 180px;
  }
}
  </style>
</head>
<body>
  <div id="student-dashboard">
 <div class="container">
    <aside class="sidebar">
      <div class="logo">
        <img src="../assets/pics/sc_logo.png" alt="SILID Logo" />
      </div>
      <nav>
        <h2>Student Dashboard</h2>
        <ul>
          <li id="menu-home" class="active">Home</li>
          <li id="menu-exams">Quiz/Exams</li>
          <li id="menu-performance">Performance</li>
          <li id="menu-scores">Scores</li>
        </ul>
        <div class="settings">
          <h3>Account Settings</h3>
          <ul>
            <li id="logout-btn">Logout</li>
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
          <h1>Student Dashboard</h1>
          <p>Welcome back, <?php echo isset($_SESSION['student_name']) ? htmlspecialchars($_SESSION['student_name']) : 'Student'; ?>!</p>
        </div>
        <div id="student-quiz-status" style="position:absolute;right:48px;top:72px;z-index:2;margin-top:8px;background:#fff;border-radius:16px;padding:6px 18px;font-size:1.05em;box-shadow:0 1px 6px rgba(44,196,182,0.08);font-weight:600;color:#222;min-width:90px;text-align:center;">
          Quizzes: <span id="quiz-count">0</span>
        </div>
      </header>
      <div id="dashboard-main" class="student-dashboard-main">
  <div class="student-main-card">
    <h2 style="font-size:1.3em;font-weight:700;margin-bottom:18px;">Today: Ongoing Quizzes</h2>
    <p class="section-desc" id="ongoing-desc">You have no pending quizzes for today. Great job!</p>
    <ul id="student-todo-quiz-list">
      <li>No ongoing quizzes.</li>
    </ul>
  </div>
  <div class="student-side-widgets">
    <div class="student-calendar-widget">
      <h2 style="font-size:1.2em;font-weight:700;margin-bottom:10px;text-align:center;">Calendar</h2>
      <div class="calendar" style="background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,0.08);padding:0;margin-bottom:0;display:flex;justify-content:center;align-items:center;min-height:320px;max-width:400px;width:100%;overflow:auto;">
        <iframe src="https://calendar.google.com/calendar/embed?src=en.philippines%23holiday%40group.v.calendar.google.com&ctz=Asia%2FManila&hl=en" style="border:0;width:100%;min-width:320px;max-width:400px;height:320px;" frameborder="0" scrolling="no"></iframe>
      </div>
    </div>
    <div class="student-todo-widget">
      <h3>To-Do</h3>
      <textarea id="student-sticky-note"></textarea>
      <div class="todo-actions">
        <button id="save-student-sticky-note">Save</button>
        <button class="clear" id="clear-student-sticky-note">Clear</button>
      </div>
    </div>
  </div>
</div>
      <section id="scores-section" class="dashboard-body" style="display:none;">
    <div class="scores-container">
      <h2>My Quiz Scores</h2>
      <table class="scores-table" style="width:100%;border-collapse:collapse;margin-top:18px;">
        <thead>
          <tr style="background:#e6f9ed;">
            <th style="padding:12px 8px;border-bottom:2px solid #b7e4c7;text-align:left;">Task Title</th>
            <th style="padding:12px 8px;border-bottom:2px solid #b7e4c7;text-align:left;">Subject</th>
            <th style="padding:12px 8px;border-bottom:2px solid #b7e4c7;text-align:left;">Score</th>
            <th style="padding:12px 8px;border-bottom:2px solid #b7e4c7;text-align:left;">Total Items</th>
            <th style="padding:12px 8px;border-bottom:2px solid #b7e4c7;text-align:left;">Date Taken</th>
          </tr>
        </thead>
        <tbody>
        <?php
        if (isset($_SESSION['student_id'])) {
          $student_id = intval($_SESSION['student_id']);
          $sql = "SELECT qr.*, q.title, q.subject, (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) as total_items FROM quiz_results qr JOIN quizzes q ON qr.quiz_id = q.id WHERE qr.student_id = $student_id ORDER BY qr.taken_at DESC";
          $result = $conn->query($sql);
          if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
              echo '<tr style="border-bottom:1px solid #e0f7fa;">';
              echo '<td style="padding:10px 8px;">' . htmlspecialchars($row['title']) . '</td>';
              echo '<td style="padding:10px 8px;">' . htmlspecialchars($row['subject']) . '</td>';
              echo '<td style="padding:10px 8px;">' . htmlspecialchars($row['score']) . '</td>';
              echo '<td style="padding:10px 8px;">' . htmlspecialchars($row['total_items']) . '</td>';
              echo '<td style="padding:10px 8px;">' . htmlspecialchars($row['taken_at']) . '</td>';
              echo '</tr>';
            }
          } else {
            echo '<tr><td colspan="5" style="padding:12px 8px;text-align:center;">No quiz scores found.</td></tr>';
          }
        } else {
          echo '<tr><td colspan="5" style="padding:12px 8px;text-align:center;">Not logged in.</td></tr>';
        }
        ?>
        </tbody>
      </table>
    </div>
  </section>
      <section id="performance-section" class="dashboard-body" style="display:none;">
        <div class="performance-container" style="max-width:700px;margin:auto;">
          <h2 style="font-size:1.1em;margin-bottom:10px;">Quiz Performance</h2>
          <canvas id="performanceChart" width="600" height="300" style="display:block;margin:auto;"></canvas>
        </div>
      </section>
      <!-- Removed Class Schedule and Lessons sections -->
      <div id="quizzes-section" class="dashboard-body" style="display:none;"></div>
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
  document.getElementById('logout-btn').onclick = function() {
  document.getElementById('logoutModal').style.display = 'block';
};
function closeLogoutModal() {
  document.getElementById('logoutModal').style.display = 'none';
}
document.getElementById('confirmLogoutBtn').onclick = function() {
  window.location.href = '../logout.php';
};
// Update quiz count in header and keep in sync with pending quizzes
function updateQuizCountAndList() {
  fetch('load_quizzes.php?student_ongoing=1')
    .then(response => response.json())
    .then(data => {
      var count = (data.success && data.quizzes) ? data.quizzes.length : 0;
      var quizCountSpan = document.getElementById('quiz-count');
      if (quizCountSpan) quizCountSpan.textContent = count;
      // Also update the main dashboard list
      var list = document.getElementById('student-todo-quiz-list');
      if (list) {
        list.innerHTML = '';
        if (count > 0) {
          data.quizzes.forEach(function(quiz) {
            var li = document.createElement('li');
            li.innerHTML = '<b>' + quiz.title + '</b> <span style="color:#2ec4b6;">(' + quiz.subject + ')</span>';
            list.appendChild(li);
          });
          document.getElementById('ongoing-desc').textContent = 'You have pending quizzes for today!';
        } else {
          var li = document.createElement('li');
          li.textContent = 'No ongoing quizzes.';
          list.appendChild(li);
          document.getElementById('ongoing-desc').textContent = 'You have no pending quizzes for today. Great job!';
        }
      }
    });
}
document.addEventListener('DOMContentLoaded', function() {
  // Helper: Hide all tabs
  function hideAllTabs() {
    document.getElementById('dashboard-main').style.display = 'none';
    var tabIds = ['quizzes-section', 'scores-section', 'performance-section'];
    tabIds.forEach(function(id) {
      var el = document.getElementById(id);
      if (el) el.style.display = 'none';
    });
  }
  // Home tab
  var homeBtn = document.getElementById('menu-home');
  if (homeBtn) {
    homeBtn.addEventListener('click', function() {
      hideAllTabs();
      document.getElementById('dashboard-main').style.display = 'flex';
      document.querySelectorAll('.sidebar ul li').forEach(function(li) { li.classList.remove('active'); });
      this.classList.add('active');
      updateQuizCountAndList();
    });
  }
  // Quiz/Exams tab
  var examsBtn = document.getElementById('menu-exams');
  if (examsBtn) {
    examsBtn.addEventListener('click', function() {
      hideAllTabs();
      var quizzesSection = document.getElementById('quizzes-section');
      if (quizzesSection) quizzesSection.style.display = 'block';
      document.querySelectorAll('.sidebar ul li').forEach(function(li) { li.classList.remove('active'); });
      this.classList.add('active');
      fetch('load_quizzes.php?student_ongoing=1')
        .then(response => response.json())
        .then(data => {
          var quizCountSpan = document.getElementById('quiz-count');
          if (quizCountSpan) quizCountSpan.textContent = (data.success && data.quizzes) ? data.quizzes.length : 0;
          quizzesSection.innerHTML = '<h2 style="font-size:1.5em;font-weight:700;margin-bottom:24px;">Available Quizzes/Exams</h2>';
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
    });
  }
  // Scores tab
  var scoresBtn = document.getElementById('menu-scores');
  if (scoresBtn) {
    scoresBtn.addEventListener('click', function() {
      hideAllTabs();
      var scoresSection = document.getElementById('scores-section');
      if (scoresSection) scoresSection.style.display = 'block';
      document.querySelectorAll('.sidebar ul li').forEach(function(li) { li.classList.remove('active'); });
      this.classList.add('active');
    });
  }
  // Performance tab
  var performanceBtn = document.getElementById('menu-performance');
  if (performanceBtn) {
    performanceBtn.addEventListener('click', function() {
      hideAllTabs();
      var perfSection = document.getElementById('performance-section');
      if (perfSection) perfSection.style.display = 'block';
      document.querySelectorAll('.sidebar ul li').forEach(function(li) { li.classList.remove('active'); });
      this.classList.add('active');
      fetch('load_scores.php?performance=1')
        .then(response => response.json())
        .then(function(data) {
          var ctx = document.getElementById('performanceChart').getContext('2d');
          if (window.performanceChartInstance) window.performanceChartInstance.destroy();
          window.performanceChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
              labels: data.labels,
              datasets: [{
                label: '',
                data: data.scores,
                backgroundColor: ['#2ec4b6', '#ffb703', '#ff6f61'],
                borderRadius: 6,
                barPercentage: 0.6,
                categoryPercentage: 0.7,
                maxBarThickness: 18
              }]
            },
            options: {
              responsive: false,
              animation: false,
              plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
              },
              scales: {
                x: {
                  grid: { display: false },
                  ticks: { font: { size: 11 } }
                },
                y: {
                  beginAtZero: true,
                  max: 100,
                  grid: { display: false },
                  ticks: { font: { size: 11 }, stepSize: 20 }
                }
              },
              layout: { padding: 0 }
            }
          });
        });
    });
  }
  // --- Quiz Modal Logic ---
  document.addEventListener('click', function(e) {
    if (e.target.classList.contains('take-quiz-btn')) {
      const quizId = e.target.getAttribute('data-quizid');
      fetch('get_quiz.php?quiz_id=' + encodeURIComponent(quizId))
        .then(response => response.json())
        .then(data => {
          if (data.success && data.quiz) {
            const quiz = data.quiz;
            document.getElementById('quiz-title').textContent = quiz.title;
            const form = document.getElementById('quiz-form');
            form.innerHTML = quiz.questions.map((q, i) =>
              `<p>${q}<br><input type="text" name="answer[${i}]" required style="width:90%;padding:8px;margin-top:4px;"></p>`
            ).join('');
            form.setAttribute('data-quizid', quiz.id);
            document.getElementById('quiz-modal').style.display = 'block';
          } else {
            alert('Quiz not found.');
          }
        });
    }
  });
  document.getElementById('close-quiz').onclick = function() {
    document.getElementById('quiz-modal').style.display = 'none';
  };
  document.getElementById('submit-quiz').onclick = function() {
    const form = document.getElementById('quiz-form');
    const quizId = form.getAttribute('data-quizid');
    const formData = new FormData(form);
    // Convert FormData to URLSearchParams for POST
    const params = new URLSearchParams();
    params.append('quiz_id', quizId);
    // Collect answers as array
    const answers = [];
    for (let [key, value] of formData.entries()) {
      if (key.startsWith('answer[')) {
        answers.push(value);
      }
    }
    answers.forEach((ans, i) => params.append(`answer[${i}]`, ans));
    fetch('submit_quiz.php', {
      method: 'POST',
      body: params
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert(`Submitted! You scored ${data.score}/${data.total}`);
          document.getElementById('quiz-modal').style.display = 'none';
          // Always update scores table after taking a quiz
          fetch('load_scores.php')
            .then(response => response.text())
            .then(html => {
              const scoresTbody = document.querySelector('#scores-section .scores-table tbody');
              if (scoresTbody) scoresTbody.innerHTML = html;
            });
          // Always update quiz count and list (for dashboard/home)
          updateQuizCountAndList();
          // Refresh performance tab if visible
          if (document.getElementById('performance-section').style.display !== 'none') {
            fetch('load_scores.php?performance=1')
              .then(response => response.json())
              .then(function(data) {
                var ctx = document.getElementById('performanceChart').getContext('2d');
                if (window.performanceChartInstance) window.performanceChartInstance.destroy();
                window.performanceChartInstance = new Chart(ctx, {
                  type: 'bar',
                  data: {
                    labels: data.labels,
                    datasets: [{
                      label: '',
                      data: data.scores,
                      backgroundColor: ['#2ec4b6', '#ffb703', '#ff6f61'],
                      borderRadius: 6,
                      barPercentage: 0.6,
                      categoryPercentage: 0.7,
                      maxBarThickness: 18
                    }]
                  },
                  options: {
                    responsive: false,
                    animation: false,
                    plugins: {
                      legend: { display: false },
                      tooltip: { enabled: false }
                    },
                    scales: {
                      x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                      },
                      y: {
                        beginAtZero: true,
                        max: 100,
                        grid: { display: false },
                        ticks: { font: { size: 11 }, stepSize: 20 }
                      }
                    },
                    layout: { padding: 0 }
                  }
                });
              });
          }
          // If neither tab is visible, do nothing (dashboard/home or exams tab)
        } else {
          alert(data.message || 'Submission failed.');
        }
      });
  };
  // Sticky note localStorage logic
const stickyNote = document.getElementById('student-sticky-note');
const saveBtn = document.getElementById('save-student-sticky-note');
const clearBtn = document.getElementById('clear-student-sticky-note');

// Load sticky note from localStorage on page load
stickyNote.value = localStorage.getItem('studentStickyNote') || '';

saveBtn.onclick = function() {
  localStorage.setItem('studentStickyNote', stickyNote.value);
  saveBtn.textContent = 'Saved!';
  setTimeout(() => { saveBtn.textContent = 'Save'; }, 1200);
};

clearBtn.onclick = function() {
  stickyNote.value = '';
  localStorage.removeItem('studentStickyNote');
};
  // Initial load: show dashboard main only
  hideAllTabs();
  document.getElementById('dashboard-main').style.display = 'flex';
  updateQuizCountAndList();
});

// Enhanced active menu highlighting
document.addEventListener('DOMContentLoaded', function() {
  // Function to set active menu item with enhanced visual feedback
  function setActiveMenuItem(element) {
    // Remove active class from all menu items
    const allMenuItems = document.querySelectorAll('.sidebar nav ul li, .settings ul li');
    allMenuItems.forEach(item => {
      item.classList.remove('active');
    });
    
    // Add active class to clicked item with visual feedback
    element.classList.add('active');
    
    // Create ripple effect
    const ripple = document.createElement('span');
    ripple.classList.add('menu-ripple');
    ripple.style.position = 'absolute';
    ripple.style.background = 'rgba(46, 196, 182, 0.1)';
    ripple.style.borderRadius = '50%';
    ripple.style.transform = 'scale(0)';
    ripple.style.animation = 'ripple-effect 0.6s linear';
    ripple.style.pointerEvents = 'none';
    
    element.appendChild(ripple);
    
    // Remove ripple after animation
    setTimeout(() => {
      ripple.remove();
    }, 700);
    
    // Store active menu in session storage
    sessionStorage.setItem('activeMenu', element.id);
  }
  
  // Add keyframes for ripple effect to document
  const style = document.createElement('style');
  style.textContent = `
    @keyframes ripple-effect {
      0% { transform: scale(0); opacity: 1; }
      50% { transform: scale(10); opacity: 0.3; }
      100% { transform: scale(20); opacity: 0; }
    }
  `;
  document.head.appendChild(style);
  
  // Set up click handlers for all menu items
  const menuItems = document.querySelectorAll('.sidebar nav ul li, .settings ul li');
  menuItems.forEach(item => {
    item.addEventListener('click', function() {
      setActiveMenuItem(this);
    });
  });
  
  // Restore active menu from session storage
  const activeMenuId = sessionStorage.getItem('activeMenu') || 'menu-home';
  const activeMenu = document.getElementById(activeMenuId);
  if (activeMenu) {
    setActiveMenuItem(activeMenu);
  }
  
  // Create visual tracker that shows current position
  const tracker = document.createElement('div');
  tracker.classList.add('current-page-indicator');
  document.querySelector('.sidebar nav ul').appendChild(tracker);
  
  function updateTracker() {
    const activeItem = document.querySelector('.sidebar nav ul li.active');
    if (activeItem) {
      const itemRect = activeItem.getBoundingClientRect();
      const navRect = document.querySelector('.sidebar nav ul').getBoundingClientRect();
      
      tracker.style.top = `${activeItem.offsetTop}px`;
      tracker.style.height = `${itemRect.height}px`;
      tracker.style.transform = 'scaleY(1)';
    } else {
      tracker.style.transform = 'scaleY(0)';
    }
  }
  
  // Update tracker on page load and when menu changes
  updateTracker();
  
  menuItems.forEach(item => {
    item.addEventListener('click', updateTracker);
  });
  
  // Update tracker on window resize
  window.addEventListener('resize', updateTracker);
});

// Sidebar mobile responsiveness
document.addEventListener('DOMContentLoaded', function() {
  // Create mobile toggle button if it doesn't exist
  if (!document.querySelector('.sidebar-toggle')) {
    const toggle = document.createElement('button');
    toggle.className = 'sidebar-toggle';
    toggle.innerHTML = '☰';
    toggle.setAttribute('aria-label', 'Toggle menu');
    document.body.appendChild(toggle);
    
    // Create backdrop
    const backdrop = document.createElement('div');
    backdrop.className = 'sidebar-backdrop';
    document.body.appendChild(backdrop);
    
    // Toggle sidebar on button click
    toggle.addEventListener('click', function() {
      const sidebar = document.querySelector('.sidebar');
      sidebar.classList.toggle('active');
      backdrop.classList.toggle('active');
    });
    
    // Close sidebar when clicking backdrop
    backdrop.addEventListener('click', function() {
      const sidebar = document.querySelector('.sidebar');
      sidebar.classList.remove('active');
      backdrop.classList.remove('active');
    });
  }
  
  // Add active class to current menu item
  const currentPath = window.location.pathname;
  const menuItems = document.querySelectorAll('.sidebar nav ul li');
  menuItems.forEach(item => {
    item.addEventListener('click', function() {
      menuItems.forEach(i => i.classList.remove('active'));
      this.classList.add('active');
    });
  });
});
</script>
</body>
</html>
