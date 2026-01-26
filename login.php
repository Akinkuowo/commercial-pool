<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Commercial Pool Equipment</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet">
      <!-- Adobe Fonts - Myriad Pro -->
    <link rel="stylesheet" href="https://use.typekit.net/yzr5vmg.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="assets/css/styles.css" rel="stylesheet" />
    <?php include('include/style.php') ?>
</head>
<body class="bg-gray-50">
    <?php include('include/header.php') ?>

    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-6xl w-full flex flex-col lg:flex-row shadow-xl rounded-2xl overflow-hidden">
            <!-- Left Column - Login Form -->
            <div class="lg:w-1/2 bg-white p-8 lg:p-12">
                <!-- Breadcrumbs -->
                <nav class="mb-8" aria-label="Breadcrumb">
                    <ol class="flex items-center text-sm text-gray-600">
                        <li class="inline-flex items-center">
                            <a href="/" class="hover:text-[#022658]">Home</a>
                            <span class="mx-2">›</span>
                        </li>
                        <li class="inline-flex items-center text-gray-900 font-medium" aria-current="page">
                            Login
                        </li>
                    </ol>
                </nav>
                
                <h1 class="text-3xl font-bold text-gray-900 mb-8">Sign in</h1>
                
                <?php if (isset($_GET['registered']) && $_GET['registered'] == 'true'): ?>
                    <div class="mb-6 p-4 bg-[#022658]/10 border border-[#022658]/20 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-[#022658] mr-3"></i>
                            <p class="text-[#022658] font-medium">Account created successfully! Please sign in.</p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['reset']) && $_GET['reset'] == 'success'): ?>
                    <div class="mb-6 p-4 bg-[#022658]/10 border border-[#022658]/20 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-[#022658] mr-3"></i>
                            <p class="text-[#022658] font-medium">Password reset successfully! You can now sign in.</p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['logout']) && $_GET['logout'] == 'success'): ?>
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle text-blue-500 mr-3"></i>
                            <p class="text-blue-700 font-medium">You have been logged out successfully.</p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Login Form -->
                <form id="loginForm" action="api/process_login.php" method="POST" class="space-y-6">
                    <input type="hidden" name="redirect" value="<?php echo isset($_GET['redirect']) ? htmlspecialchars($_GET['redirect']) : ''; ?>">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address:
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg form-input focus:outline-none focus:ring-2 focus:ring-[#022658]"
                               placeholder="your.email@example.com"
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        <div id="emailError" class="error-message"></div>
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password:
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg form-input focus:outline-none focus:ring-2 focus:ring-[#022658] pr-12"
                                   placeholder="Enter your password">
                            <button type="button" 
                                    id="togglePassword" 
                                    class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 focus:outline-none">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div id="passwordError" class="error-message"></div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="remember" 
                                   name="remember"
                                   class="h-4 w-4 text-[#022658] focus:ring-[#022658] border-gray-300 rounded">
                            <label for="remember" class="ml-2 block text-sm text-gray-700">
                                Remember me
                            </label>
                        </div>
                        <a href="forgot_password.php" class="text-sm text-[#022658] hover:text-[#011a3d] hover:underline">
                            Forgot your password?
                        </a>
                    </div>
                    
                    <button type="submit" 
                            id="submitBtn"
                            class="w-full btn-primary text-white font-semibold py-3 px-4 rounded-lg hover:opacity-90 transition duration-300">
                        Sign in
                    </button>
                </form>
                
                <?php if (isset($_GET['error'])): ?>
                    <div id="serverError" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                            <p class="text-red-700 font-medium">
                                <?php 
                                $errors = [
                                    'invalid' => 'Invalid email or password.',
                                    'locked' => 'Account is temporarily locked due to multiple failed login attempts. Please try again in 15 minutes.',
                                    'inactive' => 'Your account is inactive. Please contact support.',
                                    'not_found' => 'No account found with this email address.',
                                    'empty' => 'Please enter both email and password.',
                                    'config' => 'System configuration error. Please contact support.',
                                    'system' => 'A system error occurred. Please try again later.'
                                ];
                                echo $errors[$_GET['error']] ?? 'An error occurred. Please try again.';
                                ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Right Column - New Customer Info -->
            <div class="lg:w-1/2 bg-gradient-to-br from-[#022658] to-[#011a3d] p-8 lg:p-12 text-white">
                <h2 class="text-2xl font-bold mb-6">Trade Account Member?</h2>
                <p class="text-lg mb-6">Create an account with us and you'll be able to:</p>
                
                <ul class="space-y-4 mb-8">
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-blue-200 mr-3 mt-1"></i>
                        <span>Check out faster</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-blue-200 mr-3 mt-1"></i>
                        <span>Save multiple shipping addresses</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-blue-200 mr-3 mt-1"></i>
                        <span>Access your order history</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-blue-200 mr-3 mt-1"></i>
                        <span>Track new orders</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-blue-200 mr-3 mt-1"></i>
                        <span>Save items to your lists</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-blue-200 mr-3 mt-1"></i>
                        <span class="font-semibold">View trade prices and discounts</span>
                    </li>
                </ul>
                
                <a href="register.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>" 
                   class="inline-block bg-white text-[#022658] font-semibold py-3 px-8 rounded-lg hover:bg-gray-100 transition duration-300 transform hover:scale-105">
                    Create Account
                </a>
                
                <div class="mt-12 pt-8 border-t border-[#022658]/30">
                    <h3 class="text-xl font-bold mb-4">Trade Account Benefits</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-start">
                            <i class="fas fa-percentage text-blue-200 mr-2 mt-1"></i>
                            <span>Exclusive trade discounts</span>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-truck text-blue-200 mr-2 mt-1"></i>
                            <span>Priority shipping</span>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-headset text-blue-200 mr-2 mt-1"></i>
                            <span>Dedicated trade support</span>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-file-invoice-dollar text-blue-200 mr-2 mt-1"></i>
                            <span>Trade credit accounts</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('include/footer.php') ?>
    <!-- <?php include('include/script.php') ?> -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const togglePasswordBtn = document.getElementById('togglePassword');
            const submitBtn = document.getElementById('submitBtn');
            
            // Toggle password visibility
            togglePasswordBtn.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
            
            // Form validation
            loginForm.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Clear previous errors
                document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
                const serverError = document.getElementById('serverError');
                if (serverError) serverError.style.display = 'none';
                
                // Email validation
                const email = emailInput.value.trim();
                if (!email) {
                    document.getElementById('emailError').textContent = 'Email address is required';
                    isValid = false;
                } else if (!isValidEmail(email)) {
                    document.getElementById('emailError').textContent = 'Please enter a valid email address';
                    isValid = false;
                }
                
                // Password validation
                const password = passwordInput.value;
                if (!password) {
                    document.getElementById('passwordError').textContent = 'Password is required';
                    isValid = false;
                }
                
                if (!isValid) {
                    e.preventDefault();
                } else {
                    // Show loading state
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Signing in...';
                }
            });
            
            // Real-time validation
            emailInput.addEventListener('blur', validateEmail);
            passwordInput.addEventListener('blur', validatePassword);
            
            function validateEmail() {
                const email = emailInput.value.trim();
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
            
            function validatePassword() {
                const password = passwordInput.value;
                const errorElement = document.getElementById('passwordError');
                
                if (!password) {
                    errorElement.textContent = 'Password is required';
                    return false;
                } else {
                    errorElement.textContent = '';
                    return true;
                }
            }
            
            function isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }
        });
    </script>
</body>
</html>