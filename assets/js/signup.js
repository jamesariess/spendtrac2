// Signup Form Functionality with Security Features
const signupForm = document.getElementById('signupForm');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const confirmPasswordInput = document.getElementById('confirmPassword');
const captchaAnswerInput = document.getElementById('captchaAnswer');
const signupBtn = document.getElementById('signupBtn');
const termsCheckbox = document.getElementById('termsCheckbox');

// Error elements
const emailError = document.getElementById('emailError');
const passwordError = document.getElementById('passwordError');
const confirmPasswordError = document.getElementById('confirmPasswordError');
const captchaError = document.getElementById('captchaError');
const termsError = document.getElementById('termsError');

// Password strength elements
const strengthLabel = document.getElementById('strengthLabel');
const strengthBar = document.getElementById('strengthBar');

// Password requirements
const requirements = {
    length: document.getElementById('req-length'),
    upper: document.getElementById('req-upper'),
    lower: document.getElementById('req-lower'),
    number: document.getElementById('req-number'),
    special: document.getElementById('req-special')
};

const matchIndicator = document.getElementById('matchIndicator');
const togglePasswordBtn = document.getElementById('togglePassword');

// Validation helpers
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function validatePassword(password) {
    return {
        length: password.length >= 8,
        upper: /[A-Z]/.test(password),
        lower: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
    };
}

function getPasswordStrength(password) {
    const validation = validatePassword(password);
    const passedChecks = Object.values(validation).filter(Boolean).length;

    if (passedChecks <= 2) return { strength: 'weak', color: '#ef4444', width: '33%' };
    if (passedChecks <= 3) return { strength: 'fair', color: '#f59e0b', width: '66%' };
    return { strength: 'strong', color: '#10b981', width: '100%' };
}

function updatePasswordStrength(password) {
    const validation = validatePassword(password);
    const strength = getPasswordStrength(password);

    // Update requirements display
    Object.keys(validation).forEach(key => {
        const element = requirements[key];
        if (element) {
            if (validation[key]) {
                element.classList.add('met');
            } else {
                element.classList.remove('met');
            }
        }
    });

    // Update strength bar
    strengthBar.style.width = strength.width;
    strengthBar.style.backgroundColor = strength.color;
    strengthLabel.textContent = ` (${strength.strength})`;
    strengthLabel.style.color = strength.color;
}

function showError(input, errorElement, message = '') {
    input.classList.add('error');
    errorElement.classList.add('show');
    if (message) errorElement.textContent = message;
}

function clearError(input, errorElement) {
    input.classList.remove('error');
    errorElement.classList.remove('show');
}

// Load CAPTCHA on page load
async function loadCaptcha() {
    try {
        const res = await fetch('../frameworks/captcha.php');
        const data = await res.json();

        if (data.success) {
            document.getElementById('captchaProblem').textContent = data.question;
            captchaAnswerInput.value = '';
            clearError(captchaAnswerInput, captchaError);
        }
    } catch (error) {
        document.getElementById('captchaProblem').textContent = 'Error loading CAPTCHA. Please refresh.';
    }
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
    const password = passwordInput.value;

    if (password) {
        updatePasswordStrength(password);
        clearError(passwordInput, passwordError);

        // Check match with confirm password
        if (confirmPasswordInput.value) {
            updatePasswordMatch();
        }
    } else {
        strengthBar.style.width = '0%';
        strengthLabel.textContent = '';
    }
});

confirmPasswordInput.addEventListener('input', updatePasswordMatch);

function updatePasswordMatch() {
    if (passwordInput.value && confirmPasswordInput.value) {
        if (passwordInput.value === confirmPasswordInput.value) {
            matchIndicator.textContent = '✓ Passwords match';
            matchIndicator.style.color = '#10b981';
            clearError(confirmPasswordInput, confirmPasswordError);
        } else {
            matchIndicator.textContent = '✗ Passwords do not match';
            matchIndicator.style.color = '#ef4444';
        }
    } else {
        matchIndicator.textContent = '';
    }
}

// Toggle password visibility
togglePasswordBtn.addEventListener('click', (e) => {
    e.preventDefault();
    const type = passwordInput.type === 'password' ? 'text' : 'password';
    passwordInput.type = type;
    togglePasswordBtn.classList.toggle('active');
});

