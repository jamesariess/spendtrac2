 // Login Form Functionality
        const loginForm = document.getElementById('loginForm');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const loginBtn = document.getElementById('loginBtn');
        const emailError = document.getElementById('emailError');
        const passwordError = document.getElementById('passwordError');
        const successMessage = document.getElementById('successMessage');

        // Demo credentials
        const DEMO_EMAIL = 'demo@spendtrack.com';
        const DEMO_PASSWORD = 'demo123';

        // Validate email format
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Show error
        function showError(input, errorElement) {
            input.classList.add('error');
            errorElement.classList.add('show');
        }

        // Clear error
        function clearError(input, errorElement) {
            input.classList.remove('error');
            errorElement.classList.remove('show');
        }

        // Real-time validation
        emailInput.addEventListener('input', () => {
            if (emailInput.value && !isValidEmail(emailInput.value)) {
                showError(emailInput, emailError);
            } else {
                clearError(emailInput, emailError);
            }
        });

        passwordInput.addEventListener('input', () => {
            if (passwordInput.value && passwordInput.value.length < 6) {
                showError(passwordInput, passwordError);
            } else {
                clearError(passwordInput, passwordError);
            }
        });

        // Form submission
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const email = emailInput.value.trim();
            const password = passwordInput.value;
            let hasError = false;

            // Validate email
            if (!email) {
                showError(emailInput, emailError);
                emailError.textContent = 'Email is required';
                hasError = true;
            } else if (!isValidEmail(email)) {
                showError(emailInput, emailError);
                emailError.textContent = 'Please enter a valid email address';
                hasError = true;
            } else {
                clearError(emailInput, emailError);
            }

            // Validate password
            if (!password) {
                showError(passwordInput, passwordError);
                passwordError.textContent = 'Password is required';
                hasError = true;
            } else if (password.length < 6) {
                showError(passwordInput, passwordError);
                passwordError.textContent = 'Password must be at least 6 characters';
                hasError = true;
            } else {
                clearError(passwordInput, passwordError);
            }

            if (hasError) return;

            // Show loading state
            loginBtn.classList.add('btn-loading');
            loginBtn.disabled = true;

            // Simulate API call delay
            await new Promise(resolve => setTimeout(resolve, 1500));

            // Check credentials
            if (email === DEMO_EMAIL && password === DEMO_PASSWORD) {
                // Success
                successMessage.classList.add('show');
                loginBtn.classList.remove('btn-loading');
                loginBtn.textContent = 'Success!';
                
                // Store session
                localStorage.setItem('isLoggedIn', 'true');
                localStorage.setItem('userEmail', email);

                // Redirect to dashboard after short delay
                setTimeout(() => {
                    window.location.href = 'dashboard.html';
                }, 1500);
            } else {
                // Error - reset button
                loginBtn.classList.remove('btn-loading');
                loginBtn.disabled = false;
                
                // Show error message
                showError(passwordInput, passwordError);
                passwordError.textContent = 'Invalid email or password. Try demo@spendtrack.com / demo123';
            }
        });

        // Check if already logged in
        if (localStorage.getItem('isLoggedIn') === 'true') {
            window.location.href = 'dashboard.html';
        }