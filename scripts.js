function openSignupModal(type) {
    if (type === 'teacher') {
        document.getElementById('teacherModal').style.display = 'block';
    } else if (type === 'student') {
        document.getElementById('studentModal').style.display = 'block';
    }
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Optional: Close modal when clicking outside of it
window.onclick = function(event) {
    const teacherModal = document.getElementById('teacherModal');
    const studentModal = document.getElementById('studentModal');
    const loginModal = document.getElementById('loginModal');
    const messageModal = document.getElementById('messageModal');
    if (event.target === teacherModal) {
        teacherModal.style.display = 'none';
        document.body.style.overflow = '';
    }
    if (event.target === studentModal) {
        studentModal.style.display = 'none';
        document.body.style.overflow = '';
    }
    if (event.target === loginModal) {
        loginModal.style.display = 'none';
        document.body.style.overflow = '';
    }
    if (event.target === messageModal) {
        messageModal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

function scrollToSignUp() {
    const signupSection = document.getElementById('signup');
    if (signupSection) {
        signupSection.scrollIntoView({ behavior: 'smooth' });
        // Optionally, focus the first option for accessibility
        const firstOption = signupSection.querySelector('.option-card');
        if (firstOption) {
            firstOption.focus();
        }
    }
}

function openLoginModal() {
    document.getElementById('loginModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
    // Optionally, default to teacher tab
    if (typeof switchLoginTab === 'function') {
        switchLoginTab('teacher');
    }
}

function switchLoginTab(type) {
    // Remove 'active' from all tab buttons and form containers
    document.querySelectorAll('.login-tab-btn').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.login-form-container').forEach(form => form.classList.remove('active'));

    // Add 'active' to the selected tab and form
    if (type === 'teacher') {
        document.querySelectorAll('.login-tab-btn')[0].classList.add('active');
        document.getElementById('teacherLoginForm').classList.add('active');
    } else {
        document.querySelectorAll('.login-tab-btn')[1].classList.add('active');
        document.getElementById('studentLoginForm').classList.add('active');
    }
}

// Set localStorage.role on login form submit
window.addEventListener('DOMContentLoaded', function() {
    var teacherLoginForm = document.querySelector('#teacherLoginForm form');
    var studentLoginForm = document.querySelector('#studentLoginForm form');
    if (teacherLoginForm) {
        teacherLoginForm.addEventListener('submit', function() {
            localStorage.setItem('role', 'teacher');
        });
    }
    if (studentLoginForm) {
        studentLoginForm.addEventListener('submit', function() {
            localStorage.setItem('role', 'student');
        });
    }
});

function showMessageModal(title, message) {
    var modal = document.getElementById('messageModal');
    var titleElem = document.getElementById('messageModalTitle');
    var textElem = document.getElementById('messageModalText');
    if (modal && titleElem && textElem) {
        titleElem.textContent = title;
        textElem.textContent = message;
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

function closeMessageModal() {
    var modal = document.getElementById('messageModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}
