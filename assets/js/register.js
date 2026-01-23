// assets/js/register.js - Registration Form Handler

document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 1;
    const totalSteps = 3;
    const passwordToggle = document.getElementById('togglePassword');
    
    // Step navigation event listeners
    document.getElementById('nextStep1').addEventListener('click', () => validateStep1());
    document.getElementById('nextStep2').addEventListener('click', () => validateStep2());
    document.getElementById('prevStep2').addEventListener('click', () => goToStep(1));
    document.getElementById('prevStep3').addEventListener('click', () => goToStep(2));
    
    // Toggle password visibility
    if (passwordToggle) {
        passwordToggle.addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    }
    
    // Step navigation functions
    function goToStep(step) {
        document.querySelectorAll('.form-step').forEach(el => {
            el.classList.add('hidden');
        });
        
        document.getElementById(`step${step}`).classList.remove('hidden');
        updateStepIndicators(step);
        currentStep = step;
        
        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    
    function updateStepIndicators(activeStep) {
        for (let i = 1; i <= totalSteps; i++) {
            const indicator = document.getElementById(`step${i}Indicator`);
            if (i < activeStep) {
                indicator.className = 'step-indicator completed';
                indicator.innerHTML = '<i class="fas fa-check"></i>';
            } else if (i === activeStep) {
                indicator.className = 'step-indicator active';
                indicator.textContent = i;
            } else {
                indicator.className = 'step-indicator';
                indicator.textContent = i;
            }
        }
    }
    
    // Validation functions
    function validateStep1() {
        let isValid = true;
        clearStep1Errors();
        
        const email = document.getElementById('email').value.trim();
        if (!email) {
            document.getElementById('emailError').textContent = 'Email address is required';
            isValid = false;
        } else if (!isValidEmail(email)) {
            document.getElementById('emailError').textContent = 'Please enter a valid email address';
            isValid = false;
        }
        
        const password = document.getElementById('password').value;
        if (!password) {
            document.getElementById('passwordError').textContent = 'Password is required';
            isValid = false;
        } else if (!isStrongPassword(password)) {
            document.getElementById('passwordError').textContent = 'Password must be at least 8 characters with uppercase, lowercase, and numbers';
            isValid = false;
        }
        
        const confirmPassword = document.getElementById('confirm_password').value;
        if (!confirmPassword) {
            document.getElementById('confirmPasswordError').textContent = 'Please confirm your password';
            isValid = false;
        } else if (password !== confirmPassword) {
            document.getElementById('confirmPasswordError').textContent = 'Passwords do not match';
            isValid = false;
        }
        
        const businessName = document.getElementById('business_name').value.trim();
        if (!businessName) {
            document.getElementById('businessNameError').textContent = 'Business name is required';
            isValid = false;
        }
        
        const businessType = document.getElementById('business_type').value;
        if (!businessType) {
            document.getElementById('businessTypeError').textContent = 'Please select a business type';
            isValid = false;
        }
        
        const websiteUrl = document.getElementById('website_url').value.trim();
        if (websiteUrl && !isValidUrl(websiteUrl)) {
            document.getElementById('websiteUrlError').textContent = 'Please enter a valid URL';
            isValid = false;
        }
        
        if (isValid) {
            goToStep(2);
        }
        
        return isValid;
    }
    
    function validateStep2() {
        let isValid = true;
        clearStep2Errors();
        
        const firstName = document.getElementById('first_name').value.trim();
        if (!firstName) {
            document.getElementById('firstNameError').textContent = 'First name is required';
            isValid = false;
        }
        
        const lastName = document.getElementById('last_name').value.trim();
        if (!lastName) {
            document.getElementById('lastNameError').textContent = 'Last name is required';
            isValid = false;
        }
        
        const companyName = document.getElementById('company_name').value.trim();
        if (!companyName) {
            document.getElementById('companyNameError').textContent = 'Company name is required';
            isValid = false;
        }
        
        const phoneNumber = document.getElementById('phone_number').value.trim();
        if (!phoneNumber) {
            document.getElementById('phoneNumberError').textContent = 'Phone number is required';
            isValid = false;
        } else if (!isValidPhoneNumber(phoneNumber)) {
            document.getElementById('phoneNumberError').textContent = 'Please enter a valid phone number';
            isValid = false;
        }
        
        const mobileNumber = document.getElementById('mobile_number').value.trim();
        if (mobileNumber && !isValidPhoneNumber(mobileNumber)) {
            document.getElementById('mobileNumberError').textContent = 'Please enter a valid mobile number';
            isValid = false;
        }
        
        if (isValid) {
            goToStep(3);
        }
        
        return isValid;
    }
    
    function clearStep1Errors() {
        ['emailError', 'passwordError', 'confirmPasswordError', 'businessNameError', 'businessTypeError', 'websiteUrlError'].forEach(id => {
            document.getElementById(id).textContent = '';
        });
    }
    
    function clearStep2Errors() {
        ['firstNameError', 'lastNameError', 'companyNameError', 'phoneNumberError', 'mobileNumberError'].forEach(id => {
            document.getElementById(id).textContent = '';
        });
    }
    
    function clearStep3Errors() {
        ['addressLine1Error', 'townCityError', 'countryError', 'postcodeError', 'termsError'].forEach(id => {
            document.getElementById(id).textContent = '';
        });
    }
    
    // Helper validation functions
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    function isStrongPassword(password) {
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d\S]{8,}$/;
        return passwordRegex.test(password);
    }
    
    function isValidUrl(url) {
        try {
            new URL(url);
            return true;
        } catch (_) {
            return false;
        }
    }
    
    function isValidPhoneNumber(phone) {
        const phoneRegex = /^[\d\s\-+()]{10,20}$/;
        return phoneRegex.test(phone);
    }
    
    // Form submission
    document.getElementById('registerForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validate step 3
        let isValid = true;
        clearStep3Errors();
        
        const addressLine1 = document.getElementById('address_line1').value.trim();
        if (!addressLine1) {
            document.getElementById('addressLine1Error').textContent = 'Address line 1 is required';
            isValid = false;
        }
        
        const townCity = document.getElementById('town_city').value.trim();
        if (!townCity) {
            document.getElementById('townCityError').textContent = 'Town/City is required';
            isValid = false;
        }
        
        const country = document.getElementById('country').value;
        if (!country) {
            document.getElementById('countryError').textContent = 'Country is required';
            isValid = false;
        }
        
        const postcode = document.getElementById('postcode').value.trim();
        if (!postcode) {
            document.getElementById('postcodeError').textContent = 'Postcode is required';
            isValid = false;
        }
        
        const terms = document.getElementById('terms').checked;
        if (!terms) {
            document.getElementById('termsError').textContent = 'You must agree to the terms and conditions';
            isValid = false;
        }
        
        if (!isValid) {
            return;
        }
        
        // Show loading state
        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Creating Account...';
        
        // Prepare form data
        const formData = new FormData(this);
        
        try {
            const response = await fetch('api/register.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showAlert('success', 'Account created successfully! Redirecting...');
                
                // Redirect after 2 seconds
                setTimeout(() => {
                    window.location.href = data.redirect || 'dashboard.php';
                }, 2000);
            } else {
                showAlert('error', data.error || 'Failed to create account. Please try again.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                
                // If there are specific field errors, display them
                if (data.errors && Array.isArray(data.errors)) {
                    // Go back to appropriate step if needed
                    if (!validateStep1()) {
                        goToStep(1);
                    } else if (!validateStep2()) {
                        goToStep(2);
                    }
                }
            }
        } catch (error) {
            console.error('Registration error:', error);
            showAlert('error', 'An error occurred. Please try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
    
    // Show alert function
    function showAlert(type, message) {
        const alertContainer = document.getElementById('alertContainer');
        const colors = {
            success: 'bg-green-50 border-green-200 text-green-700',
            error: 'bg-red-50 border-red-200 text-red-700',
            warning: 'bg-yellow-50 border-yellow-200 text-yellow-700',
            info: 'bg-blue-50 border-blue-200 text-blue-700'
        };
        
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        
        alertContainer.className = `mb-6 p-4 border rounded-lg ${colors[type]}`;
        alertContainer.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${icons[type]} mr-3"></i>
                <p class="font-medium">${message}</p>
            </div>
        `;
        alertContainer.classList.remove('hidden');
        
        // Scroll to alert
        alertContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    // Real-time validation
    document.getElementById('email').addEventListener('blur', validateEmailField);
    document.getElementById('password').addEventListener('input', validatePasswordField);
    document.getElementById('confirm_password').addEventListener('blur', validatePasswordMatchField);
    
    function validateEmailField() {
        const email = document.getElementById('email').value.trim();
        const errorElement = document.getElementById('emailError');
        
        if (!email) {
            errorElement.textContent = 'Email address is required';
            return false;
        } else if (!isValidEmail(email)) {
            errorElement.textContent = 'Please enter a valid email address';
            return false;
        } else {
            errorElement.textContent = '';
            return true;
        }
    }
    
    function validatePasswordField() {
        const password = document.getElementById('password').value;
        const errorElement = document.getElementById('passwordError');
        
        if (password && !isStrongPassword(password)) {
            errorElement.textContent = 'Password must be at least 8 characters with uppercase, lowercase, and numbers';
            return false;
        } else {
            errorElement.textContent = '';
            return true;
        }
    }
    
    function validatePasswordMatchField() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const errorElement = document.getElementById('confirmPasswordError');
        
        if (confirmPassword && password !== confirmPassword) {
            errorElement.textContent = 'Passwords do not match';
            return false;
        } else {
            errorElement.textContent = '';
            return true;
        }
    }
});