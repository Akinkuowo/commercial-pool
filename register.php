<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Commercial Pool Equipment</title>
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
    
    <style>
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
            margin-right: 8px;
        }
        
        .step-indicator.active {
            background-color: #022658;
            color: white;
        }
        
        .step-indicator.completed {
            background-color: #022658;
            color: white;
        }
        
        .error-message {
            color: #dc2626;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
        }
        
        .required::after {
            content: " *";
            color: #dc2626;
        }
        
        .btn-primary {
            background-color: #022658;
        }
        
        .form-input:focus, .form-select:focus {
            border-color: #022658;
            ring-color: #022658;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include('include/header.php') ?>

    <div class="min-h-screen py-8 px-4">
        <div class="max-w-6xl mx-auto">
            <nav class="mb-6" aria-label="Breadcrumb">
                <ol class="flex items-center text-sm text-gray-600">
                    <li class="inline-flex items-center">
                        <a href="/" class="hover:text-[#011a3d]">Home</a>
                        <span class="mx-2">›</span>
                    </li>
                    <li class="inline-flex items-center text-gray-900 font-medium" aria-current="page">
                        Create Account
                    </li>
                </ol>
            </nav>
            
            <h1 class="text-3xl font-bold text-gray-900 mb-2">New Account</h1>
            <p class="text-gray-600 mb-8">Create your trade account to access exclusive benefits and pricing.</p>
            
            <!-- Alert Messages -->
            <div id="alertContainer" class="mb-6 hidden"></div>
            
            <!-- Multi-step Form -->
            <form id="registerForm" class="bg-white rounded-xl shadow-lg p-6 lg:p-8">
                <input type="hidden" name="redirect" value="<?php echo isset($_GET['redirect']) ? htmlspecialchars($_GET['redirect']) : ''; ?>">
                <!-- Step Indicators -->
                <div class="mb-8">
                    <div class="flex items-center justify-center space-x-6">
                        <div class="flex items-center">
                            <span id="step1Indicator" class="step-indicator active">1</span>
                            <span class="text-sm font-medium text-gray-700">Account Details</span>
                        </div>
                        <div class="h-0.5 w-16 bg-gray-300"></div>
                        <div class="flex items-center">
                            <span id="step2Indicator" class="step-indicator">2</span>
                            <span class="text-sm font-medium text-gray-600">Personal Details</span>
                        </div>
                        <div class="h-0.5 w-16 bg-gray-300"></div>
                        <div class="flex items-center">
                            <span id="step3Indicator" class="step-indicator">3</span>
                            <span class="text-sm font-medium text-gray-600">Address</span>
                        </div>
                    </div>
                </div>
                
                <!-- Step 1: Account Details -->
                <div id="step1" class="form-step">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2 required">
                                Email Address
                            </label>
                            <input type="email" id="email" name="email" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg form-input focus:outline-none"
                                   placeholder="your.email@example.com">
                            <span id="emailError" class="error-message"></span>
                        </div>
                        
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2 required">
                                Password
                            </label>
                            <div class="relative">
                                <input type="password" id="password" name="password" required 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg form-input focus:outline-none pr-12"
                                       placeholder="Minimum 8 characters">
                                <button type="button" id="togglePassword" 
                                        class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <span id="passwordError" class="error-message"></span>
                            <p class="text-xs text-gray-500 mt-1">Must contain uppercase, lowercase, and numbers</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2 required">
                                Confirm Password
                            </label>
                            <input type="password" id="confirm_password" name="confirm_password" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg form-input focus:outline-none"
                                   placeholder="Re-enter your password">
                            <span id="confirmPasswordError" class="error-message"></span>
                        </div>
                        
                        <div>
                            <label for="business_name" class="block text-sm font-medium text-gray-700 mb-2 required">
                                Business Name
                            </label>
                            <input type="text" id="business_name" name="business_name" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg form-input focus:outline-none"
                                   placeholder="Your business name">
                            <span id="businessNameError" class="error-message"></span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="business_type" class="block text-sm font-medium text-gray-700 mb-2 required">
                                Business Type
                            </label>
                            <select id="business_type" name="business_type" required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg form-select focus:outline-none appearance-none">
                                <option value="">Select Business Type</option>
                                <option value="Campervan Converter">Campervan Converter</option>
                                <option value="Campervan Sales">Campervan Sales</option>
                                <option value="Caravan Site">Caravan Site</option>
                                <option value="Caravan & Motorhome Dealer">Caravan & Motorhome Dealer</option>
                                <option value="Repair Engineer">Repair Engineer</option>
                                <option value="Repair Workshop">Repair Workshop</option>
                                <option value="Houseboat FW/ Specialist">Houseboat FW/ Specialist</option>
                                <option value="Accessory Installer">Accessory Installer</option>
                                <option value="Retailer">Retailer</option>
                                <option value="Wholesaler">Wholesaler</option>
                                <option value="OEM">OEM</option>
                                <option value="Other">Other</option>
                            </select>
                            <span id="businessTypeError" class="error-message"></span>
                        </div>
                        
                        <div>
                            <label for="website_url" class="block text-sm font-medium text-gray-700 mb-2">
                                Website URL
                            </label>
                            <input type="url" id="website_url" name="website_url" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg form-input focus:outline-none"
                                   placeholder="https://example.com">
                            <span id="websiteUrlError" class="error-message"></span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <label for="vat_number" class="block text-sm font-medium text-gray-700 mb-2">
                                VAT No.
                            </label>
                            <input type="text" id="vat_number" name="vat_number" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg form-input focus:outline-none"
                                   placeholder="GB123 4567 89">
                        </div>
                        
                        <div>
                            <label for="company_registration_no" class="block text-sm font-medium text-gray-700 mb-2">
                                Company Registration No.
                            </label>
                            <input type="text" id="company_registration_no" name="company_registration_no" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg form-input focus:outline-none"
                                   placeholder="01234567">
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="button" id="nextStep1" 
                                class="btn-primary text-white font-semibold py-3 px-8 rounded-lg hover:bg-[#011a3d] transition duration-300">
                            Next <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Step 2: Personal Details -->
                <div id="step2" class="form-step hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2 required">
                                First Name
                            </label>
                            <input type="text" id="first_name" name="first_name" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg form-input focus:outline-none"
                                   placeholder="John">
                            <span id="firstNameError" class="error-message"></span>
                        </div>
                        
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2 required">
                                Last Name
                            </label>
                            <input type="text" id="last_name" name="last_name" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg form-input focus:outline-none"
                                   placeholder="Doe">
                            <span id="lastNameError" class="error-message"></span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2 required">
                                Company Name
                            </label>
                            <input type="text" id="company_name" name="company_name" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg form-input focus:outline-none"
                                   placeholder="Your company name">
                            <span id="companyNameError" class="error-message"></span>
                        </div>
                        
                        <div>
                            <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2 required">
                                Phone Number
                            </label>
                            <input type="tel" id="phone_number" name="phone_number" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg form-input focus:outline-none"
                                   placeholder="01234 567890">
                            <span id="phoneNumberError" class="error-message"></span>
                        </div>
                    </div>
                    
                    <div class="mb-8">
                        <label for="mobile_number" class="block text-sm font-medium text-gray-700 mb-2">
                            Mobile Number
                        </label>
                        <input type="tel" id="mobile_number" name="mobile_number" 
                               class="w-full md:w-1/2 px-4 py-3 border border-gray-300 rounded-lg form-input focus:outline-none"
                               placeholder="07123 456789">
                        <span id="mobileNumberError" class="error-message"></span>
                    </div>
                    
                    <div class="flex justify-between">
                        <button type="button" id="prevStep2" 
                                class="text-gray-700 font-semibold py-3 px-8 rounded-lg border border-gray-300 hover:bg-gray-50 transition duration-300">
                            <i class="fas fa-arrow-left mr-2"></i> Back
                        </button>
                        
                        <button type="button" id="nextStep2" 
                                class="btn-primary text-white font-semibold py-3 px-8 rounded-lg hover:bg-[#011a3d] transition duration-300">
                            Next <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Step 3: Address Details -->
                <div id="step3" class="form-step hidden">
                    <div class="mb-6">
                        <label for="address_line1" class="block text-sm font-medium text-gray-700 mb-2 required">
                            Address Line 1
                        </label>
                        <input type="text" id="address_line1" name="address_line1" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg form-input focus:outline-none"
                               placeholder="Street address, P.O. box">
                        <span id="addressLine1Error" class="error-message"></span>
                    </div>
                    
                    <div class="mb-6">
                        <label for="address_line2" class="block text-sm font-medium text-gray-700 mb-2">
                            Address Line 2
                        </label>
                        <input type="text" id="address_line2" name="address_line2" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg form-input focus:outline-none"
                               placeholder="Apartment, suite, unit, building, floor, etc.">
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="town_city" class="block text-sm font-medium text-gray-700 mb-2 required">
                                Town/City
                            </label>
                            <input type="text" id="town_city" name="town_city" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg form-input focus:outline-none"
                                   placeholder="City or town">
                            <span id="townCityError" class="error-message"></span>
                        </div>
                        
                        <div>
                            <label for="country" class="block text-sm font-medium text-gray-700 mb-2 required">
                                Country
                            </label>
                            <select id="country" name="country" required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg form-select focus:outline-none appearance-none">
                                <option value="">Select Country</option>
                                <option value="United Kingdom" selected>United Kingdom</option>
                                <option value="Ireland">Ireland</option>
                                <option value="France">France</option>
                                <option value="Germany">Germany</option>
                                <option value="Spain">Spain</option>
                                <option value="Italy">Italy</option>
                                <option value="Netherlands">Netherlands</option>
                                <option value="Belgium">Belgium</option>
                                <option value="Other">Other</option>
                            </select>
                            <span id="countryError" class="error-message"></span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="county" class="block text-sm font-medium text-gray-700 mb-2">
                                County
                            </label>
                            <input type="text" id="county" name="county" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg form-input focus:outline-none"
                                   placeholder="County or region">
                        </div>
                        
                        <div>
                            <label for="postcode" class="block text-sm font-medium text-gray-700 mb-2 required">
                                Postcode
                            </label>
                            <input type="text" id="postcode" name="postcode" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg form-input focus:outline-none"
                                   placeholder="AB12 3CD">
                            <span id="postcodeError" class="error-message"></span>
                        </div>
                    </div>
                    
                    <!-- Terms and Conditions -->
                    <div class="mb-8 p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-start mb-4">
                            <input type="checkbox" id="terms" name="terms" required 
                                   class="h-4 w-4 text-[#022658] focus:ring-[#022658] border-gray-300 rounded mt-1">
                            <label for="terms" class="ml-3 text-sm text-gray-700">
                                I agree to the <a href="/terms" class="text-[#022658] hover:underline">Terms and Conditions</a> 
                                and <a href="/privacy" class="text-[#022658] hover:underline">Privacy Policy</a>. 
                                I understand that my data will be processed in accordance with these documents.
                            </label>
                        </div>
                        <span id="termsError" class="error-message"></span>
                        
                        <div class="flex items-start">
                            <input type="checkbox" id="marketing" name="marketing" 
                                   class="h-4 w-4 text-[#022658] focus:ring-[#022658] border-gray-300 rounded mt-1">
                            <label for="marketing" class="ml-3 text-sm text-gray-700">
                                I would like to receive marketing communications about products, services, 
                                and special offers from Commercial Pool Equipment and Supplies.
                            </label>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="flex justify-between">
                        <button type="button" id="prevStep3" 
                                class="text-gray-700 font-semibold py-3 px-8 rounded-lg border border-gray-300 hover:bg-gray-50 transition duration-300">
                            <i class="fas fa-arrow-left mr-2"></i> Back
                        </button>
                        
                        <button type="submit" id="submitBtn" 
                                class="btn-primary text-white font-semibold py-3 px-8 rounded-lg hover:bg-[#011a3d] transition duration-300">
                            Create Account
                        </button>
                    </div>
                </div>
            </form>
            
            <!-- Already have account -->
            <div class="mt-8 text-center">
                <p class="text-gray-600">
                    Already have an account? 
                    <a href="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>" class="text-[#022658] font-semibold hover:underline">
                        Sign in here
                    </a>
                </p>
            </div>
        </div>
    </div>

    <?php include('include/footer.php') ?>
    <?php include('include/script.php') ?>

    <script src="assets/js/register.js"></script>
</body>
</html>