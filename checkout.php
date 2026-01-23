<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php?redirect=' . urlencode('checkout.php'));
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Jacksons Leisure</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'custom-blue': '#0e703a',
                    }
                }
            }
        }
    </script>
    <link href="assets/css/styles.css" rel="stylesheet" />
    <?php include('include/style.php') ?>
    <style>
        /* Custom styles for color replacements */
        .bg-custom-blue { background-color: #0e703a; }
        .hover\:bg-custom-blue:hover { background-color: #0b5a2e; }
        .text-custom-blue { color: #0e703a; }
        .hover\:text-custom-blue:hover { color: #0b5a2e; }
        .border-custom-blue { border-color: #0e703a; }
        .focus\:ring-custom-blue:focus { --tw-ring-color: #0e703a; }
        .focus\:border-custom-blue:focus { border-color: #0e703a; }
    </style>
</head>
<body class="bg-gray-50">
    
    <?php include('include/header.php'); ?>

    <!-- Main Content Container -->
    <main class="min-h-screen bg-gray-50">
        
        <!-- Checkout Header -->
        <div class="bg-white border-b border-gray-200">
            <div class="container mx-auto px-4 max-w-7xl">
                <div class="py-6">
                    <!-- Breadcrumb -->
                    <nav class="flex items-center text-sm font-medium text-gray-500 mb-4">
                        <a href="/" class="text-gray-500 hover:text-gray-700">Home</a>
                        <i class="fas fa-chevron-right mx-2 text-xs text-gray-400"></i>
                        <a href="cart.php" class="text-gray-500 hover:text-gray-700">Shopping Cart</a>
                        <i class="fas fa-chevron-right mx-2 text-xs text-gray-400"></i>
                        <span class="text-gray-900 font-semibold">Checkout</span>
                    </nav>
                    
                    <!-- Page Title -->
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Checkout</h1>
                            <p class="text-gray-500 mt-1">Complete your purchase in just a few steps</p>
                        </div>
                        <div class="hidden md:block">
                            <div class="text-sm font-medium text-gray-700 bg-gray-100 px-4 py-2 rounded-lg">
                                <i class="fas fa-lock mr-2 text-orange-500"></i>
                                Secure Checkout
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Steps -->
        <div class="bg-white border-b border-gray-200">
            <div class="container mx-auto px-4 max-w-7xl">
                <div class="py-4">
                    <div class="flex items-center justify-center">
                        <!-- Progress Line -->
                        <div class="absolute w-4/5 max-w-2xl h-1 bg-gray-200 left-1/2 transform -translate-x-1/2 hidden md:block"></div>
                        
                        <!-- Steps -->
                        <div class="flex items-center justify-between w-full md:w-4/5 max-w-2xl relative z-10">
                            <!-- Step 1 -->
                            <div class="flex flex-col items-center">
                                <div id="step-indicator-1" class="w-10 h-10 rounded-full bg-custom-blue text-white flex items-center justify-center font-semibold mb-2 shadow-sm">
                                    <i class="fas fa-user"></i>
                                </div>
                                <span id="step-label-1" class="text-sm font-medium text-custom-blue">Information</span>
                            </div>
                            
                            <!-- Step 2 -->
                            <div class="flex flex-col items-center">
                                <div id="step-indicator-2" class="w-10 h-10 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center font-semibold mb-2 shadow-sm">
                                    <i class="fas fa-truck"></i>
                                </div>
                                <span id="step-label-2" class="text-sm font-medium text-gray-500">Delivery</span>
                            </div>
                            
                            <!-- Step 3 -->
                            <div class="flex flex-col items-center">
                                <div id="step-indicator-3" class="w-10 h-10 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center font-semibold mb-2 shadow-sm">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                                <span id="step-label-3" class="text-sm font-medium text-gray-500">Payment</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container mx-auto px-4 py-8 max-w-7xl">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Column - Checkout Form -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        
                        <!-- STEP 1: INFORMATION -->
                        <div id="step-information">
                            <!-- Form Header -->
                            <div class="border-b border-gray-200 px-8 pt-8 pb-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h2 class="text-xl font-bold text-gray-900">Contact & Shipping Information</h2>
                                        <p class="text-gray-500 text-sm mt-1">Enter your details to proceed with delivery</p>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Step 1 of 3
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Form Content -->
                            <form id="checkoutForm" class="space-y-6 px-8 py-8">
                                <!-- Contact Section -->
                                <div class="bg-custom-blue bg-opacity-10 border-l-4 border-custom-blue p-4 rounded-r-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-envelope text-custom-blue mr-3"></i>
                                        <div class="flex-1">
                                            <h3 class="font-medium text-gray-900">Contact Information</h3>
                                            <?php if ($isLoggedIn): ?>
                                                <p class="text-sm text-gray-700 mt-1">
                                                    Logged in as <span class="font-semibold"><?php echo htmlspecialchars($userEmail); ?></span>
                                                </p>
                                                <input type="hidden" name="email" value="<?php echo htmlspecialchars($userEmail); ?>">
                                            <?php else: ?>
                                                <div class="mt-2">
                                                    <input type="email" name="email" required 
                                                           placeholder="Enter your email address"
                                                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-custom-blue focus:border-custom-blue outline-none transition text-sm">
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Shipping Address -->
                                <div>
                                    <div class="flex items-center mb-6">
                                        <i class="fas fa-map-marker-alt text-gray-700 mr-3"></i>
                                        <h3 class="text-lg font-semibold text-gray-900">Shipping Address</h3>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <!-- Name Fields -->
                                        <div class="space-y-1">
                                            <label class="block text-sm font-medium text-gray-700">First Name</label>
                                            <input type="text" name="first_name" required 
                                                   value="<?php echo $isLoggedIn ? htmlspecialchars($userName) : ''; ?>"
                                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-custom-blue focus:border-custom-blue outline-none transition text-sm">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="block text-sm font-medium text-gray-700">Last Name</label>
                                            <input type="text" name="last_name" required 
                                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-custom-blue focus:border-custom-blue outline-none transition text-sm">
                                        </div>
                                        
                                        <!-- Country -->
                                        <div class="md:col-span-2 space-y-1">
                                            <label class="block text-sm font-medium text-gray-700">Country/Region</label>
                                            <select name="country" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-custom-blue focus:border-custom-blue outline-none transition text-sm bg-white">
                                                <option value="UK">United Kingdom</option>
                                            </select>
                                        </div>
                                        
                                        <!-- Address -->
                                        <div class="md:col-span-2 space-y-1">
                                            <label class="block text-sm font-medium text-gray-700">Street Address</label>
                                            <input type="text" name="address" required placeholder="House number and street name"
                                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-custom-blue focus:border-custom-blue outline-none transition text-sm mb-3">
                                            <input type="text" name="address_2" placeholder="Apartment, suite, unit, etc. (optional)"
                                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-custom-blue focus:border-custom-blue outline-none transition text-sm">
                                        </div>
                                        
                                        <!-- City & Postcode -->
                                        <div class="space-y-1">
                                            <label class="block text-sm font-medium text-gray-700">City</label>
                                            <input type="text" name="city" required 
                                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-custom-blue focus:border-custom-blue outline-none transition text-sm">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="block text-sm font-medium text-gray-700">Postcode</label>
                                            <input type="text" name="postcode" required 
                                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-custom-blue focus:border-custom-blue outline-none transition text-sm">
                                        </div>
                                        
                                        <!-- Phone -->
                                        <div class="md:col-span-2 space-y-1">
                                            <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                                            <input type="tel" name="phone" required 
                                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-custom-blue focus:border-custom-blue outline-none transition text-sm">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="pt-6 border-t border-gray-200">
                                    <div class="flex justify-end">
                                        <button type="button" onclick="goToStep('delivery')" 
                                                class="bg-custom-blue hover:bg-custom-blue hover:opacity-90 text-white font-semibold py-3.5 px-8 rounded-lg transition flex items-center shadow-sm hover:shadow">
                                            Continue to Delivery
                                            <i class="fas fa-arrow-right ml-2"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- STEP 2: DELIVERY -->
                        <div id="step-delivery" class="hidden">
                            <!-- Form Header -->
                            <div class="border-b border-gray-200 px-8 pt-8 pb-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h2 class="text-xl font-bold text-gray-900">Delivery Method</h2>
                                        <p class="text-gray-500 text-sm mt-1">Choose how you want to receive your order</p>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Step 2 of 3
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Form Content -->
                            <div class="px-8 py-8">
                                <!-- Review Info -->
                                <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 mb-8">
                                    <div class="flex items-center mb-4">
                                        <i class="fas fa-check-circle text-orange-500 mr-3"></i>
                                        <h3 class="font-medium text-gray-900">Review Information</h3>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-gray-500 mb-1">Contact</p>
                                            <p id="review-contact" class="font-medium text-gray-900">user@example.com</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500 mb-1">Shipping Address</p>
                                            <p id="review-address" class="font-medium text-gray-900">123 Street, City...</p>
                                        </div>
                                    </div>
                                    <button type="button" onclick="goToStep('information')" 
                                            class="mt-4 text-custom-blue hover:text-custom-blue hover:opacity-80 text-sm font-medium flex items-center">
                                        <i class="fas fa-edit mr-2"></i>
                                        Edit Information
                                    </button>
                                </div>
                                
                                <!-- Delivery Options -->
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Select Delivery Method</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Home Delivery -->
                                        <label class="relative cursor-pointer">
                                            <input type="radio" name="delivery_method" value="delivery" class="sr-only" checked onchange="updateShippingCost()">
                                            <div class="border-2 border-gray-200 hover:border-custom-blue rounded-xl p-5 transition-all duration-200 h-full">
                                                <div class="flex items-start justify-between">
                                                    <div class="flex items-center">
                                                        <div class="w-6 h-6 rounded-full border-2 border-gray-300 mr-3 flex items-center justify-center">
                                                            <div class="w-3 h-3 rounded-full bg-custom-blue hidden"></div>
                                                        </div>
                                                        <div>
                                                            <h4 class="font-semibold text-gray-900">Home Delivery</h4>
                                                            <p class="text-sm text-gray-500 mt-1">2-3 Business Days</p>
                                                        </div>
                                                    </div>
                                                    <span class="font-bold text-gray-900">£42.00</span>
                                                </div>
                                                <div class="mt-4 flex items-center text-sm text-gray-600">
                                                    <i class="fas fa-home mr-2"></i>
                                                    <span>Delivered to your doorstep</span>
                                                </div>
                                            </div>
                                        </label>
                                        
                                        <!-- Click & Collect -->
                                        <label class="relative cursor-pointer">
                                            <input type="radio" name="delivery_method" value="collection" class="sr-only" onchange="updateShippingCost()">
                                            <div class="border-2 border-gray-200 hover:border-custom-blue rounded-xl p-5 transition-all duration-200 h-full">
                                                <div class="flex items-start justify-between">
                                                    <div class="flex items-center">
                                                        <div class="w-6 h-6 rounded-full border-2 border-gray-300 mr-3 flex items-center justify-center">
                                                            <div class="w-3 h-3 rounded-full bg-custom-blue hidden"></div>
                                                        </div>
                                                        <div>
                                                            <h4 class="font-semibold text-gray-900">Click & Collect</h4>
                                                            <p class="text-sm text-gray-500 mt-1">Ready in 1 hour</p>
                                                        </div>
                                                    </div>
                                                    <span class="font-bold text-orange-500">Free</span>
                                                </div>
                                                <div class="mt-4 flex items-center text-sm text-gray-600">
                                                    <i class="fas fa-store mr-2"></i>
                                                    <span>Pick up from our store</span>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="pt-8 border-t border-gray-200">
                                    <div class="flex justify-between">
                                        <button type="button" onclick="goToStep('information')" 
                                                class="text-gray-700 hover:text-gray-900 font-medium py-3.5 px-6 rounded-lg border border-gray-300 hover:border-gray-400 transition flex items-center">
                                            <i class="fas fa-arrow-left mr-2"></i>
                                            Back to Information
                                        </button>
                                        <button type="button" onclick="goToStep('payment')" 
                                                class="bg-custom-blue hover:bg-custom-blue hover:opacity-90 text-white font-semibold py-3.5 px-8 rounded-lg transition flex items-center shadow-sm hover:shadow">
                                            Continue to Payment
                                            <i class="fas fa-arrow-right ml-2"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- STEP 3: PAYMENT -->
                        <div id="step-payment" class="hidden">
                            <!-- Form Header -->
                            <div class="border-b border-gray-200 px-8 pt-8 pb-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h2 class="text-xl font-bold text-gray-900">Payment Method</h2>
                                        <p class="text-gray-500 text-sm mt-1">Complete your order securely</p>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Step 3 of 3
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Form Content -->
                            <div class="px-8 py-8">
                                <!-- Review Summary -->
                                <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 mb-8">
                                    <div class="flex items-center mb-4">
                                        <i class="fas fa-check-circle text-orange-500 mr-3"></i>
                                        <h3 class="font-medium text-gray-900">Order Summary</h3>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <p class="text-sm text-gray-500">Contact</p>
                                                <p id="review-contact-2" class="font-medium text-gray-900"></p>
                                            </div>
                                            <button type="button" onclick="goToStep('information')" 
                                                    class="text-custom-blue hover:text-custom-blue hover:opacity-80 text-sm font-medium">
                                                Change
                                            </button>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <p class="text-sm text-gray-500">Shipping to</p>
                                                <p id="review-address-2" class="font-medium text-gray-900"></p>
                                            </div>
                                            <button type="button" onclick="goToStep('information')" 
                                                    class="text-custom-blue hover:text-custom-blue hover:opacity-80 text-sm font-medium">
                                                Change
                                            </button>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <p class="text-sm text-gray-500">Delivery Method</p>
                                                <p id="review-method" class="font-medium text-gray-900">Home Delivery</p>
                                            </div>
                                            <button type="button" onclick="goToStep('delivery')" 
                                                    class="text-custom-blue hover:text-custom-blue hover:opacity-80 text-sm font-medium">
                                                Change
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Payment Options -->
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Select Payment Method</h3>
                                    <p class="text-sm text-gray-500 mb-6 flex items-center">
                                        <i class="fas fa-lock mr-2 text-orange-500"></i>
                                        All transactions are secure and encrypted
                                    </p>
                                    
                                    <div class="space-y-3">
                                        <!-- Apple Pay -->
                                        <label class="relative cursor-pointer">
                                            <input type="radio" name="payment_method" value="apple_pay" class="sr-only" required>
                                            <div class="border-2 border-gray-200 hover:border-custom-blue rounded-xl p-5 transition-all duration-200">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center">
                                                        <div class="w-6 h-6 rounded-full border-2 border-gray-300 mr-4 flex items-center justify-center">
                                                            <div class="w-3 h-3 rounded-full bg-custom-blue hidden"></div>
                                                        </div>
                                                        <div>
                                                            <h4 class="font-semibold text-gray-900">Apple Pay</h4>
                                                            <p class="text-sm text-gray-500 mt-1">Pay securely with Apple Pay</p>
                                                        </div>
                                                    </div>
                                                    <i class="fab fa-apple text-2xl text-gray-900"></i>
                                                </div>
                                            </div>
                                        </label>
                                        
                                        <!-- PayPal -->
                                        <label class="relative cursor-pointer">
                                            <input type="radio" name="payment_method" value="paypal" class="sr-only">
                                            <div class="border-2 border-gray-200 hover:border-custom-blue rounded-xl p-5 transition-all duration-200">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center">
                                                        <div class="w-6 h-6 rounded-full border-2 border-gray-300 mr-4 flex items-center justify-center">
                                                            <div class="w-3 h-3 rounded-full bg-custom-blue hidden"></div>
                                                        </div>
                                                        <div>
                                                            <h4 class="font-semibold text-gray-900">PayPal</h4>
                                                            <p class="text-sm text-gray-500 mt-1">Pay with your PayPal account</p>
                                                        </div>
                                                    </div>
                                                    <i class="fab fa-paypal text-2xl text-blue-700"></i>
                                                </div>
                                            </div>
                                        </label>
                                        
                                        <!-- Credit/Debit Card -->
                                        <label class="relative cursor-pointer">
                                            <input type="radio" name="payment_method" value="worldpay" class="sr-only">
                                            <div class="border-2 border-gray-200 hover:border-custom-blue rounded-xl p-5 transition-all duration-200">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center">
                                                        <div class="w-6 h-6 rounded-full border-2 border-gray-300 mr-4 flex items-center justify-center">
                                                            <div class="w-3 h-3 rounded-full bg-custom-blue hidden"></div>
                                                        </div>
                                                        <div>
                                                            <h4 class="font-semibold text-gray-900">Credit/Debit Card</h4>
                                                            <p class="text-sm text-gray-500 mt-1">Pay with Visa, Mastercard, etc.</p>
                                                        </div>
                                                    </div>
                                                    <div class="flex gap-2">
                                                        <i class="fab fa-cc-visa text-2xl text-blue-800"></i>
                                                        <i class="fab fa-cc-mastercard text-2xl text-red-600"></i>
                                                        <i class="fab fa-cc-amex text-2xl text-blue-600"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                        
                                        <!-- Google Pay -->
                                        <label class="relative cursor-pointer">
                                            <input type="radio" name="payment_method" value="google_pay" class="sr-only">
                                            <div class="border-2 border-gray-200 hover:border-custom-blue rounded-xl p-5 transition-all duration-200">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center">
                                                        <div class="w-6 h-6 rounded-full border-2 border-gray-300 mr-4 flex items-center justify-center">
                                                            <div class="w-3 h-3 rounded-full bg-custom-blue hidden"></div>
                                                        </div>
                                                        <div>
                                                            <h4 class="font-semibold text-gray-900">Google Pay</h4>
                                                            <p class="text-sm text-gray-500 mt-1">Pay securely with Google Pay</p>
                                                        </div>
                                                    </div>
                                                    <i class="fab fa-google-pay text-2xl text-gray-700"></i>
                                                </div>
                                            </div>
                                        </label>
                                        
                                        <!-- Cash -->
                                        <label class="relative cursor-pointer">
                                            <input type="radio" name="payment_method" value="cash" class="sr-only">
                                            <div class="border-2 border-gray-200 hover:border-custom-blue rounded-xl p-5 transition-all duration-200">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center">
                                                        <div class="w-6 h-6 rounded-full border-2 border-gray-300 mr-4 flex items-center justify-center">
                                                            <div class="w-3 h-3 rounded-full bg-custom-blue hidden"></div>
                                                        </div>
                                                        <div>
                                                            <h4 class="font-semibold text-gray-900">Cash on Collection</h4>
                                                            <p class="text-sm text-gray-500 mt-1">Pay when you pick up</p>
                                                        </div>
                                                    </div>
                                                    <i class="fas fa-money-bill-wave text-2xl text-orange-500"></i>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="pt-8 border-t border-gray-200">
                                    <div class="flex justify-between">
                                        <button type="button" onclick="goToStep('delivery')" 
                                                class="text-gray-700 hover:text-gray-900 font-medium py-3.5 px-6 rounded-lg border border-gray-300 hover:border-gray-400 transition flex items-center">
                                            <i class="fas fa-arrow-left mr-2"></i>
                                            Back to Delivery
                                        </button>
                                        <button type="button" id="placeOrderBtnMain" onclick="placeOrder()" 
                                                class="bg-custom-blue hover:bg-custom-blue hover:opacity-90 text-white font-bold py-3.5 px-10 rounded-lg shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5 flex items-center">
                                            <i class="fas fa-lock mr-2"></i>
                                            Place Order
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column - Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 sticky top-8">
                        <!-- Summary Header -->
                        <div class="border-b border-gray-200 p-6">
                            <h2 class="text-xl font-bold text-gray-900">Order Summary</h2>
                            <p class="text-gray-500 text-sm mt-1">Review your items and total</p>
                        </div>
                        
                        <!-- Loading -->
                        <div id="cartLoading" class="text-center py-8">
                            <i class="fas fa-spinner fa-spin text-gray-400 text-xl mb-3"></i>
                            <p class="text-gray-500">Loading cart items...</p>
                        </div>
                        
                        <!-- Cart Content -->
                        <div id="cartSummaryContent" class="hidden">
                            <!-- Items List -->
                            <div id="orderItems" class="p-6 max-h-96 overflow-y-auto">
                                <!-- Items injected via JS -->
                            </div>
                            
                            <!-- Discount Code -->
                            <div class="border-t border-gray-200 p-6">
                                <div class="flex gap-2">
                                    <input type="text" placeholder="Gift card or discount code" 
                                           class="flex-1 px-4 py-3 border border-gray-300 rounded-lg text-sm outline-none focus:border-custom-blue">
                                    <button class="bg-gray-100 text-gray-700 px-5 py-3 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                                        Apply
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Totals -->
                            <div class="border-t border-gray-200 p-6 space-y-4">
                                <div class="flex justify-between text-gray-600">
                                    <span>Subtotal</span>
                                    <span id="summarySubtotal" class="font-medium">£0.00</span>
                                </div>
                                <div class="flex justify-between text-gray-600">
                                    <span>Shipping</span>
                                    <span id="summaryShipping" class="font-medium">£42.00</span>
                                </div>
                                <div class="border-t border-gray-200 pt-4">
                                    <div class="flex justify-between text-lg font-bold text-gray-900">
                                        <span>Total</span>
                                        <span id="summaryTotal">£0.00</span>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-2">Including VAT where applicable</p>
                                </div>
                            </div>
                            
                            <!-- Security Badge -->
                            <div class="border-t border-gray-200 p-6 bg-gray-50">
                                <div class="flex items-center justify-center space-x-6">
                                    <div class="text-center">
                                        <i class="fas fa-shield-alt text-orange-500 text-xl mb-2"></i>
                                        <p class="text-xs text-gray-600">Secure Payment</p>
                                    </div>
                                    <div class="text-center">
                                        <i class="fas fa-lock text-orange-500 text-xl mb-2"></i>
                                        <p class="text-xs text-gray-600">SSL Encrypted</p>
                                    </div>
                                    <div class="text-center">
                                        <i class="fas fa-undo text-custom-blue text-xl mb-2"></i>
                                        <p class="text-xs text-gray-600">Easy Returns</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include('include/footer.php'); ?>
    <?php include('include/script.php') ?>
    <script>
        // Load cart summary on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadCartSummary();
            updateShippingCost();
            goToStep('information'); // Start at the first step
        });
        
        // Multi-step Logic
        function goToStep(stepName) {
            // Validate before moving forward
            if (stepName === 'delivery') {
                const infoStep = document.getElementById('step-information');
                // Basic HTML5 validation
                const inputs = infoStep.querySelectorAll('input[required], select[required]');
                let valid = true;
                inputs.forEach(input => {
                    if (!input.checkValidity()) {
                        input.reportValidity();
                        valid = false;
                    }
                });
                if (!valid) return;
                
                // Update Review Info
                const formData = new FormData(document.getElementById('checkoutForm'));
                const email = formData.get('email') || document.querySelector('input[name="email"][type="hidden"]')?.value || '';
                const address = `${formData.get('address')}, ${formData.get('city')}, ${formData.get('postcode')}`;
                
                document.getElementById('review-contact').textContent = email;
                document.getElementById('review-address').textContent = address;
                document.getElementById('review-contact-2').textContent = email;
                document.getElementById('review-address-2').textContent = address;
            }
            
            // Hide all steps
            document.getElementById('step-information').classList.add('hidden');
            document.getElementById('step-delivery').classList.add('hidden');
            document.getElementById('step-payment').classList.add('hidden');
            
            // Show target step
            document.getElementById(`step-${stepName}`).classList.remove('hidden');
            
            // Update progress indicators
            const steps = ['information', 'delivery', 'payment'];
            const currentIndex = steps.indexOf(stepName);
            
            steps.forEach((step, index) => {
                const indicator = document.getElementById(`step-indicator-${index + 1}`);
                const label = document.getElementById(`step-label-${index + 1}`);
                
                if (index < currentIndex) {
                    // Completed steps
                    indicator.className = 'w-10 h-10 rounded-full bg-orange-500 text-white flex items-center justify-center font-semibold mb-2 shadow-sm';
                    indicator.innerHTML = '<i class="fas fa-check"></i>';
                    label.className = 'text-sm font-medium text-orange-500';
                } else if (index === currentIndex) {
                    // Current step
                    indicator.className = 'w-10 h-10 rounded-full bg-custom-blue text-white flex items-center justify-center font-semibold mb-2 shadow-sm';
                    label.className = 'text-sm font-medium text-custom-blue font-bold';
                } else {
                    // Future steps
                    indicator.className = 'w-10 h-10 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center font-semibold mb-2 shadow-sm';
                    label.className = 'text-sm font-medium text-gray-500';
                }
            });
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        
        function updateShippingCost() {
            const method = document.querySelector('input[name="delivery_method"]:checked')?.value || 'delivery';
            const costElement = document.getElementById('summaryShipping');
            
            let cost = 0;
            if (method === 'delivery') {
                cost = 42.00;
                document.getElementById('review-method').textContent = 'Home Delivery';
            } else {
                cost = 0;
                document.getElementById('review-method').textContent = 'Click & Collect';
            }
            
            costElement.textContent = cost === 0 ? 'Free' : `£${cost.toFixed(2)}`;
            
            // Update radio button visual states
            document.querySelectorAll('input[name="delivery_method"]').forEach(radio => {
                const container = radio.closest('label');
                const checkmark = container.querySelector('.w-3.h-3.rounded-full');
                if (radio.checked) {
                    container.querySelector('.border-2').classList.add('border-custom-blue');
                    checkmark?.classList.remove('hidden');
                } else {
                    container.querySelector('.border-2').classList.remove('border-custom-blue');
                    checkmark?.classList.add('hidden');
                }
            });
            
            // Recalculate total
            const subtext = document.getElementById('summarySubtotal').textContent.replace('£', '');
            const subtotal = parseFloat(subtext) || 0;
            const total = subtotal + cost;
            document.getElementById('summaryTotal').textContent = '£' + total.toFixed(2);
        }
        
        // Update payment method visual states
        document.addEventListener('change', function(e) {
            if (e.target.name === 'payment_method') {
                document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
                    const container = radio.closest('label');
                    const checkmark = container.querySelector('.w-3.h-3.rounded-full');
                    if (radio.checked) {
                        container.querySelector('.border-2').classList.add('border-custom-blue');
                        checkmark?.classList.remove('hidden');
                    } else {
                        container.querySelector('.border-2').classList.remove('border-custom-blue');
                        checkmark?.classList.add('hidden');
                    }
                });
            }
        });

        async function loadCartSummary() {
            try {
                const response = await fetch('api/cart.php');
                const result = await response.json();

                const loading = document.getElementById('cartLoading');
                const content = document.getElementById('cartSummaryContent');
                const itemsContainer = document.getElementById('orderItems');

                if (result.success && result.cart && result.cart.length > 0) {
                    loading.classList.add('hidden');
                    content.classList.remove('hidden');
                    
                    let html = '';
                    let subtotal = 0;

                    result.cart.forEach(item => {
                        const itemTotal = item.price * item.quantity;
                        subtotal += itemTotal;
                        html += `
                            <div class="flex items-center gap-4 mb-4 pb-4 border-b border-gray-100 last:border-0">
                                <div class="relative flex-shrink-0">
                                    <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden border border-gray-200">
                                        <img src="${item.image}" alt="${item.name}" 
                                             class="w-full h-full object-cover" onerror="this.src='assets/img/placeholder.jpg'">
                                    </div>
                                    <span class="absolute -top-2 -right-2 bg-custom-blue text-white text-xs font-bold w-6 h-6 flex items-center justify-center rounded-full shadow">
                                        ${item.quantity}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium text-gray-900 truncate">${item.name}</h4>
                                    <p class="text-xs text-gray-500 mt-1">£${parseFloat(item.price).toFixed(2)} each</p>
                                </div>
                                <div class="text-sm font-bold text-gray-900">
                                    £${itemTotal.toFixed(2)}
                                </div>
                            </div>
                        `;
                    });

                    itemsContainer.innerHTML = html;
                    document.getElementById('summarySubtotal').textContent = `£${subtotal.toFixed(2)}`;
                    
                    // Initial shipping calc
                    updateShippingCost();

                } else {
                    window.location.href = 'index.php'; // Redirect if empty
                }
            } catch (error) {
                console.error('Error loading cart:', error);
            }
        }

        async function placeOrder() {
            const form = document.getElementById('checkoutForm');
            const btn = document.getElementById('placeOrderBtnMain');
            
            // Validate Payment Step
            const payment = document.querySelector('input[name="payment_method"]:checked');
            if (!payment) {
                alert('Please select a payment method');
                return;
            }

            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
            btn.disabled = true;

            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch('api/checkout_process.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const text = await response.text();
                let result;
                try {
                    result = JSON.parse(text);
                } catch (e) {
                    console.error('Invalid JSON response:', text);
                    alert('Server Error: ' + text.substring(0, 200)); // Show first 200 chars of error
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    return;
                }

                if (result.success) {
                    if (result.redirect_url) {
                        window.location.href = result.redirect_url;
                    } else {
                        window.location.href = `order_success.php?order_id=${result.order_id}&code=${result.collection_code || ''}&qr=${encodeURIComponent(result.qr_url || '')}`;
                    }
                } else {
                    alert(result.message || 'Failed to place order.');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            } catch (error) {
                console.error('Error placing order:', error);
                alert('An error occurred. Please try again.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>