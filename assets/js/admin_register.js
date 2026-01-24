// assets/js/admin_register.js

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('adminRegisterForm');
    const steps = document.querySelectorAll('.form-step');
    const stepIndicators = [
        document.getElementById('step1Indicator'),
        document.getElementById('step2Indicator'),
        document.getElementById('step3Indicator')
    ];
    
    let currentStep = 0;

    // Form fields
    const username = document.getElementById('username');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const firstName = document.getElementById('first_name');
    const lastName = document.getElementById('last_name');
    const terms = document.getElementById('terms');
    const dataHandling = document.getElementById('data_handling');
    const codeOfConduct = document.getElementById('code_of_conduct');

    // Password toggle
    const togglePassword = document.getElementById('togglePassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');

    togglePassword?.addEventListener('click', function() {
        togglePasswordVisibility(password, this);
    });

    toggleConfirmPassword?.addEventListener('click', function() {
        togglePasswordVisibility(confirmPassword, this);
    });

    function togglePasswordVisibility(input, button) {
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
        button.querySelector('i').classList.toggle('fa-eye');
        button.querySelector('i').classList.toggle('fa-eye-slash');
    }

    // Real-time username validation
    username?.addEventListener('blur', async function() {
        await validateUsername();
    });

    username?.addEventListener('input', function() {
        clearError('username');
        clearSuccess('username');
    });

    // Real-time email validation
    email?.addEventListener('blur', async function() {
        await validateEmail();
    });

    email?.addEventListener('input', function() {
        clearError('email');
        clearSuccess('email');
    });

    // Password strength checker
    password?.addEventListener('input', function() {
        checkPasswordStrength(this.value);
        if (confirmPassword.value) {
            validatePasswordMatch();
        }
    });

    // Confirm password validation
    confirmPassword?.addEventListener('input', function() {
        validatePasswordMatch();
    });

    // Step navigation
    document.getElementById('nextStep1')?.addEventListener('click', async function() {
        if (await validateStep1()) {
            goToStep(1);
        }
    });

    document.getElementById('prevStep2')?.addEventListener('click', function() {
        goToStep(0);
    });

    document.getElementById('nextStep2')?.addEventListener('click', function() {
        if (validateStep2()) {
            goToStep(2);
        }
    });

    document.getElementById('prevStep3')?.addEventListener('click', function() {
        goToStep(1);
    });

    // Form submission
    form?.addEventListener('submit', async function(e) {
        e.preventDefault();

        if (!await validateStep1() || !validateStep2() || !validateStep3()) {
            return;
        }

        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Creating Account...';

        // Submit form
        this.submit();
    });

    // Validation functions
    async function validateUsername() {
        const value = username.value.trim();
        const errorEl = document.getElementById('usernameError');
        const successEl = document.getElementById('usernameSuccess');

        clearError('username');
        clearSuccess('username');

        if (!value) {
            showError('username', 'Username is required');
            return false;
        }

        if (value.length < 3 || value.length > 20) {
            showError('username', 'Username must be 3-20 characters');
            return false;
        }

        if (!/^[a-zA-Z0-9]+$/.test(value)) {
            showError('username', 'Username can only contain letters and numbers');
            return false;
        }

        // Check if username exists (you'd need to create this endpoint)
        try {
            const response = await fetch(`api/admin/check_username.php?username=${encodeURIComponent(value)}`);
            const data = await response.json();
            
            if (data.exists) {
                showError('username', 'This username is already taken');
                return false;
            }

            showSuccess('username', 'Username is available');
            return true;
        } catch (error) {
            console.error('Error checking username:', error);
            return true; // Allow to proceed if check fails
        }
    }

    async function validateEmail() {
        const value = email.value.trim();
        
        clearError('email');
        clearSuccess('email');

        if (!value) {
            showError('email', 'Email is required');
            return false;
        }

        if (!isValidEmail(value)) {
            showError('email', 'Please enter a valid email address');
            return false;
        }

        // Check if email exists
        try {
            const response = await fetch(`api/admin/check_email.php?email=${encodeURIComponent(value)}`);
            const data = await response.json();
            
            if (data.exists) {
                showError('email', 'An account with this email already exists');
                return false;
            }

            showSuccess('email', 'Email is available');
            return true;
        } catch (error) {
            console.error('Error checking email:', error);
            return true;
        }
    }

    function validatePassword() {
        const value = password.value;
        
        clearError('password');

        if (!value) {
            showError('password', 'Password is required');
            return false;
        }

        if (value.length < 8) {
            showError('password', 'Password must be at least 8 characters');
            return false;
        }

        if (!/[A-Z]/.test(value)) {
            showError('password', 'Password must contain at least one uppercase letter');
            return false;
        }

        if (!/[a-z]/.test(value)) {
            showError('password', 'Password must contain at least one lowercase letter');
            return false;
        }

        if (!/[0-9]/.test(value)) {
            showError('password', 'Password must contain at least one number');
            return false;
        }

        if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(value)) {
            showError('password', 'Password must contain at least one special character');
            return false;
        }

        return true;
    }

    function validatePasswordMatch() {
        clearError('confirmPassword');
        clearSuccess('confirmPassword');

        if (!confirmPassword.value) {
            showError('confirmPassword', 'Please confirm your password');
            return false;
        }

        if (password.value !== confirmPassword.value) {
            showError('confirmPassword', 'Passwords do not match');
            return false;
        }

        showSuccess('confirmPassword', 'Passwords match');
        return true;
    }

    function checkPasswordStrength(password) {
        const strengthBar = document.getElementById('passwordStrength');
        const strengthText = document.getElementById('passwordStrengthText');
        
        let strength = 0;
        let text = '';
        let color = '';

        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) strength++;

        switch (strength) {
            case 0:
            case 1:
                text = 'Weak password';
                color = '#ef4444';
                break;
            case 2:
            case 3:
                text = 'Medium password';
                color = '#f59e0b';
                break;
            case 4:
                text = 'Strong password';
                color = '#10b981';
                break;
            case 5:
                text = 'Very strong password';
                color = '#059669';
                break;
        }

        strengthBar.style.width = (strength * 20) + '%';
        strengthBar.style.backgroundColor = color;
        strengthText.textContent = text;
        strengthText.style.color = color;
    }

    async function validateStep1() {
        const usernameValid = await validateUsername();
        const emailValid = await validateEmail();
        const passwordValid = validatePassword();
        const passwordMatchValid = validatePasswordMatch();

        return usernameValid && emailValid && passwordValid && passwordMatchValid;
    }

    function validateStep2() {
        let isValid = true;

        clearError('firstName');
        clearError('lastName');

        if (!firstName.value.trim()) {
            showError('firstName', 'First name is required');
            isValid = false;
        }

        if (!lastName.value.trim()) {
            showError('lastName', 'Last name is required');
            isValid = false;
        }

        return isValid;
    }

    function validateStep3() {
        let isValid = true;

        clearError('terms');
        clearError('dataHandling');
        clearError('codeOfConduct');

        if (!terms.checked) {
            showError('terms', 'You must agree to the terms and conditions');
            isValid = false;
        }

        if (!dataHandling.checked) {
            showError('dataHandling', 'You must acknowledge data handling responsibilities');
            isValid = false;
        }

        if (!codeOfConduct.checked) {
            showError('codeOfConduct', 'You must agree to the code of conduct');
            isValid = false;
        }

        return isValid;
    }

    function goToStep(step) {
        steps[currentStep].classList.remove('active');
        stepIndicators[currentStep].classList.remove('active');
        stepIndicators[currentStep].classList.add('completed');

        currentStep = step;

        steps[currentStep].classList.add('active');
        stepIndicators[currentStep].classList.add('active');

        // Update indicators
        for (let i = 0; i < stepIndicators.length; i++) {
            if (i < currentStep) {
                stepIndicators[i].classList.add('completed');
                stepIndicators[i].classList.remove('active');
            } else if (i === currentStep) {
                stepIndicators[i].classList.add('active');
                stepIndicators[i].classList.remove('completed');
            } else {
                stepIndicators[i].classList.remove('active', 'completed');
            }
        }

        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Helper functions
    function showError(fieldName, message) {
        const errorEl = document.getElementById(fieldName + 'Error');
        if (errorEl) {
            errorEl.textContent = message;
        }
    }

    function clearError(fieldName) {
        const errorEl = document.getElementById(fieldName + 'Error');
        if (errorEl) {
            errorEl.textContent = '';
        }
    }

    function showSuccess(fieldName, message) {
        const successEl = document.getElementById(fieldName + 'Success');
        if (successEl) {
            successEl.textContent = message;
        }
    }

    function clearSuccess(fieldName) {
        const successEl = document.getElementById(fieldName + 'Success');
        if (successEl) {
            successEl.textContent = '';
        }
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
});