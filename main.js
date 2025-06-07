document.addEventListener('DOMContentLoaded', function() {
            // Add smooth scrolling for nav links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Option card hover effects
            const cards = document.querySelectorAll('.option-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
            
            // Handle login form submissions
            document.querySelectorAll('.login-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    alert('Login successful!');
                    closeModal('loginModal');
                    this.reset();
                });
            });
        });

        function scrollToSignUp() {
            document.getElementById('signup').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        function openSignupModal(type) {
            const modalId = type === 'teacher' ? 'teacherModal' : 'studentModal';
            document.getElementById(modalId).style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        
        function openLoginModal() {
            document.getElementById('loginModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
            // Default to teacher login tab
            switchLoginTab('teacher');
        }
        
        function switchLoginTab(type) {
            // Hide all login forms
            document.querySelectorAll('.login-form-container').forEach(container => {
                container.classList.remove('active');
            });
            
            // Deactivate all tab buttons
            document.querySelectorAll('.login-tab-btn').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected form
            document.getElementById(type + 'LoginForm').classList.add('active');
            
            // Activate selected tab button
            document.querySelectorAll('.login-tab-btn').forEach(tab => {
                if (tab.textContent.toLowerCase() === type) {
                    tab.classList.add('active');
                }
            });
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        // Handle signup form submissions
        document.querySelectorAll('.signup-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const password = formData.get('password');
                const confirmPassword = formData.get('confirm-password');
                
                if (password !== confirmPassword) {
                    alert('Passwords do not match!');
                    return;
                }
                
                alert('Account created successfully!');
                // Close the modal
                this.closest('.modal').style.display = 'none';
                document.body.style.overflow = 'auto';
                
                // Reset form
                this.reset();
            });
        });