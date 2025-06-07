const database = {
    students: [
      { id: 1, name: "Trisha Marie Oronan", subjects: [
        {name: "Mathematics", score: 85, grade: "A" },
        {name: "Science", score: 90, grade: "B" },
        {name: "English", score: 80, grade: "B" }
      ] },
    ],
    schedules: [
      { subject: "Mathematics", time: "8:00 AM", room: "101" },
      { subject: "Science", time: "10:00 AM", room: "102" },
      { subject: "English", time: "1:00 PM", room: "103" }
    ],
    lessons: [
      { subject: "Mathematics", lesson: "Lesson 1 - Functions" },
      { subject: "Araling Panlipunan", lesson: "Lesson 2 - Mga Bayani" }
    ],
    quizzes: [
      { subject: "Mathematics", title: "Exam: Functions", type: "Exam",  questions: [ { question: "1 + 1", answer: "2" }, { question: "2+2", answer: "4" } ] , scores: [ { student: "Trisha Marie Oronan", score: 80 }, { student: "John Mark Doe", score: 70 } ] },
      { subject: "Science", title: "Scientifica", type: "Exam",  questions: [ { question: "Question 1", answer: "Answer A" }, { question: "Question 2", answer: "Answer B" } ], scores: [ { student: "Trisha Marie Oronan", score: 80 }, { student: "John Mark Doe", score: 70 } ] },
    ]
  };
  const currentStudent = "Trisha Marie Oronan";

function renderQuizzes() {
  const section = document.querySelector('#quizzes-section');
  section.innerHTML = '<h2>Available Quizzes</h2>';
  database.quizzes.forEach((quiz, index) => {
    const quizDiv = document.createElement('div');
    quizDiv.innerHTML = `
      <div style="border: 1px solid #aaa; padding: 10px; margin: 10px 0;">
        <strong>${quiz.subject}:</strong> ${quiz.title}
        <button onclick="openQuiz(${index})">Take Quiz</button>
      </div>
    `;
    section.appendChild(quizDiv);
  });
}



function openQuiz(index) {
  const quiz = database.quizzes[index];
  const modal = document.getElementById('quiz-modal');
  const form = document.getElementById('quiz-form');

  modal.style.display = 'block';
  document.getElementById('quiz-title').textContent = quiz.title;

  form.innerHTML = quiz.questions.map((q, i) => `
    <p>${q.question}<br>
    <input type="text" name="q${i}" required></p>
  `).join('');

  document.getElementById('submit-quiz').onclick = function () {
    const formData = new FormData(form);
    let score = 0;
    quiz.questions.forEach((q, i) => {
      const userAnswer = formData.get(`q${i}`).trim().toLowerCase();
      const correct = q.answer.trim().toLowerCase();
      if (userAnswer === correct) score += 1;
    });

    const total = quiz.questions.length;
    const percentScore = Math.round((score / total) * 100);

 
    const existing = quiz.scores.find(s => s.student === currentStudent);
    if (existing) {
      existing.score = percentScore;
    } else {
      quiz.scores.push({ student: currentStudent, score: percentScore });
    }

    alert(`Submitted! You scored ${percentScore}%`);
    modal.style.display = 'none';
    renderScores(); 
  };
}


function renderScores() {
  const table = document.querySelector('#scores-section .scores-table');
  let rows = '';

  database.quizzes.forEach(quiz => {
    const studentScore = quiz.scores.find(s => s.student === currentStudent);
    if (studentScore) {
      rows += `
        <tr>
          <td>${quiz.subject}</td>
          <td>${quiz.title}</td>
          <td>${studentScore.score}</td>
        </tr>
      `;
    }
  });

  table.innerHTML = '<tr><th>Subject</th><th>Quiz</th><th>Score</th></tr>' + rows;
}

  document.addEventListener('DOMContentLoaded', () => {
    renderSchedule();
    renderScores();
    renderQuizzes();

  });
document.querySelectorAll('.sidebar ul li').forEach(item => {
    item.addEventListener('click', () => {
      document.querySelectorAll('.dashboard-body').forEach(section => {
        section.style.display = 'none';
      });
  
   
      document.querySelectorAll('.sidebar ul li').forEach(li => li.classList.remove('active'));
      item.classList.add('active');
  
      const sectionMap = {
        "menu-schedule": "schedule-section",
        "menu-lessons": "lessons-section",
        "menu-exams": "", 
        "menu-performance": "", 
        "menu-scores": "scores-section",
        "teacher-menu-add-lessons": "add-lessons-section",
        "teacher-menu-view-lessons": "view-lessons-section",
        "teacher-menu-create-quiz": "create-quiz-section",
        "teacher-menu-view-quiz": "view-quiz-section",
        "teacher-menu-student-scores": "student-scores-section",
        "teacher-menu-class-performance": "class-performance-section",
        "teacher-menu-view-students": "view-students-section"
      };
  
      const targetId = sectionMap[item.id];
      if (targetId) {
        document.getElementById(targetId).style.display = 'block';
      }
    });
  });

