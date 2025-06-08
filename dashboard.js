document.addEventListener('DOMContentLoaded', function () {
  const role = localStorage.getItem('role');
  const teacherDashboard = document.getElementById('teacher-dashboard');
  const studentDashboard = document.getElementById('student-dashboard');

  if (role === 'teacher') {
    teacherDashboard.style.display = 'flex';
    studentDashboard.style.display = 'none';
  } else if (role === 'student') {
    teacherDashboard.style.display = 'none';
    studentDashboard.style.display = 'flex';
  } else {
    if (teacherDashboard) teacherDashboard.style.display = 'none';
    if (studentDashboard) studentDashboard.style.display = 'none';
    document.body.innerHTML = '<h2 style="text-align:center;">No role set. Please log in.</h2>';
  }

  // Logout for both dashboards
  const logoutBtnStudent = document.getElementById('logout-btn');
  const logoutBtnTeacher = document.getElementById('logout-btn-teacher');
  const logoutBtnTop = document.getElementById('logout-btn-top');
  [logoutBtnStudent, logoutBtnTeacher, logoutBtnTop].forEach(btn => {
    if (btn) {
      btn.addEventListener('click', function() {
        localStorage.removeItem('role');
        window.location.href = 'index.html';
      });
    }
  });

  // Sidebar menu items (Student Dashboard)
  const menuSchedule = document.getElementById('menu-schedule');
  const dashboardMain = document.getElementById('dashboard-main');
  const scheduleSection = document.getElementById('schedule-section');
  const sidebarItems = document.querySelectorAll('.sidebar ul li');

  if (menuSchedule && dashboardMain && scheduleSection) {
    menuSchedule.addEventListener('click', function() {
      sidebarItems.forEach(li => li.classList.remove('active'));
      menuSchedule.classList.add('active');
      dashboardMain.style.display = 'none';
      scheduleSection.style.display = 'flex';
      if (document.getElementById('lessons-section')) document.getElementById('lessons-section').style.display = 'none';
      if (document.getElementById('scores-section')) document.getElementById('scores-section').style.display = 'none';
    });
  }

  const menuLessons = document.getElementById('menu-lessons');
  const lessonsSection = document.getElementById('lessons-section');
  if (menuLessons && lessonsSection) {
    menuLessons.addEventListener('click', function() {
      sidebarItems.forEach(li => li.classList.remove('active'));
      menuLessons.classList.add('active');
      dashboardMain.style.display = 'none';
      scheduleSection.style.display = 'none';
      lessonsSection.style.display = 'flex';
      if (document.getElementById('scores-section')) document.getElementById('scores-section').style.display = 'none';
    });
  }

  const menuScores = document.getElementById('menu-scores');
  const scoresSection = document.getElementById('scores-section');
  if (menuScores && scoresSection) {
    menuScores.addEventListener('click', function() {
      sidebarItems.forEach(li => li.classList.remove('active'));
      menuScores.classList.add('active');
      dashboardMain.style.display = 'none';
      scheduleSection.style.display = 'none';
      lessonsSection.style.display = 'none';
      scoresSection.style.display = 'flex';
    });
  }

  // --- Teacher Dashboard Sidebar Logic ---
  const teacherMenuAddLessons = document.getElementById('teacher-menu-add-lessons');
  const teacherMenuViewLessons = document.getElementById('teacher-menu-view-lessons');
  const teacherMenuCreateQuiz = document.getElementById('teacher-menu-create-quiz');
  const teacherMenuViewQuiz = document.getElementById('teacher-menu-view-quiz');
  const teacherMenuStudentScores = document.getElementById('teacher-menu-student-scores');
  const teacherMenuClassPerformance = document.getElementById('teacher-menu-class-performance');
  const teacherMenuViewStudents = document.getElementById('teacher-menu-view-students');

  const addLessonsSection = document.getElementById('add-lessons-section');
  const viewLessonsSection = document.getElementById('view-lessons-section');
  const createQuizSection = document.getElementById('create-quiz-section');
  const viewQuizSection = document.getElementById('view-quiz-section');
  const studentScoresSection = document.getElementById('student-scores-section');
  const classPerformanceSection = document.getElementById('class-performance-section');
  const viewStudentsSection = document.getElementById('view-students-section');

  const teacherDashboardBody = teacherDashboard ? teacherDashboard.querySelector('.dashboard-body') : null;
  const lessonsList = document.getElementById('lessons-list');
  const quizForm = document.getElementById('quiz-form');
  const quizList = document.getElementById('quiz-list');
  const scoresTbody = document.getElementById('scores-tbody');
  const performanceList = document.getElementById('performance-list');
  const studentsList = document.getElementById('students-list');

  // Show Add Lessons section
  if (teacherMenuAddLessons && addLessonsSection && teacherDashboardBody) {
    teacherMenuAddLessons.addEventListener('click', function() {
      teacherDashboardBody.style.display = 'none';
      addLessonsSection.style.display = 'flex';
      viewLessonsSection.style.display = 'none';
      createQuizSection.style.display = 'none';
      viewQuizSection.style.display = 'none';
      studentScoresSection.style.display = 'none';
      classPerformanceSection.style.display = 'none';
      viewStudentsSection.style.display = 'none';
    });
  }

  // Show View Lessons section
  if (teacherMenuViewLessons && viewLessonsSection && teacherDashboardBody) {
    teacherMenuViewLessons.addEventListener('click', function() {
      teacherDashboardBody.style.display = 'none';
      addLessonsSection.style.display = 'none';
      viewLessonsSection.style.display = 'flex';
      createQuizSection.style.display = 'none';
      viewQuizSection.style.display = 'none';
      studentScoresSection.style.display = 'none';
      classPerformanceSection.style.display = 'none';
      viewStudentsSection.style.display = 'none';
      displayLessons();
    });
  }

  // Store and display lessons using localStorage
  function displayLessons() {
    if (!lessonsList) return;
    lessonsList.innerHTML = '';
    const lessons = JSON.parse(localStorage.getItem('teacherLessons') || '[]');
    if (lessons.length === 0) {
      lessonsList.innerHTML = '<div>No lessons added yet.</div>';
      return;
    }
    lessons.forEach(lesson => {
      const div = document.createElement('div');
      div.className = 'lesson-item';
      div.textContent = `${lesson.subject}: ${lesson.title}`;
      lessonsList.appendChild(div);
    });
  }

  // Add lesson buttons logic
  document.querySelectorAll('.add-lesson-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const subject = this.parentElement.querySelector('span').textContent;
      const title = prompt(`Enter lesson title for ${subject}:`);
      if (title) {
        let lessons = JSON.parse(localStorage.getItem('teacherLessons') || '[]');
        lessons.push({ subject, title });
        localStorage.setItem('teacherLessons', JSON.stringify(lessons));
        alert('Lesson added!');
      }
    });
  });

  // --- Teacher Quiz/Exam Logic START ---
  // Show Create Quiz section
  if (teacherMenuCreateQuiz && createQuizSection && teacherDashboardBody) {
    teacherMenuCreateQuiz.addEventListener('click', function() {
      teacherDashboardBody.style.display = 'none';
      addLessonsSection.style.display = 'none';
      viewLessonsSection.style.display = 'none';
      createQuizSection.style.display = 'flex';
      viewQuizSection.style.display = 'none';
      studentScoresSection.style.display = 'none';
      classPerformanceSection.style.display = 'none';
      viewStudentsSection.style.display = 'none';
    });
  }

  // Show View Quiz section
  if (teacherMenuViewQuiz && viewQuizSection && teacherDashboardBody) {
    teacherMenuViewQuiz.addEventListener('click', function() {
      teacherDashboardBody.style.display = 'none';
      addLessonsSection.style.display = 'none';
      viewLessonsSection.style.display = 'none';
      createQuizSection.style.display = 'none';
      viewQuizSection.style.display = 'flex';
      studentScoresSection.style.display = 'none';
      classPerformanceSection.style.display = 'none';
      viewStudentsSection.style.display = 'none';
      displayQuizzes();
    });
  }

  // Handle quiz creation
  if (quizForm) {
    quizForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const subject = document.getElementById('quiz-subject').value;
      const title = document.getElementById('quiz-title').value;
      const type = document.getElementById('quiz-type').value;
      let quizzes = JSON.parse(localStorage.getItem('teacherQuizzes') || '[]');
      quizzes.push({ subject, title, type });
      localStorage.setItem('teacherQuizzes', JSON.stringify(quizzes));
      alert('Quiz/Exam created!');
      quizForm.reset();
    });
  }

  // Display quizzes/exams
  function displayQuizzes() {
    if (!quizList) return;
    quizList.innerHTML = '';
    const quizzes = JSON.parse(localStorage.getItem('teacherQuizzes') || '[]');
    if (quizzes.length === 0) {
      quizList.innerHTML = '<div>No quizzes or exams created yet.</div>';
      return;
    }
    quizzes.forEach(q => {
      const div = document.createElement('div');
      div.className = 'quiz-item';
      div.textContent = `${q.subject} - ${q.type}: ${q.title}`;
      quizList.appendChild(div);
    });
  }

  // --- Student Scores & Performance ---
  // Dummy data for demonstration
  const students = [
    { name: "Trisha Marie Oronan", type: "Quiz", no: 1, score: "15/15", performance: 90, label: "VERY GOOD" },
    { name: "Angelyn Panesa", type: "Quiz", no: 1, score: "15/15", performance: 90, label: "VERY GOOD" },
    { name: "Shaznay Eubra", type: "Quiz", no: 1, score: "15/15", performance: 90, label: "VERY GOOD" }
  ];

  // Show Student Scores section
  if (teacherMenuStudentScores && studentScoresSection && teacherDashboardBody) {
    teacherMenuStudentScores.addEventListener('click', function() {
      teacherDashboardBody.style.display = 'none';
      addLessonsSection.style.display = 'none';
      viewLessonsSection.style.display = 'none';
      createQuizSection.style.display = 'none';
      viewQuizSection.style.display = 'none';
      classPerformanceSection.style.display = 'none';
      viewStudentsSection.style.display = 'none';
      studentScoresSection.style.display = 'flex';
      displayScores();
    });
  }

  // Show Class Performance section
  if (teacherMenuClassPerformance && classPerformanceSection && teacherDashboardBody) {
    teacherMenuClassPerformance.addEventListener('click', function() {
      teacherDashboardBody.style.display = 'none';
      addLessonsSection.style.display = 'none';
      viewLessonsSection.style.display = 'none';
      createQuizSection.style.display = 'none';
      viewQuizSection.style.display = 'none';
      studentScoresSection.style.display = 'none';
      viewStudentsSection.style.display = 'none';
      classPerformanceSection.style.display = 'flex';
      displayPerformance();
    });
  }

  // Populate scores table
  function displayScores() {
    if (!scoresTbody) return;
    scoresTbody.innerHTML = '';
    students.forEach(stu => {
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${stu.name}</td><td>${stu.type}</td><td>${stu.no}</td><td>${stu.score}</td>`;
      scoresTbody.appendChild(tr);
    });
  }

  // Populate performance rings
  function displayPerformance() {
    if (!performanceList) return;
    performanceList.innerHTML = '';
    students.forEach(stu => {
      const div = document.createElement('div');
      div.className = 'performance-item';
      div.innerHTML = `
        <span>${stu.name}</span>
        <div class="ring">
          <svg width="50" height="50">
            <circle class="bg" cx="25" cy="25" r="20"></circle>
            <circle class="progress" cx="25" cy="25" r="20"
              stroke-dasharray="126"
              stroke-dashoffset="${126 - (stu.performance / 100) * 126}">
            </circle>
          </svg>
        </div>
        <span class="performance-label">${stu.label}</span>
      `;
      performanceList.appendChild(div);
    });
  }

  // --- View Students ---
  // Dummy data for enrolled students
  const enrolledStudents = [
    "Trisha Marie Oronan",
    "Angelyn Panesa",
    "Shaznay Eubra"
  ];

  // Show View Students section
  if (teacherMenuViewStudents && viewStudentsSection && teacherDashboardBody) {
    teacherMenuViewStudents.addEventListener('click', function() {
      teacherDashboardBody.style.display = 'none';
      addLessonsSection.style.display = 'none';
      viewLessonsSection.style.display = 'none';
      createQuizSection.style.display = 'none';
      viewQuizSection.style.display = 'none';
      studentScoresSection.style.display = 'none';
      classPerformanceSection.style.display = 'none';
      viewStudentsSection.style.display = 'flex';
      displayStudents();
    });
  }

  // Populate students list
  function displayStudents() {
    if (!studentsList) return;
    studentsList.innerHTML = '';
    enrolledStudents.forEach(name => {
      const li = document.createElement('li');
      li.textContent = name;
      studentsList.appendChild(li);
    });
  }

});

