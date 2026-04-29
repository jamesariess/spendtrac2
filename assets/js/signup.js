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

// New OTP elements
const sendCodeBtn = document.getElementById('sendCodeBtn');
const otpSection = document.getElementById('otpSection');
const verifyOtpBtn = document.getElementById('verifyOtpBtn');
const otpCodeInput = document.getElementById('otpCode');
const otpError = document.getElementById('otpError');
const statusMessage = document.getElementById('statusMessage');
const resendLink = document.getElementById('resendLink');
const timerEl = document.getElementById('timer');
let timeRemaining = 120;
let resendTimer;
let currentEmail = '';

// OTP utilities
function showStatus(message, isError = false) {
    statusMessage.textContent = message;
    statusMessage.style.backgroundColor = isError ? '#fee2e2' : '#dcfce7';
    statusMessage.style.color = isError ? '#dc2626' : '#166534';
    statusMessage.style.display = 'block';
    statusMessage.scrollIntoView({ behavior: 'smooth' });
}

function hideStatus() {
    statusMessage.style.display = 'none';
}

function startResendTimer() {
    timeRemaining = 120;
    resendLink.style.pointerEvents = 'none';
    resendLink.style.opacity = '0.5';
    
    resendTimer = setInterval(() => {
        timeRemaining--;
        const mins = Math.floor(timeRemaining / 60);
        const secs = timeRemaining % 60;
        timerEl.textContent = `${mins}:${secs.toString().padStart(2, '0')}`;
        
        if (timeRemaining <= 0) {
            clearInterval(resendTimer);
            resendLink.style.pointerEvents = 'auto';
            resendLink.style.opacity = '1';
            timerEl.textContent = 'Resend';
        }
    }, 1000);
}

// Send code handler - only email + captcha
sendCodeBtn.addEventListener('click', async () => {
    const email = emailInput.value.trim();
    const captchaAnswer = captchaAnswerInput.value.trim();
    let hasError = false;

    // Validate email
    if (!email) {
        showError(emailInput, emailError, 'Email is required');
        hasError = true;
    } else if (!isValidEmail(email)) {
        showError(emailInput, emailError, 'Please enter a valid email address');
        hasError = true;
    }

    // Validate CAPTCHA
    if (!captchaAnswer) {
        showError(captchaAnswerInput, captchaError, 'Please answer the CAPTCHA');
        hasError = true;
    }

    if (hasError) return;

    sendCodeBtn.disabled = true;
    sendCodeBtn.textContent = 'Sending...';

    try {
        // Verify CAPTCHA
        const captchaRes = await fetch('../frameworks/verify_captcha.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ answer: captchaAnswer })
        });

        const captchaData = await captchaRes.json();

        if (!captchaData.success) {
            showError(captchaAnswerInput, captchaError, captchaData.message || 'CAPTCHA failed');
            return;
        }

        // Send OTP (modified signup.php call - only email needed for init)
        const response = await fetch('../frameworks/signup.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                email: email,
                password: '', // Backend ignores if email new
                confirmPassword: ''
            })
        });

        const result = await response.json();

        if (result.success) {
            currentEmail = email;
            // Hide password fields, terms, show OTP section
            document.querySelectorAll('.form-group:not(:first-child):not(.form-group:has(#sendCodeBtn))').forEach(el => {
                el.style.display = 'none';
            });
            signupBtn.style.display = 'none';
            otpSection.style.display = 'block';
            showStatus('OTP sent to ' + email + '! Check your inbox.');
            startResendTimer();
            document.getElementById('code').style.display = 'none';
            sendCodeBtn.style.display = 'none';
        } else {
            showError(emailInput, emailError, result.message);
        }
    } catch (error) {
        showStatus('Network error. Please try again.', true);
    } finally {
        sendCodeBtn.disabled = false;
        sendCodeBtn.textContent = 'Send Code';
    }
});

// Verify OTP handler
verifyOtpBtn.addEventListener('click', async () => {
    const otp = otpCodeInput.value.trim().replace(/[^0-9]/g, '');

    if (otp.length !== 6) {
        showError(otpCodeInput, otpError, 'Enter 6-digit code');
        return;
    }

    verifyOtpBtn.disabled = true;
    verifyOtpBtn.textContent = 'Verifying...';

    try {
        // Verify OTP
        const verifyRes = await fetch('../frameworks/verify_otp.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ otp })
        });

        const verifyData = await verifyRes.json();

        if (verifyData.success) {
            // Complete signup
            const completeRes = await fetch('../frameworks/complete_signup.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });

            const completeData = await completeRes.json();

            if (completeData.success) {
                showStatus('Account created successfully! Redirecting to login...');
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 1500);
            } else {
                showStatus(completeData.message || 'Failed to complete signup', true);
            }
        } else {
            showError(otpCodeInput, otpError, verifyData.message || 'Invalid OTP');
            otpCodeInput.value = '';
        }
    } catch (error) {
        showStatus('Network error. Please try again.', true);
    } finally {
        verifyOtpBtn.disabled = false;
        verifyOtpBtn.textContent = 'Verify & Complete Signup';
    }
});

// Resend OTP
resendLink.addEventListener('click', async (e) => {
    e.preventDefault();
    if (timeRemaining > 0) return;

    try {
        const res = await fetch('../frameworks/resend_otp.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        });

        const data = await res.json();

        if (data.success) {
            showStatus('New OTP sent to ' + currentEmail);
            startResendTimer();
        } else {
            showStatus(data.message || 'Failed to resend OTP', true);
        }
    } catch (error) {
        showStatus('Error resending OTP', true);
    }
});

// Form submission - now only for validation display (sendCodeBtn handles actual flow)
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

        const result = await response.json();

        // This branch should not be reached - sendCodeBtn handles flow
        if (result.success) {
            showStatus('Please use Send Code button for signup flow.', false);
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
