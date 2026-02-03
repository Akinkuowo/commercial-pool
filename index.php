<?php
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.gc_maxlifetime', 3600);
    
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Fetch new products from database
    require_once 'config.php';
    
    $newProducts = [];
    $popularProducts = [];
    
    try {
        $conn = getDbConnection();
        
        // Fetch new products (limit 6)
        $newSql = "SELECT 
                    id,
                    product_name as name,
                    sku_number as sku,
                    price,
                    brand_name as brand,
                    stock_status as stock,
                    quantity,
                    product_description as description,
                    image,
                    category
                FROM products 
                WHERE is_new_product = 1
                ORDER BY id DESC
                LIMIT 6";
        
        $newResult = $conn->query($newSql);
        if ($newResult) {
            while ($row = $newResult->fetch_assoc()) {
                $image = $row['image'];
                
                $row['image'] = $image;
                $newProducts[] = $row;
            }
        }
        
        // Fetch popular products (limit 6)
        $popularSql = "SELECT 
                        id,
                        product_name as name,
                        sku_number as sku,
                        price,
                        brand_name as brand,
                        stock_status as stock,
                        quantity,
                        product_description as description,
                        image,
                        category
                    FROM products 
                    WHERE is_popular_product = 1
                    ORDER BY id DESC
                    LIMIT 6";
        
        $popularResult = $conn->query($popularSql);
        if ($popularResult) {
            while ($row = $popularResult->fetch_assoc()) {
                $image = $row['image'];
                
                $row['image'] = $image;
                $popularProducts[] = $row;
            }
        }
        
        // Connection will be closed later in the footer or by PHP at script end
    } catch (Exception $e) {
        error_log("Error fetching products: " . $e->getMessage());
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commercial Pool Equipment</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Adobe Fonts - Myriad Pro -->
    <link rel="stylesheet" href="https://use.typekit.net/yzr5vmg.css">
    <!-- Font Awesome for hamburger icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="assets/css/styles.css" rel="stylesheet" />

    <?php include('include/style.php') ?>
</head>
<body class="font-sans">
    <?php include('include/header.php') ?>

    <!-- Hero Banner  -->
    <section class="carousel-container">
        <!-- Single Hero Slide -->
        <div class="carousel-slide active">
            <img src="assets/img/dolphinpoolcleaner__19107.jpg" alt="Outdoor Leisure Equipment" onerror="this.src='https://images.unsplash.com/photo-1504280390367-361c6d9f38f4?w=1920&h=600&fit=crop'">
            <div class="carousel-overlay">
                <div class="text-center px-4">
                    <h1 class="hero-title text-white  md:text-4xl lg:text-5xl font-bold mb-6 leading-tight drop-shadow-lg">
                        Welcome to <br />Commercial Pool Equipment
                    </h1>
                    <a href="product.php" class="shop-now-btn">Shop Now</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Shop by Category Section -->
    <section class="py-16 md:py-24 bg-gray-50">
        <div class="container mx-auto px-4 max-w-7xl">
            <!-- Section Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-12">
                <div class="mb-6 md:mb-0">
                    <span class="text-[#022658] font-bold text-sm uppercase tracking-wider">Explore Collections</span>
                    <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mt-2">
                        Shop by Category
                    </h2>
                </div>
                <a href="product.php" class="text-[#022658] font-semibold flex items-center gap-2 hover:gap-3 transition-all">
                    View All Categories
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </a>
            </div>
            
            <!-- Category Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
                <!-- Pumps & Filters -->
                <a href="product.php?category=pumps-filters" class="group relative overflow-hidden rounded-2xl aspect-[4/5] shadow-lg hover:shadow-2xl transition-all duration-500">
                    <img src="assets/img/Category/pumps.png" alt="Pumps & Filters" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" onerror="this.src='https://images.unsplash.com/photo-1576013551627-0cc20b96c2a7?w=600&h=800&fit=crop'">
                    <div class="absolute inset-0 bg-gradient-to-t from-gray-900/90 via-gray-900/20 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 p-6 w-full">
                        <h3 class="text-xl font-bold text-white mb-2">Pumps & Filters</h3>
                        <span class="inline-flex items-center text-sm font-medium text-blue-400 group-hover:text-blue-300 transition-colors">
                            Browse Collection
                            <svg class="ml-2 w-4 h-4 transition-transform group-hover:translate-x-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </span>
                    </div>
                </a>

                <!-- Lights -->
                <a href="product.php?category=lights" class="group relative overflow-hidden rounded-2xl aspect-[4/5] shadow-lg hover:shadow-2xl transition-all duration-500">
                    <img src="assets/img/Category/lights.png" alt="Pool Lights" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" onerror="this.src='https://images.unsplash.com/photo-1544148103-0773bf10d330?w=600&h=800&fit=crop'">
                    <div class="absolute inset-0 bg-gradient-to-t from-gray-900/90 via-gray-900/20 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 p-6 w-full">
                        <h3 class="text-xl font-bold text-white mb-2">Pool Lights</h3>
                        <span class="inline-flex items-center text-sm font-medium text-blue-400 group-hover:text-blue-300 transition-colors">
                            Browse Collection
                            <svg class="ml-2 w-4 h-4 transition-transform group-hover:translate-x-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </span>
                    </div>
                </a>

                <!-- Cleaners -->
                <a href="product.php?category=cleaners" class="group relative overflow-hidden rounded-2xl aspect-[4/5] shadow-lg hover:shadow-2xl transition-all duration-500">
                    <img src="assets/img/Category/cleaners.png" alt="Pool Cleaners" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" onerror="this.src='https://images.unsplash.com/photo-1533038590840-1cde6e668a91?w=600&h=800&fit=crop'">
                    <div class="absolute inset-0 bg-gradient-to-t from-gray-900/90 via-gray-900/20 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 p-6 w-full">
                        <h3 class="text-xl font-bold text-white mb-2">Pool Cleaners</h3>
                        <span class="inline-flex items-center text-sm font-medium text-blue-400 group-hover:text-blue-300 transition-colors">
                            Browse Collection
                            <svg class="ml-2 w-4 h-4 transition-transform group-hover:translate-x-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </span>
                    </div>
                </a>

                <!-- Heaters -->
                <a href="product.php?category=heaters" class="group relative overflow-hidden rounded-2xl aspect-[4/5] shadow-lg hover:shadow-2xl transition-all duration-500">
                    <img src="assets/img/Category/heaters.png" alt="Heaters" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" onerror="this.src='https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=600&h=800&fit=crop'">
                    <div class="absolute inset-0 bg-gradient-to-t from-gray-900/90 via-gray-900/20 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 p-6 w-full">
                        <h3 class="text-xl font-bold text-white mb-2">Pool Heaters</h3>
                        <span class="inline-flex items-center text-sm font-medium text-blue-400 group-hover:text-blue-300 transition-colors">
                            Browse Collection
                            <svg class="ml-2 w-4 h-4 transition-transform group-hover:translate-x-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </span>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="py-16 px-4 bg-[#f8f9fa] relative overflow-hidden">
        <!-- Background pattern (optional) -->
        <div class="absolute inset-0 opacity-5">
            <div class="absolute top-0 left-0 w-64 h-64 bg-[#022658] rounded-full -translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-[#022658] rounded-full translate-x-1/2 translate-y-1/2"></div>
        </div>
        
        <div class="max-w-4xl mx-auto relative z-10">
            <div class="text-center mb-12 fade-in">
                <h2 class="text-4xl md:text-5xl font-bold  mb-4">
                    Stay Connected with Commerial Pool Equipment
                </h2>
                <p class="text-xl text-[#022658] max-w-2xl mx-auto">
                    Subscribe to our newsletter and be the first to know about new products, exclusive offers, and camping tips.
                </p>
            </div>
            
            <form id="newsletterForm" class="bg-white rounded-2xl shadow-2xl p-8 md:p-10 max-w-2xl mx-auto fade-in" style="animation-delay: 0.2s;">
                <div class="space-y-6">
                    <!-- Name Field -->
                    <div class="space-y-2">
                        <label for="name" class="block text-gray-800 font-medium">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </span>
                            <input 
                                type="text" 
                                id="name" 
                                name="name"
                                required
                                placeholder="Enter your full name"
                                class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200"
                            >
                        </div>
                    </div>
                    
                    <!-- Email Field -->
                    <div class="space-y-2">
                        <label for="email" class="block text-gray-800 font-medium">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                            </span>
                            <input 
                                type="email" 
                                id="email" 
                                name="email"
                                required
                                placeholder="your.email@example.com"
                                class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200"
                            >
                        </div>
                    </div>
                    
                    <!-- Consent Checkbox -->
                    <div class="space-y-3">
                        <label class="flex items-start space-x-3 cursor-pointer">
                            <input type="checkbox" id="consent" name="consent" class="custom-checkbox" required>
                            <span class="checkmark mt-1 flex-shrink-0"></span>
                            <span class="text-gray-700 text-sm leading-relaxed">
                                I consent to receiving marketing emails from Welcome to Commercial Pool Equipment and Supplies Limited. I understand that I can unsubscribe at any time by clicking the link in the footer of our emails. For information about our privacy practices, please visit our 
                                <a href="/privacy-policy" class="text-orange-600 font-medium hover:text-orange-700 underline">Privacy Policy</a>.
                                <span class="text-red-500"> *</span>
                            </span>
                        </label>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button 
                            type="submit"
                            id="subscribeBtn"
                            class="w-full bg-[#022658] hover:bg-white hover:text-black text-white font-bold py-4 px-8 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 focus:ring-4 focus:ring-orange-300 focus:ring-opacity-50"
                        >
                            <span class="flex items-center justify-center space-x-3">
                                <span class="text-lg">Subscribe Now</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-send">
                                    <line x1="22" y1="2" x2="11" y2="13"></line>
                                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                </svg>
                            </span>
                        </button>
                        <p class="text-center text-gray-600 text-sm mt-3">
                            Get 10% off your first order as a welcome gift!
                        </p>
                    </div>
                </div>
                
                <!-- Success Message (Hidden by default) -->
                <div id="successMessage" class="hidden mt-6 p-4 bg-[#022658]/10 border border-[#022658]/20 rounded-xl">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-[#022658]/20 rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#022658" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-[#022658]">Welcome to Commercial Pool Equipment and Supplies Limited!</h3>
                            <p class="text-[#022658] text-sm">
                                Thank you for subscribing! Check your email for a special welcome message and your 10% discount code.
                            </p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Footer Section -->
    <?php include('include/footer.php') ?>

    <?php include('include/script.php') ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add to cart buttons
            document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    addToCart(parseInt(btn.dataset.productId), btn);
                });
            });
        });
        

        
        async function addToCart(productId, buttonElement) {
            if (buttonElement) {
                buttonElement.disabled = true;
                const originalText = buttonElement.textContent;
                buttonElement.textContent = 'Adding...';
            }
            
            try {
                const response = await fetch('api/cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'add', product_id: productId, quantity: 1 })
                });
                const data = await response.json();
                if (data.success) {
                    showNotification(data.message || 'Added to cart', 'success');
                    updateCartCount(data.cart_count || data.total_items);
                } else {
                    showNotification(data.message || 'Failed to add to cart', 'error');
                }
            } catch (error) {
                console.error('Error adding to cart:', error);
                showNotification('An error occurred.', 'error');
            } finally {
                if (buttonElement) {
                    buttonElement.disabled = false;
                    buttonElement.textContent = 'Add to Cart';
                }
            }
        }
        
        function updateCartCount(count) {
            const cartBadge = document.querySelector('.cart-count, #cartCount');
            if (cartBadge) {
                cartBadge.textContent = count;
                cartBadge.classList.toggle('hidden', count <= 0);
            }
        }
        
        function showNotification(message, type = 'info') {
            const colors = { success: 'bg-[#022658]', error: 'bg-red-500', warning: 'bg-yellow-500', info: 'bg-blue-500' };
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300 translate-x-[400px]`;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => notification.classList.remove('translate-x-[400px]'), 10);
            setTimeout(() => {
                notification.classList.add('translate-x-[400px]');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>