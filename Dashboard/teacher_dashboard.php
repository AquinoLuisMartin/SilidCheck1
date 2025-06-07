<?php
session_start();
include 'db_connect.php';

// Fetch teacher name from DB if not set in session
if (!isset($_SESSION['teacher_name']) && isset($_SESSION['teacher_id'])) {
    $teacher_id = $_SESSION['teacher_id'];
    $stmt = $conn->prepare('SELECT name FROM teachers WHERE id = ?');
    $stmt->bind_param('i', $teacher_id);
    $stmt->execute();
    $stmt->bind_result($teacher_name);
    if ($stmt->fetch()) {
        $_SESSION['teacher_name'] = $teacher_name;
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
</head>
<body>
<div id="teacher-dashboard" >
    <div class="container">
      <aside class="sidebar">
        <div class="logo">
          <img src="../assets/pics/sc_logo.png" alt="SILID Logo" />
        </div>
        <nav>
          <h2>Teacher Dashboard</h2>
          <ul>
            <li id="teacher-menu-home">Home</li>
            <!-- Removed Add and View Lessons -->
            <li id="teacher-menu-create-quiz">Create</li>
            <li id="teacher-menu-view-quiz">View</li>
            <li id="teacher-menu-student-scores">Students Scores</li>
            <li id="teacher-menu-view-students">View Students</li>
          </ul>
          <div class="settings">
            <h3>Account Settings</h3>
            <ul>
              <li id="logout-btn-teacher">Logout</li>
            </ul>
            <!-- Logout Confirmation Modal -->
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
          <div class="header-title middle-left">
            <h1>Teacher Dashboard</h1>
            <p>Welcome back, <?php echo isset($_SESSION['teacher_name']) ? htmlspecialchars($_SESSION['teacher_name']) : (isset($_SESSION['teacher_id']) ? 'Teacher ID: ' . intval($_SESSION['teacher_id']) : 'Teacher'); ?>!</p>
          </div>
          <!-- Search bar removed from header -->
        </header>
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
              <!-- End Sticky Note To-Do Widget -->
            </div>
          </div>
        </section>
    <section id="create-quiz-section" class="dashboard-body" style="display:none;">
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
      </form>
    </div>
  </section>
  <section id="view-quiz-section" class="dashboard-body" style="display:none;">
  <div class="view-quiz-container" style="background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(44, 196, 182, 0.10);padding:36px 32px 32px 32px;margin:40px auto 0 auto;max-width:700px;">
    <h2 style="font-size:1.6em;font-weight:700;margin-bottom:28px;">View Quizzes</h2>
    <div class="quizzes-list">
      <?php
      // Example: Replace this with your actual PHP quiz loop
      if (isset($quizzes) && is_array($quizzes)) {
        foreach ($quizzes as $quiz) {
          echo '<div class="quiz-card">';
          echo '<h3>' . htmlspecialchars($quiz["title"]) . ' <span class="quiz-meta">(' . htmlspecialchars($quiz["subject"]) . ')</span></h3>';
          echo '<div class="quiz-info"><b>Questions:</b> ' . intval($quiz["question_count"]) . '</div>';
          echo '<div class="quiz-info"><b>Students Taken:</b> ' . intval($quiz["taken_count"]) . '</div>';
          echo '<div class="quiz-actions">';
          echo '<button class="delete">Delete</button> ';
          echo '<select><option>Ongoing</option><option>Closed</option></select>';
          echo '</div>';
          echo '</div>';
        }
      }
      ?>
    </div>
  </div>
</section>
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
.quiz-card button.delete {
  background: #ffd6d6 !important;
  color: #d32f2f !important;
  border: none !important;
  margin-right: 8px !important;
}
.quiz-card button.delete:hover {
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
</style>
  <section id="student-scores-section" class="dashboard-body" style="display:none;">
    <div class="student-scores-container">
      <h2>Students Scores</h2>
      <table class="scores-table">
        <tr>
          <th>Student Name</th>
          <th>Task Title</th>
          <th>Subject</th>
          <th>Score</th>
          <th>Date Taken</th>
        </tr>
        <?php
        $sql = "SELECT s.name AS student_name, q.title AS quiz_title, q.subject, qr.score, qr.taken_at FROM quiz_results qr JOIN students s ON qr.student_id = s.id JOIN quizzes q ON qr.quiz_id = q.id ORDER BY qr.taken_at DESC";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['student_name']) . '</td>';
            echo '<td>' . htmlspecialchars($row['quiz_title']) . '</td>';
            echo '<td>' . htmlspecialchars($row['subject']) . '</td>';
            echo '<td>' . htmlspecialchars($row['score']) . '</td>';
            echo '<td>' . htmlspecialchars($row['taken_at']) . '</td>';
            echo '</tr>';
          }
        } else {
          echo '<tr><td colspan="5" style="text-align:center;">No quiz scores found.</td></tr>';
        }
        ?>
      </table>
    </div>
  </section>
  <section id="view-students-section" class="dashboard-body" style="display:none;">
    <div class="view-students-container" style="background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,0.08);padding:32px 24px;margin:40px auto 0 auto;max-width:900px;">
      <h2>Registered Students</h2>
      <div class="student-header-search" style="display:flex;align-items:center;gap:8px;max-width:320px;width:100%;margin-bottom:18px;">
        <input type="text" id="student-search-input" placeholder="Search students by name, email, or grade..." style="flex:1;padding:10px 36px 10px 14px;border-radius:24px;border:1px solid #b7e4c7;background:#f8f9fa;outline:none;box-shadow:none;transition:border 0.2s;"/>
        <span style="position:relative;left:-32px;color:#b7e4c7;font-size:1.2em;pointer-events:none;"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><line x1="16.5" y1="16.5" x2="21" y2="21"/></svg></span>
      </div>
      <table class="students-table" style="width:100%;border-collapse:collapse;margin-top:18px;">
        <thead>
          <tr style="background:#e6f9ed;">
            <th style="padding:12px 8px;border-bottom:2px solid #b7e4c7;text-align:left;">Name</th>
            <th style="padding:12px 8px;border-bottom:2px solid #b7e4c7;text-align:left;">Email</th>
            <th style="padding:12px 8px;border-bottom:2px solid #b7e4c7;text-align:left;">Grade Level</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $result = $conn->query("SELECT name, email, grade FROM students");
        if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo '<tr style="border-bottom:1px solid #e0f7fa;">';
            echo '<td style="padding:10px 8px;">' . htmlspecialchars($row['name']) . '</td>';
            echo '<td style="padding:10px 8px;">' . htmlspecialchars($row['email']) . '</td>';
            echo '<td style="padding:10px 8px;">' . htmlspecialchars($row['grade']) . '</td>';
            echo '</tr>';
          }
        } else {
          echo '<tr><td colspan="3" style="padding:12px 8px;text-align:center;">No students found.</td></tr>';
        }
        ?>
        </tbody>
      </table>
    </div>
  </section>
  <div id="quizViewModal" class="modal" style="display:none;z-index:99999;position:fixed;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.25);">
    <div class="modal-content" style="max-width:400px;text-align:left;position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);background:#fff;padding:32px 24px 24px 24px;border-radius:10px;box-shadow:0 4px 24px rgba(0,0,0,0.15);">
      <h2 id="quizViewModalTitle">Quiz Details</h2>
      <div id="quizViewModalBody"></div>
      <button id="closeQuizViewModal" style="margin-top:18px;">Close</button>
    </div>
  </div>
  <footer class="footer">
    <p>&copy; 2023 SILID. All rights reserved.</p>
  </footer>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
