<?php
// admin_register.php
session_start();
require_once '../config.php';

// Check if registrations are allowed (you can make this configurable)
$allow_registration = true; // Set to false to disable public registration

// Optional: Require invitation token
$require_invitation = false; // Set to true to require invitation codes

if (!$allow_registration) {
    header('Location: admin_login.php?error=registration_disabled');
    exit;
}

// Check invitation token if required
if ($require_invitation && !isset($_GET['token'])) {
    header('Location: admin_login.php?error=invitation_required');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration | Commercial Pool Equipment and Supplies</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .admin-gradient {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        }
        .error-message {
            color: #dc2626;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
        }
        .success-message {
            color: #16a34a;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
        }
        .step-indicator {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #e5e7eb;
            color: #6b7280;
            font-weight: 600;
            transition: all 0.3s;
        }
        .step-indicator.active {
            background-color: #3b82f6;
            color: white;
        }
        .step-indicator.completed {
            background-color: #10b981;
            color: white;
        }
        .form-step {
            display: none;
        }
        .form-step.active {
            display: block;
        }
        .password-strength {
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-6xl w-full">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center mb-4">
                    <i class="fas fa-shield-halved text-5xl text-blue-600 mr-3"></i>
                    <div class="text-left">
                        <h2 class="text-3xl font-bold text-gray-900">Admin Registration</h2>
                        <p class="text-gray-600">Commercial Pool Equipment & Supplies</p>
                    </div>
                </div>
                <p class="text-gray-600 mt-2">Create your administrative account</p>
            </div>

            <div class="flex flex-col lg:flex-row shadow-2xl rounded-2xl overflow-hidden">
                <!-- Left Column - Registration Form -->
                <div class="lg:w-2/3 bg-white p-8 lg:p-12">
                    <!-- Step Indicators -->
                    <div class="mb-8">
                        <div class="flex items-center justify-center space-x-4">
                            <div class="flex flex-col items-center">
                                <span id="step1Indicator" class="step-indicator active mb-2">1</span>
                                <span class="text-xs font-medium text-gray-700">Account Info</span>
                            </div>
                            <div class="h-0.5 w-16 bg-gray-300"></div>
                            <div class="flex flex-col items-center">
                                <span id="step2Indicator" class="step-indicator mb-2">2</span>
                                <span class="text-xs font-medium text-gray-600">Personal Details</span>
                            </div>
                            <div class="h-0.5 w-16 bg-gray-300"></div>
                            <div class="flex flex-col items-center">
                                <span id="step3Indicator" class="step-indicator mb-2">3</span>
                                <span class="text-xs font-medium text-gray-600">Security</span>
                            </div>
                        </div>
                    </div>

                    <?php if (isset($_GET['success'])): ?>
                        <div class="mb-6 p-4 bg-[#022658]/10 border border-[#022658]/20 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-[#022658] mr-3"></i>
                                <p class="text-[#022658] font-medium">Registration successful! You can now sign in.</p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Registration Form -->
                    <form id="adminRegisterForm" action="../api/admin/process_register.php" method="POST" class="space-y-6">
                        <?php if ($require_invitation && isset($_GET['token'])): ?>
                            <input type="hidden" name="invitation_token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                        <?php endif; ?>

                        <!-- Step 1: Account Information -->
                        <div id="step1" class="form-step active">
                            <h3 class="text-xl font-bold text-gray-900 mb-6">Account Information</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                                        Username <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-3.5 text-gray-400">
                                            <i class="fas fa-user"></i>
                                        </span>
                                        <input type="text" 
                                               id="username" 
                                               name="username" 
                                               required 
                                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               placeholder="Choose a username">
                                    </div>
                                    <span id="usernameError" class="error-message"></span>
                                    <span id="usernameSuccess" class="success-message"></span>
                                    <p class="text-xs text-gray-500 mt-1">3-20 characters, letters and numbers only</p>
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                        Email Address <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-3.5 text-gray-400">
                                            <i class="fas fa-envelope"></i>
                                        </span>
                                        <input type="email" 
                                               id="email" 
                                               name="email" 
                                               required 
                                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               placeholder="your.email@example.com">
                                    </div>
                                    <span id="emailError" class="error-message"></span>
                                    <span id="emailSuccess" class="success-message"></span>
                                </div>

                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                        Password <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-3.5 text-gray-400">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        <input type="password" 
                                               id="password" 
                                               name="password" 
                                               required 
                                               class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               placeholder="Create a strong password">
                                        <button type="button" 
                                                id="togglePassword" 
                                                class="absolute right-3 top-3.5 text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="mt-2">
                                        <div class="password-strength bg-gray-200" id="passwordStrength"></div>
                                        <p class="text-xs text-gray-500 mt-1" id="passwordStrengthText">Password strength</p>
                                    </div>
                                    <span id="passwordError" class="error-message"></span>
                                </div>

                                <div>
                                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                                        Confirm Password <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-3.5 text-gray-400">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        <input type="password" 
                                               id="confirm_password" 
                                               name="confirm_password" 
                                               required 
                                               class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               placeholder="Re-enter your password">
                                        <button type="button" 
                                                id="toggleConfirmPassword" 
                                                class="absolute right-3 top-3.5 text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <span id="confirmPasswordError" class="error-message"></span>
                                    <span id="confirmPasswordSuccess" class="success-message"></span>
                                </div>
                            </div>

                            <div class="flex justify-end mt-8">
                                <button type="button" 
                                        id="nextStep1" 
                                        class="bg-blue-600 text-white font-semibold py-3 px-8 rounded-lg hover:bg-blue-700 transition">
                                    Next <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Personal Details -->
                        <div id="step2" class="form-step">
                            <h3 class="text-xl font-bold text-gray-900 mb-6">Personal Details</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        First Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           id="first_name" 
                                           name="first_name" 
                                           required 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="John">
                                    <span id="firstNameError" class="error-message"></span>
                                </div>

                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Last Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           id="last_name" 
                                           name="last_name" 
                                           required 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="Doe">
                                    <span id="lastNameError" class="error-message"></span>
                                </div>

                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                        Phone Number
                                    </label>
                                    <input type="tel" 
                                           id="phone" 
                                           name="phone" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="+44 123 456 7890">
                                    <span id="phoneError" class="error-message"></span>
                                </div>

                                <div>
                                    <label for="department" class="block text-sm font-medium text-gray-700 mb-2">
                                        Department
                                    </label>
                                    <select id="department" 
                                            name="department" 
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Select Department</option>
                                        <option value="sales">Sales</option>
                                        <option value="marketing">Marketing</option>
                                        <option value="operations">Operations</option>
                                        <option value="customer_service">Customer Service</option>
                                        <option value="it">IT</option>
                                        <option value="management">Management</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">
                                        Bio / About
                                    </label>
                                    <textarea id="bio" 
                                              name="bio" 
                                              rows="3" 
                                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                              placeholder="Tell us a bit about yourself..."></textarea>
                                </div>
                            </div>

                            <div class="flex justify-between mt-8">
                                <button type="button" 
                                        id="prevStep2" 
                                        class="text-gray-700 font-semibold py-3 px-8 rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                                    <i class="fas fa-arrow-left mr-2"></i> Back
                                </button>
                                <button type="button" 
                                        id="nextStep2" 
                                        class="bg-blue-600 text-white font-semibold py-3 px-8 rounded-lg hover:bg-blue-700 transition">
                                    Next <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 3: Security & Confirmation -->
                        <div id="step3" class="form-step">
                            <h3 class="text-xl font-bold text-gray-900 mb-6">Security & Confirmation</h3>
                            
                            <div class="space-y-6">
                                <div>
                                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                                        Requested Role <span class="text-red-500">*</span>
                                    </label>
                                    <select id="role" 
                                            name="role" 
                                            required 
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="editor">Editor</option>
                                        <option value="admin">Administrator</option>
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">Admin roles require approval from existing administrators</p>
                                </div>

                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <h4 class="font-semibold text-blue-900 mb-2">Role Permissions:</h4>
                                    <div class="space-y-2 text-sm text-blue-800">
                                        <div class="flex items-start">
                                            <i class="fas fa-check-circle text-blue-600 mr-2 mt-0.5"></i>
                                            <span><strong>Editor:</strong> Can manage products, orders, and customers</span>
                                        </div>
                                        <div class="flex items-start">
                                            <i class="fas fa-check-circle text-blue-600 mr-2 mt-0.5"></i>
                                            <span><strong>Administrator:</strong> Full system access including user management and settings</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Terms and Conditions -->
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-start mb-4">
                                        <input type="checkbox" 
                                               id="terms" 
                                               name="terms" 
                                               required 
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1">
                                        <label for="terms" class="ml-3 text-sm text-gray-700">
                                            I agree to the <a href="#" class="text-blue-600 hover:underline">Admin Terms of Service</a> 
                                            and <a href="#" class="text-blue-600 hover:underline">Privacy Policy</a>. 
                                            I understand my actions will be logged and monitored.
                                        </label>
                                    </div>
                                    <span id="termsError" class="error-message"></span>

                                    <div class="flex items-start mb-4">
                                        <input type="checkbox" 
                                               id="data_handling" 
                                               name="data_handling" 
                                               required 
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1">
                                        <label for="data_handling" class="ml-3 text-sm text-gray-700">
                                            I acknowledge that I will handle customer data responsibly and in compliance with GDPR regulations.
                                        </label>
                                    </div>
                                    <span id="dataHandlingError" class="error-message"></span>

                                    <div class="flex items-start">
                                        <input type="checkbox" 
                                               id="code_of_conduct" 
                                               name="code_of_conduct" 
                                               required 
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1">
                                        <label for="code_of_conduct" class="ml-3 text-sm text-gray-700">
                                            I agree to follow the administrator code of conduct and maintain system security.
                                        </label>
                                    </div>
                                    <span id="codeOfConductError" class="error-message"></span>
                                </div>

                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <div class="flex items-start">
                                        <i class="fas fa-exclamation-triangle text-yellow-600 mr-3 mt-0.5"></i>
                                        <div class="text-sm text-yellow-800">
                                            <p class="font-semibold mb-1">Important Security Notice:</p>
                                            <p>Your account may require approval before activation. You will receive an email notification once your account is reviewed.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-between mt-8">
                                <button type="button" 
                                        id="prevStep3" 
                                        class="text-gray-700 font-semibold py-3 px-8 rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                                    <i class="fas fa-arrow-left mr-2"></i> Back
                                </button>
                                <button type="submit" 
                                        id="submitBtn" 
                                        class="bg-blue-600 text-white font-semibold py-3 px-8 rounded-lg hover:bg-blue-700 transition">
                                    <i class="fas fa-user-plus mr-2"></i> Create Account
                                </button>
                            </div>
                        </div>
                    </form>

                    <?php if (isset($_GET['error'])): ?>
                        <div id="serverError" class="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                                <p class="text-red-700 font-medium">
                                    <?php 
                                    $errors = [
                                        'username_exists' => 'This username is already taken.',
                                        'email_exists' => 'An account with this email already exists.',
                                        'invalid_token' => 'Invalid or expired invitation token.',
                                        'passwords_mismatch' => 'Passwords do not match.',
                                        'weak_password' => 'Password does not meet security requirements.',
                                        'registration_disabled' => 'Registration is currently disabled.',
                                        'invitation_required' => 'An invitation is required to register.',
                                        'system' => 'A system error occurred. Please try again later.'
                                    ];
                                    echo $errors[$_GET['error']] ?? 'An error occurred. Please try again.';
                                    ?>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Already have account -->
                    <div class="mt-8 text-center">
                        <p class="text-gray-600">
                            Already have an account? 
                            <a href="admin_login.php" class="text-blue-600 font-semibold hover:underline">
                                Sign in here
                            </a>
                        </p>
                    </div>
                </div>

                <!-- Right Column - Info Panel -->
                <div class="lg:w-1/3 admin-gradient p-8 lg:p-10 text-white flex flex-col justify-center">
                    <div class="mb-8">
                        <i class="fas fa-shield-alt text-6xl opacity-20 mb-6"></i>
                        <h2 class="text-2xl font-bold mb-4">Join Our Admin Team</h2>
                        <p class="text-blue-100 mb-6">
                            Get access to powerful tools to manage the platform effectively.
                        </p>
                    </div>

                    <div class="space-y-4 mb-8">
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-blue-300 mr-3 mt-1"></i>
                            <span>Secure role-based access control</span>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-blue-300 mr-3 mt-1"></i>
                            <span>Comprehensive activity logging</span>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-blue-300 mr-3 mt-1"></i>
                            <span>Advanced product management</span>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-blue-300 mr-3 mt-1"></i>
                            <span>Real-time analytics dashboard</span>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-blue-300 mr-3 mt-1"></i>
                            <span>Order processing & tracking</span>
                        </div>
                    </div>

                    <div class="pt-8 border-t border-blue-400">
                        <h3 class="text-lg font-bold mb-3">Need Help?</h3>
                        <p class="text-sm text-blue-100 mb-4">
                            Contact your system administrator if you have questions about the registration process.
                        </p>
                        <div class="flex items-center text-sm">
                            <i class="fas fa-envelope mr-2"></i>
                            <span>admin@commercialpoolequipment.com</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin_register.js"></script>
</body>
</html>