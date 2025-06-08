<?php
require_once 'db_connect.php';

// Handle Teacher Sign Up
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['email'], $_POST['subject'], $_POST['password'], $_POST['confirm-password'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];
    if ($password === $confirm_password) {
        // Check if email already exists for teachers
        $check_stmt = $conn->prepare("CALL CheckEmailExists(?, 'teacher')");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['exists_count'] > 0) {
            $signup_error = "Email is already registered as a teacher.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Use RegisterTeacher stored procedure
            $stmt = $conn->prepare("CALL RegisterTeacher(?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $teacher_id = $row['teacher_id'];
            
            // Replace inline SQL with UpdateTeacherSubject stored procedure
            $update_stmt = $conn->prepare("CALL UpdateTeacherSubject(?, ?)");
            $update_stmt->bind_param("is", $teacher_id, $subject);
            $update_stmt->execute();
            $update_stmt->close();
            
            $stmt->close();
            $signup_success = true;
        }
        $check_stmt->close();
    } else {
        $signup_error = "Passwords do not match.";
    }
}

// Handle Student Sign Up
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['email'], $_POST['grade'], $_POST['password'], $_POST['confirm-password']) && !isset($_POST['subject'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $grade = trim($_POST['grade']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];
    if ($password === $confirm_password) {
        // Check if email already exists for students
        $check_stmt = $conn->prepare("CALL CheckEmailExists(?, 'student')");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['exists_count'] > 0) {
            $signup_error = "Email is already registered as a student.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Use RegisterStudent stored procedure
            $stmt = $conn->prepare("CALL RegisterStudent(?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $student_id = $row['student_id'];
            
            // Replace inline SQL with UpdateStudentGrade stored procedure
            $update_stmt = $conn->prepare("CALL UpdateStudentGrade(?, ?)");
            $update_stmt->bind_param("is", $student_id, $grade);
            $update_stmt->execute();
            $update_stmt->close();
            
            $stmt->close();
            $signup_success = true;
        }
        $check_stmt->close();
    } else {
        $signup_error = "Passwords do not match.";
    }
}

// Handle Login (Teacher or Student)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['password']) && isset($_POST['login-type'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $type = $_POST['login-type'];
    
    if ($type === 'teacher') {
        // Use AuthenticateTeacher stored procedure
        $stmt = $conn->prepare("CALL AuthenticateTeacher(?)");
        $stmt->bind_param("s", $email);
    } else {
        // Use AuthenticateStudent stored procedure
        $stmt = $conn->prepare("CALL AuthenticateStudent(?)");
        $stmt->bind_param("s", $email);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id = $row['id'];
        $hashed_password = $row['password'];
        
        if (password_verify($password, $hashed_password)) {
            // Login success: set session, redirect, etc.
            session_start();
            $_SESSION['user_id'] = $id;
            $_SESSION['user_type'] = $type;
            
            if ($type === 'teacher') {
                $_SESSION['teacher_id'] = $id;
                // Store teacher name in session
                $_SESSION['teacher_name'] = $row['name'];
            } else {
                $_SESSION['student_id'] = $id;
                // Store student name in session
                $_SESSION['student_name'] = $row['name'];
            }
            
            // Show success modal instead of redirecting immediately
            $login_success = true;
        } else {
            $login_error = "Invalid password.";
        }
    } else {
        $login_error = "No account found with that email.";
    }
    
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SILID CHECK</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Cal+Sans&display=swap');
    </style>
    <header>
         <div class="logo">
        <img src="assets/pics/sc_logo.png" class="logo-img-navbar">
        <h1 style="margin: 0 0 0 10px; font-size: 2rem;">SILID CHECK</h1>
    </div>
        <nav>
            <ul>
                <li><a href="#about">About</a></li>
                <li><a href="#contact">Contact Us</a></li>
                <li><a href="#support">Support</a></li>
            </ul>
        </nav>
        <div class="header-buttons">
            <button class="login-btn" onclick="openLoginModal()">Login</button>
            <button class="sign-up-btn" onclick="scrollToSignUp()">Sign Up</button>
        </div>
    </header>
    
    <main>
        <section class="hero">
            <div class="hero-content">
                <h2>Every check is an <span>opportunity</span> to level up</h2>
                <p>Each challenge and assessment presents an opportunity for growth, improvement, and advancement. View each checkpoint not as an obstacle, but as a stepping stone toward your next breakthrough.</p>
                <button class="sign-up-btn" onclick="scrollToSignUp()">Get Started</button>
            </div>
            <div class="hero-image">
                <!-- Show teacher and student images side by side with spacing -->
                <div class="hero-images-row">
                    <img src="assets/pics/StudentsPic1 (2).png" alt="students" class="hero-img">
                </div>
            </div>
        </section>
        
      <section id="signup" class="sign-up-section">
    <h2>Sign Up As</h2>
    <div class="options">
        <div class="option-card">
    <div class="option-logo" onclick="openSignupModal('teacher')">
        <img src="assets/pics/TeacherCard 2.png" alt="Teacher" class="option-img">
        <div class="option-info">
            <h3>Teacher</h3>
            <p>A mentor who guides, educates, and inspires growth.</p>
        </div>
    </div>
</div>
<div class="option-card">
    <div class="option-logo" onclick="openSignupModal('student')">
        <img src="assets/pics/StudentCard 2.png" alt="Student" class="option-img">
        <div class="option-info">
            <h3>Student</h3>
            <p>A learner exploring knowledge and embracing challenges.</p>
        </div>
    </div>
</div>
</section>
        
        <section id="about" class="about-section">
            <h2>About SILID CHECK</h2>
            <p>SILID CHECK is an innovative educational platform designed to streamline the assessment process for both teachers and students. Our mission is to provide a user-friendly interface that simplifies exam creation, grading, and feedback, enabling educators to focus on what truly matters: teaching and learning.</p>
            <p>With SILID CHECK, teachers can easily create customized exams, automate grading, and track student performance through an intuitive analytics dashboard. Students benefit from interactive quizzes that adapt to their learning pace, ensuring a personalized educational experience.</p>
            <p>The platform supports interactive learning with 
                quizzes, timed tests, and adaptive assessments that 
                adjust based on proficiency levels. By bridging traditional 
                and digital education, Silid Check ensures accessible, 
                efficient evaluations while fostering continuous learning and 
                academic success.</p>
            <div class="mission-vision">
                <div class="mission" style="width:48%;display:inline-block;vertical-align:top;box-sizing:border-box;padding-right:2%;border-right:2px solid #e0e0e0;">
                    <h3>Our Mission</h3>
                    <p>To transform educational assessment by providing intuitive, accessible tools that empower educators and inspire students to achieve their full potential through meaningful feedback and personalized learning pathways.</p>
                </div>
                <div class="vision" style="width:48%;display:inline-block;vertical-align:top;box-sizing:border-box;padding-left:2%;">
                    <h3>Our Vision</h3>
                    <p>A world where every educational checkpoint becomes an opportunity for growth, where assessment is not merely evaluation but a stepping stone toward mastery, and where technology seamlessly enhances the learning journey for all.</p>
                </div>
            </div>
        </section>
        
        <section id="features" class="features-section">
    <h2>Features</h2>
    <div class="features-list">
        <div class="feature-item">
            <img src="assets/pics/Adaptive Learning 2.png" alt="Adaptive Learning" class="feature-icon">
            <h3>Adaptive Learning</h3>
            <p>Personalized learning paths based on student proficiency levels.</p>
        </div>
        <div class="feature-item">
            <img src="assets/pics/Automated Assessments 2.png" alt="Automated Assessments" class="feature-icon">
            <h3>Automated Assessments</h3>
            <p>Online quizzes, exams, and instant grading for efficient evaluation.</p>
        </div>
        <div class="feature-item">
            <img src="assets/pics/Digital Course Materials 2.png" alt="Digital Course Materials" class="feature-icon">
            <h3>Digital Course Materials</h3>
            <p>Digitalized access to quizzes, exams, and other resources.</p>
        </div>
        <div class="feature-item">
            <img src="assets/pics/Progress Analytics 2.png" alt="Progress Analytics" class="feature-icon">
            <h3>Progress Analytics</h3>
            <p>Detailed reports for students and teachers to assess learning trends.</p>
        </div>
        <div class="feature-item">
            <img src="assets/pics/Real-Time Feedback 2.png" alt="Real-Time Feedback" class="feature-icon">
            <h3>Real-Time Feedback</h3>
            <p>Immediate insights on performance to help students track progress.</p>
        </div>
        <div class="feature-item">
            <img src="assets/pics/Mobile Accessibility 2.png" alt="Mobile Accessibility" class="feature-icon">
            <h3>Accessibility</h3>
            <p>Seamless learning experience across websites.</p>
        </div>
    </div>
</section>

    
    <!-- Teacher Signup Modal -->
    <div id="teacherModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('teacherModal')">&times;</span>
            <h2>Sign Up as Teacher</h2>
            <form class="signup-form" method="POST" action="index.php">
                <div class="form-group">
                    <label for="teacher-name">Full Name</label>
                    <input type="text" id="teacher-name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="teacher-email">Email</label>
                    <input type="email" id="teacher-email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="teacher-subject">Subject</label>
                    <input type="text" id="teacher-subject" name="subject" required>
                </div>
                <div class="form-group">
                    <label for="teacher-password">Password</label>
                    <input type="password" id="teacher-password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="teacher-confirm-password">Confirm Password</label>
                    <input type="password" id="teacher-confirm-password" name="confirm-password" required>
                </div>
                <button type="submit" class="signup-submit-btn">Create Teacher Account</button>
            </form>
        </div>
    </div>
    
    <!-- Student Signup Modal -->
    <div id="studentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('studentModal')">&times;</span>
            <h2>Sign Up as Student</h2>
            <form class="signup-form" method="POST" action="index.php">
                <div class="form-group">
                    <label for="student-name">Full Name</label>
                    <input type="text" id="student-name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="student-email">Email</label>
                    <input type="email" id="student-email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="student-grade">Grade Level</label>
                    <select id="student-grade" name="grade" required>
                        <option value="">Select Grade</option>
                        <option value="7">Grade 7</option>
                        <option value="8">Grade 8</option>
                        <option value="9">Grade 9</option>
                        <option value="10">Grade 10</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="student-password">Password</label>
                    <input type="password" id="student-password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="student-confirm-password">Confirm Password</label>
                    <input type="password" id="student-confirm-password" name="confirm-password" required>
                </div>
                <button type="submit" class="signup-submit-btn">Create Student Account</button>
            </form>
        </div>
    </div>
    
    <!-- Login Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('loginModal')">&times;</span>
            <h2>Login to SILID CHECK</h2>
            
            <div class="login-tabs">
                <button class="login-tab-btn active" onclick="switchLoginTab('teacher')">Teacher</button>
                <button class="login-tab-btn" onclick="switchLoginTab('student')">Student</button>
            </div>
            
            <div id="teacherLoginForm" class="login-form-container active">
                <form class="login-form" method="POST" action="index.php">
                    <div class="form-group">
                        <label for="teacher-login-email">Email</label>
                        <input type="email" id="teacher-login-email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="teacher-login-password">Password</label>
                        <input type="password" id="teacher-login-password" name="password" required>
                    </div>
                    <div class="form-options">
                        <div class="remember-me">
                            <input type="checkbox" id="teacher-remember" name="remember">
                            <label for="teacher-remember">Remember me</label>
                        </div>
                        <a href="#" class="forgot-password">Forgot Password?</a>
                    </div>
                    <input type="hidden" name="login-type" value="teacher">
                    <button type="submit" class="login-submit-btn">Login as Teacher</button>
                </form>
            </div>
            
            <div id="studentLoginForm" class="login-form-container">
                <form class="login-form" method="POST" action="index.php">
                    <div class="form-group">
                        <label for="student-login-email">Email</label>
                        <input type="email" id="student-login-email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="student-login-password">Password</label>
                        <input type="password" id="student-login-password" name="password" required>
                    </div>
                    <div class="form-options">
                        <div class="remember-me">
                            <input type="checkbox" id="student-remember" name="remember">
                            <label for="student-remember">Remember me</label>
                        </div>
                        <a href="#" class="forgot-password">Forgot Password?</a>
                    </div>
                    <input type="hidden" name="login-type" value="student">
                    <button type="submit" class="login-submit-btn">Login as Student</button>
                </form>
            </div>
            
            <div class="login-footer">
                <p>Don't have an account? <a href="#" onclick="closeModal('loginModal'); scrollToSignUp();">Sign up now</a></p>
            </div>
        </div>
    </div>
    
    <!-- Message Modal for Login/Signup -->
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeMessageModal()">&times;</span>
            <h2 id="messageModalTitle" style="margin-bottom: 1rem;"></h2>
            <div class="form-group">
                <p id="messageModalText" style="text-align:center;"></p>
            </div>
            <button class="login-submit-btn" style="width:100%;margin-top:1rem;" onclick="closeMessageModal()">OK</button>
        </div>
    </div>
    
    <footer id="contact">
        <div class="footer-content">
            <div class="footer-logo">
                <h3>SILID CHECK</h3>
                <p>Transforming educational assessment</p>
                <p>Track student performance in quizzes and exams with real-time analytics.</p>
                <p>Empowering teachers and learners through data-driven insights.</p>
            </div>
            
            <div class="footer-contact">
                <h3>Contact Us</h3>
                <ul>
                    <li><strong>Email:</strong> silidcheck@gmail.com</li>
                    <li><strong>Phone:</strong> +63 949 647 6352</li>
                    <li><strong>Address:</strong> Pulong Buhangin</li>
                </ul>
            </div>
            
            <div class="footer-links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#about">About</a></li>
                    <li><a href="#signup">Sign Up</a></li>
                    <li><a href="#support">Support</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2025 SILID CHECK. All rights reserved.</p>
        </div>
    </footer>
<script>
// Pass PHP messages to JS
var loginError = <?php echo isset($login_error) ? json_encode($login_error) : 'null'; ?>;
var signupError = <?php echo isset($signup_error) ? json_encode($signup_error) : 'null'; ?>;
var signupSuccess = <?php echo isset($signup_success) ? 'true' : 'false'; ?>;
var loginSuccess = <?php echo isset($login_success) ? 'true' : 'false'; ?>;
var userType = <?php echo isset($_SESSION['user_type']) ? json_encode($_SESSION['user_type']) : 'null'; ?>;
</script>
<script src="scripts.js"></script>
<script>
window.addEventListener('DOMContentLoaded', function() {
    if (loginError) {
        showMessageModal('Login Error', loginError);
    } else if (signupError) {
        showMessageModal('Sign Up Error', signupError);
    } else if (signupSuccess) {
        showMessageModal('Sign Up Success', 'Account created successfully! You can now log in.');
    } else if (loginSuccess) {
        showMessageModal('Login Success', 'Login successful! Redirecting...');
        setTimeout(function() {
            if (userType === 'teacher') {
                window.location.href = 'Dashboard/teacher_dashboard.php';
            } else {
                window.location.href = 'Dashboard/student_dashboard.php';
            }
        }, 1500);
    }
});
</script>
</body>
</html>
