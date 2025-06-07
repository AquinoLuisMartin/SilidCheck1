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