<script src="dashboard.js"></script>
<script>
document.getElementById('logout-btn-teacher').onclick = function() {
  document.getElementById('logoutModal').style.display = 'block';
};
function closeLogoutModal() {
  document.getElementById('logoutModal').style.display = 'none';
}
document.getElementById('confirmLogoutBtn').onclick = function() {
  window.location.href = 'http://localhost/silidcheck/';
};
document.addEventListener('DOMContentLoaded', function() {
  var viewStudentsBtn = document.getElementById('teacher-menu-view-students');
  if (viewStudentsBtn) {
    viewStudentsBtn.addEventListener('click', function() {
      document.querySelectorAll('.dashboard-body').forEach(function(section) {
        section.style.display = 'none';
      });
      var studentsSection = document.getElementById('view-students-section');
      if (studentsSection) studentsSection.style.display = 'block';
      document.querySelectorAll('.sidebar ul li').forEach(function(li) {
        li.classList.remove('active');
      });
      this.classList.add('active');
    });
  }
  var homeBtn = document.getElementById('teacher-menu-home');
  if (homeBtn) {
    homeBtn.addEventListener('click', function() {
      document.querySelectorAll('.dashboard-body').forEach(function(section) {
        section.style.display = 'none';
      });
      var teacherMain = document.querySelector('.teacher-main').parentElement;
      if (teacherMain) teacherMain.style.display = 'block';
      document.querySelectorAll('.sidebar ul li').forEach(function(li) {
        li.classList.remove('active');
      });
      this.classList.add('active');
    });
  }
  // Dynamic quiz loading
  function loadQuizzes() {
    fetch('load_quizzes.php')
      .then(response => response.text())
      .then(html => {
        document.querySelector('.quizzes-list').innerHTML = html;
        attachQuizActionListeners();
        attachQuizStatusListeners();
      });
  }

  // Attach listeners for delete buttons after quizzes are loaded
  function attachQuizActionListeners() {
    document.querySelectorAll('.delete-quiz-btn').forEach(function(btn) {
      btn.onclick = function() {
        if (confirm('Are you sure you want to delete this quiz?')) {
          fetch('delete_quiz.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'quiz_id=' + encodeURIComponent(this.getAttribute('data-quizid'))
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              alert('Quiz deleted successfully.');
              loadQuizzes();
              refreshTeacherScoresAndPerformance(); // Refresh scores and performance
            } else {
              alert('Failed to delete quiz.');
            }
          })
          .catch(() => alert('Error deleting quiz.'));
        }
      };
    });
    document.querySelectorAll('.view-quiz-btn').forEach(function(btn) {
      btn.onclick = function() {
        const quizId = this.getAttribute('data-quizid');
        fetch('get_quiz.php?quiz_id=' + encodeURIComponent(quizId))
          .then(response => response.json())
          .then(data => {
            if (data.success && data.quiz) {
              fetch('quiz_taken_count.php?quiz_id=' + encodeURIComponent(quizId))
                .then(resp => resp.json())
                .then(countData => {
                  let takenCount = countData.success ? countData.count : 'N/A';
                  let html = '';
                  html += '<b>Quiz Title:</b> ' + data.quiz.title + '<br>';
                  html += '<b>Subject:</b> ' + data.quiz.subject + '<br>';
                  html += '<b>Questions:</b> ' + (data.quiz.questions ? data.quiz.questions : 'N/A') + '<br>';
                  html += '<b>Students Taken:</b> ' + takenCount + '<br>';
                  document.getElementById('quizViewModalBody').innerHTML = html;
                  document.getElementById('quizViewModal').style.display = 'block';
                });
            } else {
              alert('Quiz not found.');
            }
          });
      };
    });
    document.getElementById('closeQuizViewModal').onclick = function() {
      document.getElementById('quizViewModal').style.display = 'none';
    };
  }
  // Add this function after attachQuizActionListeners
  function attachQuizStatusListeners() {
    document.querySelectorAll('.quiz-status-select').forEach(function(select) {
      select.onchange = function() {
        const quizId = this.getAttribute('data-quizid');
        const newStatus = this.value;
        fetch('update_quiz_status.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'quiz_id=' + encodeURIComponent(quizId) + '&status=' + encodeURIComponent(newStatus)
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Quiz status updated!');
          } else {
            alert('Failed to update status.');
          }
        })
        .catch(() => alert('Error updating status.'));
      };
    });
  }
  // Create Quiz form
  var createQuizForm = document.getElementById('create-quiz-form');
  if (createQuizForm) {
    createQuizForm.addEventListener('submit', function(e) {
      e.preventDefault();
      var formData = new FormData(createQuizForm);
      // Remove empty questions/answers
      var questions = Array.from(document.querySelectorAll('.question-input')).map(q => q.value.trim());
      var answers = Array.from(document.querySelectorAll('.answer-input')).map(a => a.value.trim());
      // Remove pairs where either is empty
      var filtered = questions.map((q, i) => ({q, a: answers[i]})).filter(pair => pair.q && pair.a);
      if (filtered.length === 0) {
        alert('Please add at least one question and answer.');
        return;
      }
      // Remove all question/answer fields from FormData
      formData.delete('question[]');
      formData.delete('answer[]');
      // Add filtered pairs
      filtered.forEach(pair => {
        formData.append('question[]', pair.q);
        formData.append('answer[]', pair.a);
      });
      fetch('save_quiz.php', {
        method: 'POST',
        body: formData,
        credentials: 'include' // Ensure cookies/session are sent
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Quiz created!');
          createQuizForm.reset();
          document.getElementById('questions-list').innerHTML = '<div class="question-item"><input type="text" name="question[]" class="question-input" placeholder="Enter question here" required/><input type="text" name="answer[]" class="answer-input" placeholder="Correct answer" required style="margin-left:8px;"/><button type="button" class="delete-question-btn" style="margin-left:8px;background:#ffdddd;color:#b71c1c;border:none;padding:6px 12px;border-radius:6px;cursor:pointer;">Delete</button></div>';
          // If View Quizzes section is visible, reload its content
          if (document.getElementById('view-quiz-section').style.display !== 'none') {
            loadQuizzes();
          }
          refreshTeacherScoresAndPerformance(); // Refresh scores and performance
        } else {
          alert('Error: ' + data.message);
        }
      })
      .catch(() => {
        alert('An error occurred while saving the quiz.');
      });
    });
  }
  // Add question button logic
  var addQuestionBtn = document.getElementById('add-question-btn');
  if (addQuestionBtn) {
    addQuestionBtn.addEventListener('click', function() {
      var questionsList = document.getElementById('questions-list');
      var newQuestion = document.createElement('div');
      newQuestion.className = 'question-item';
      newQuestion.innerHTML = '<input type="text" name="question[]" class="question-input" placeholder="Enter question here" required/>' +
        '<input type="text" name="answer[]" class="answer-input" placeholder="Correct answer" required style="margin-left:8px;"/>' +
        '<button type="button" class="delete-question-btn" style="margin-left:8px;background:#ffdddd;color:#b71c1c;border:none;padding:6px 12px;border-radius:6px;cursor:pointer;">Delete</button>';
      questionsList.appendChild(newQuestion);
    });
  }
  // Delete question button logic (event delegation)
  document.getElementById('questions-list').addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('delete-question-btn')) {
      var item = e.target.closest('.question-item');
      if (item) {
        item.remove();
      }
    }
  });
  // Quiz tab navigation
  var createQuizBtn = document.getElementById('teacher-menu-create-quiz');
  if (createQuizBtn) {
    createQuizBtn.addEventListener('click', function() {
      document.querySelectorAll('.dashboard-body').forEach(function(section) {
        section.style.display = 'none';
      });
      var createQuizSection = document.getElementById('create-quiz-section');
      if (createQuizSection) createQuizSection.style.display = 'block';
      document.querySelectorAll('.sidebar ul li').forEach(function(li) {
        li.classList.remove('active');
      });
      this.classList.add('active');
    });
  }
  var viewQuizBtn = document.getElementById('teacher-menu-view-quiz');
  if (viewQuizBtn) {
    viewQuizBtn.addEventListener('click', function() {
      document.querySelectorAll('.dashboard-body').forEach(function(section) {
        section.style.display = 'none';
      });
      var viewQuizSection = document.getElementById('view-quiz-section');
      if (viewQuizSection) viewQuizSection.style.display = 'block';
      document.querySelectorAll('.sidebar ul li').forEach(function(li) {
        li.classList.remove('active');
      });
      this.classList.add('active');
      loadQuizzes();
    });
  }
  var studentScoresBtn = document.getElementById('teacher-menu-student-scores');
  if (studentScoresBtn) {
    studentScoresBtn.addEventListener('click', function() {
      document.querySelectorAll('.dashboard-body').forEach(function(section) {
        section.style.display = 'none';
      });
      var scoresSection = document.getElementById('student-scores-section');
      if (scoresSection) scoresSection.style.display = 'block';
      document.querySelectorAll('.sidebar ul li').forEach(function(li) {
        li.classList.remove('active');
      });
      this.classList.add('active');
    });
  }
  // Live search for Registered Students table only
  var studentSearchInput = document.getElementById('student-search-input');
  if (studentSearchInput) {
    studentSearchInput.addEventListener('input', function() {
      var filter = this.value.toLowerCase();
      var table = document.querySelector('.students-table');
      if (!table) return;
      var rows = table.querySelectorAll('tbody tr');
      rows.forEach(function(row) {
        var text = row.textContent.toLowerCase();
        if (filter === '' || text.indexOf(filter) !== -1) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });
  }
  // Sticky Note Widget Logic
  (function() {
    const noteKey = 'teacher_sticky_note';
    const noteTextarea = document.getElementById('sticky-note-text');
    const saveBtn = document.getElementById('save-sticky-note');
    const clearBtn = document.getElementById('clear-sticky-note');
    // Load note from localStorage
    if (noteTextarea && localStorage.getItem(noteKey)) {
      noteTextarea.value = localStorage.getItem(noteKey);
    }
    if (saveBtn) {
      saveBtn.onclick = function() {
        localStorage.setItem(noteKey, noteTextarea.value);
        saveBtn.textContent = 'Saved!';
        setTimeout(() => { saveBtn.textContent = 'Save'; }, 1200);
      };
    }
    if (clearBtn) {
      clearBtn.onclick = function() {
        noteTextarea.value = '';
        localStorage.removeItem(noteKey);
      };
    }
  })();
  // After quiz creation, update scores and performance
  function refreshTeacherScoresAndPerformance() {
    // Update scores table
    fetch('load_scores.php?teacher=1')
      .then(response => response.text())
      .then(html => {
        const scoresTbody = document.querySelector('#student-scores-section .scores-table tbody');
        if (scoresTbody) scoresTbody.innerHTML = html;
      });
    // Update performance chart if present
    if (document.getElementById('class-performance-section') && document.getElementById('performanceChart')) {
      fetch('load_scores.php?performance=1&teacher=1')
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
  }
});
</script>
</body>
</html>