function renderSchedule() {
const table = document.querySelector('.schedule-table');
const rows = database.schedules.map(
    sched => `<tr><td>${sched.time}</td><td>${sched.subject}</td><td>${sched.room}</td></tr>`
).join('');
table.innerHTML = '<tr><th>Time</th><th>Subject</th><th>Room</th></tr>' + rows;
}

document.getElementById('logout-btn').addEventListener('click', () => {
    alert("Logging out...");
  });
  document.getElementById('logout-btn-teacher').addEventListener('click', () => {
    alert("Logging out (Teacher)...");
  });

  function renderLessons() {
  const table = document.querySelector('#view-lessons-section .lessons-table');
  let rows = database.lessons.map(
    l => `<tr><td>${l.subject}</td><td>${l.lesson}</td></tr>`
  ).join('');
  table.innerHTML = '<tr><th>Subject</th><th>Lesson</th></tr>' + rows;
}

function addLesson(subject, lesson) {
  database.lessons.push({ subject, lesson });
  renderLessons();
}

// Example: Attach to a form submission
document.getElementById('add-lesson-form').addEventListener('submit', function(e) {
  e.preventDefault();
  const subject = document.getElementById('lesson-subject').value;
  const lesson = document.getElementById('lesson-name').value;
  addLesson(subject, lesson);
  this.reset();
});

// Call renderLessons on page load
document.addEventListener('DOMContentLoaded', () => {
  renderLessons();
});

document.getElementById('logout-btn').addEventListener('click', () => {
    alert("Logging out...");
  });
  document.getElementById('logout-btn-teacher').addEventListener('click', () => {
    alert("Logging out (Teacher)...");
  });

  // Render quizzes/exams in the view section
function renderQuizList() {
  const quizList = document.getElementById('quiz-list');
  if (!quizList) return;
  quizList.innerHTML = '<p>Loading...</p>';
  const teacher = localStorage.getItem('username') || '';
  fetch(`http://localhost:5000/api/quizzes?teacher=${encodeURIComponent(teacher)}`)
    .then(res => res.json())
    .then(quizzes => {
      if (!quizzes.length) {
        quizList.innerHTML = '<p>No quizzes/exams created yet.</p>';
        return;
      }
      quizList.innerHTML = '';
      quizzes.forEach((quiz, idx) => {
        const div = document.createElement('div');
        div.className = 'quiz-item';
        div.innerHTML = `
          <strong>${quiz.subject}</strong> - ${quiz.title} <span style="color:#888;">(${quiz.type})</span>
        `;
        quizList.appendChild(div);
      });
    })
    .catch(() => {
      quizList.innerHTML = '<p style="color:red">Failed to load quizzes from server.</p>';
    });
}

// On quiz form submit, add to backend and update view
const quizForm = document.getElementById('quiz-form');
if (quizForm) {
  quizForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const subject = document.getElementById('quiz-subject').value;
    const title = document.getElementById('quiz-title').value;
    const type = document.getElementById('quiz-type').value;
    const teacher_username = localStorage.getItem('username') || '';
    fetch('http://localhost:5000/api/quizzes', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ teacher_username, subject, title, type })
    })
      .then(res => {
        if (!res.ok) throw new Error('Failed to create quiz');
        return res.json();
      })
      .then(() => {
        alert('Quiz/Exam created!');
        renderQuizList();
        document.getElementById('create-quiz-section').style.display = 'none';
        document.getElementById('view-quiz-section').style.display = 'block';
        quizForm.reset();
      })
      .catch(() => {
        alert('Failed to create quiz.');
      });
  });
}

// Update sidebar click to re-render quiz list when switching to view-quiz-section
const viewQuizMenu = document.getElementById('teacher-menu-view-quiz');
if (viewQuizMenu) {
  viewQuizMenu.addEventListener('click', function() {
    renderQuizList();
  });
}

const createQuizMenu = document.getElementById('teacher-menu-create-quiz');
if (createQuizMenu) {
  createQuizMenu.addEventListener('click', function() {
    // Show the create quiz section (if not already handled elsewhere)
    const createQuizSection = document.getElementById('create-quiz-section');
    if (createQuizSection) createQuizSection.style.display = 'block';

    // Attach listeners only once
    const addBtn = document.getElementById('add-question-btn');
    const questionsList = document.getElementById('questions-list');
    if (addBtn && questionsList && !addBtn.dataset.listener) {
      addBtn.addEventListener('click', function() {
        const questionItem = document.createElement('div');
        questionItem.className = 'question-item';
        questionItem.innerHTML = '<input type="text" name="question[]" class="question-input" placeholder="Enter question here" required/>' +
          '<input type="text" name="answer[]" class="answer-input" placeholder="Correct answer" required style="margin-left:8px;"/>' +
          '<button type="button" class="delete-question-btn" style="margin-left:8px;">Delete</button>';
        questionsList.appendChild(questionItem);
      });
      questionsList.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-question-btn')) {
          e.target.parentElement.remove();
        }
      });
      addBtn.dataset.listener = "true";
    }
  });
}

