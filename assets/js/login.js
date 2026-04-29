// Login Form Functionality with Backend AJAX
const loginForm = document.getElementById('loginForm');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const loginBtn = document.getElementById('loginBtn');
const emailError = document.getElementById('emailError');
const passwordError = document.getElementById('passwordError');
const successMessage = document.getElementById('successMessage');

// Validate email format
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Show error
function showError(input, errorElement, message = '') {
    input.classList.add('error');
    errorElement.classList.add('show');
    if (message) errorElement.textContent = message;
}

// Clear error
function clearError(input, errorElement) {
    input.classList.remove('error');
    errorElement.classList.remove('show');
}

// Real-time validation
emailInput.addEventListener('input', () => {
    if (emailInput.value && !isValidEmail(emailInput.value)) {
        showError(emailInput, emailError, 'Please enter a valid email address');
    } else {
        clearError(emailInput, emailError);
    }
});

passwordInput.addEventListener('input', () => {
    if (passwordInput.value && passwordInput.value.length < 6) {
        showError(passwordInput, passwordError, 'Password must be at least 6 characters');
    } else {
        clearError(passwordInput, passwordError);
    }
});

// Form submission - Backend AJAX
loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const email = emailInput.value.trim();
    const password = passwordInput.value;
    let hasError = false;

    // Client-side validation
    if (!email) {
        showError(emailInput, emailError, 'Email is required');
        hasError = true;
    } else if (!isValidEmail(email)) {
        showError(emailInput, emailError, 'Please enter a valid email address');
        hasError = true;
    } else {
        clearError(emailInput, emailError);
    }

    if (!password) {
        showError(passwordInput, passwordError, 'Password is required');
        hasError = true;
    } else if (password.length < 6) {
        showError(passwordInput, passwordError, 'Password must be at least 6 characters');
        hasError = true;
    } else {
        clearError(passwordInput, passwordError);
    }

    if (hasError) return;

    // Show loading
    loginBtn.classList.add('btn-loading');
    loginBtn.disabled = true;
    loginBtn.textContent = 'Signing in...';

    try {
        // Real AJAX to backend
        const response = await fetch('../frameworks/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email, password })
        });

        const result = await response.json();

        if (result.success) {
            // Success
            successMessage.classList.add('show');
            loginBtn.classList.remove('btn-loading');
            
            // Optional localStorage (for frontend state)
            localStorage.setItem('isLoggedIn', 'true');
            localStorage.setItem('userEmail', email);

            // Redirect to OTP page
            setTimeout(() => {
                window.location.href = '../auth/otp.html';
            }, 1500);
        } else {
            // Backend error
            loginBtn.classList.remove('btn-loading');
            loginBtn.disabled = false;
            loginBtn.textContent = 'Sign in';
            
            showError(passwordInput, passwordError, result.message || 'Login failed');
        }
    } catch (error) {
        // Network/Server error
        loginBtn.classList.remove('btn-loading');
        loginBtn.disabled = false;
        loginBtn.textContent = 'Sign in';
        
        showError(passwordInput, passwordError, 'Network error. Please try again.');
        console.error('Login error:', error);
    }
});

// Check session on load (optional, for frontend-only)
if (localStorage.getItem('isLoggedIn') === 'true') {
    window.location.href = '../pages/dashboard.html';
}

// Check if signup was successful
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('signup') === 'success') {
    const signupSuccessMessage = document.getElementById('signupSuccessMessage');
    if (signupSuccessMessage) {
        signupSuccessMessage.classList.add('show');
    }
}