// Form submission
signupForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const email = emailInput.value.trim();
    const password = passwordInput.value;
    const confirmPassword = confirmPasswordInput.value;
    const captchaAnswer = captchaAnswerInput.value.trim();

    let hasError = false;

    // Validate email
    if (!email) {
        showError(emailInput, emailError, 'Email is required');
        hasError = true;
    } else if (!isValidEmail(email)) {
        showError(emailInput, emailError, 'Please enter a valid email address');
        hasError = true;
    } else {
        clearError(emailInput, emailError);
    }

    // Validate password
    const passwordValidation = validatePassword(password);
    if (!password) {
        showError(passwordInput, passwordError, 'Password is required');
        hasError = true;
    } else if (!passwordValidation.length) {
        showError(passwordInput, passwordError, 'Password must be at least 8 characters');
        hasError = true;
    } else if (!passwordValidation.upper) {
        showError(passwordInput, passwordError, 'Password must contain an uppercase letter');
        hasError = true;
    } else if (!passwordValidation.lower) {
        showError(passwordInput, passwordError, 'Password must contain a lowercase letter');
        hasError = true;
    } else if (!passwordValidation.number) {
        showError(passwordInput, passwordError, 'Password must contain a number');
        hasError = true;
    } else if (!passwordValidation.special) {
        showError(passwordInput, passwordError, 'Password must contain a special character');
        hasError = true;
    } else {
        clearError(passwordInput, passwordError);
    }

    // Validate confirm password
    if (!confirmPassword) {
        showError(confirmPasswordInput, confirmPasswordError, 'Please confirm your password');
        hasError = true;
    } else if (password !== confirmPassword) {
        showError(confirmPasswordInput, confirmPasswordError, 'Passwords do not match');
        hasError = true;
    } else {
        clearError(confirmPasswordInput, confirmPasswordError);
    }

    // Validate CAPTCHA
    if (!captchaAnswer) {
        showError(captchaAnswerInput, captchaError, 'Please answer the CAPTCHA');
        hasError = true;
    } else {
        clearError(captchaAnswerInput, captchaError);
    }

    // Validate terms
    if (!termsCheckbox.checked) {
        termsError.classList.add('show');
        termsError.textContent = 'You must accept the Terms of Service';
        hasError = true;
    } else {
        termsError.classList.remove('show');
    }

    if (hasError) return;

    // Verify CAPTCHA first
    signupBtn.classList.add('btn-loading');
    signupBtn.disabled = true;
    signupBtn.textContent = 'Verifying...';

    try {
        const captchaRes = await fetch('../frameworks/verify_captcha.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ answer: captchaAnswer })
        });

        const captchaData = await captchaRes.json();

        if (!captchaData.success) {
            showError(captchaAnswerInput, captchaError, captchaData.message || 'CAPTCHA verification failed');
            signupBtn.classList.remove('btn-loading');
            signupBtn.disabled = false;
            signupBtn.textContent = 'Create Account';
            return;
        }

        // CAPTCHA verified, proceed with signup
        signupBtn.textContent = 'Creating Account...';

        const response = await fetch('../frameworks/signup.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password, confirmPassword })
});

const text = await response.text();
let result;
try {
    result = JSON.parse(text);
} catch (e) {
    console.error("Invalid JSON response:", text);
    throw new Error("Server returned invalid response");
}

        if (result.success) {
            // Success - redirect to OTP page
            signupBtn.classList.remove('btn-loading');
            signupBtn.textContent = 'Redirecting...';

            // Store email in localStorage for OTP page
            localStorage.setItem('signupEmail', email);

            // Inside if (result.success)
showSuccessMessage("Account created successfully! Redirecting to login...");

function showSuccessMessage(msg) {
    const successDiv = document.createElement('div');
    successDiv.className = 'form-success';
    successDiv.textContent = msg;
    signupForm.appendChild(successDiv);
}

            setTimeout(() => {
        window.location.href = '../auth/login.html';
            }, 1500);
        } else {
            // Signup failed
            signupBtn.classList.remove('btn-loading');
            signupBtn.disabled = false;
            signupBtn.textContent = 'Create Account';

            // Show error on the most relevant field
            if (result.message.includes('email')) {
                showError(emailInput, emailError, result.message);
            } else if (result.message.includes('password')) {
                showError(passwordInput, passwordError, result.message);
            } else {
                showError(emailInput, emailError, result.message);
            }

            // Reload CAPTCHA
            loadCaptcha();
        }
    } catch (error) {
        signupBtn.classList.remove('btn-loading');
        signupBtn.disabled = false;
        signupBtn.textContent = 'Create Account';

        showError(emailInput, emailError, 'Network error. Please try again.');
        console.error('Signup error:', error);
    }
});

// Load CAPTCHA when page loads
window.addEventListener('load', loadCaptcha);
