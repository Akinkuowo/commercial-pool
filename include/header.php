<?php

$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$userName = $isLoggedIn ? ($_SESSION['user_name'] ?? 'User') : '';
$userEmail = $isLoggedIn ? ($_SESSION['user_email'] ?? '') : '';

// Get user_id from session
$userId = $_SESSION['user_id'] ?? null;

// Initialize counts
$cartCount = 0;
$favoriteCount = 0;

// Fetch cart and favorite counts
if ($userId) {
    require_once 'config.php';
    $conn = getDbConnection();
    
    if ($conn) {
        // Get cart count - sum of all quantities
        $cartSql = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
        $cartStmt = $conn->prepare($cartSql);
        if ($cartStmt) {
            $cartStmt->bind_param("s", $userId);
            $cartStmt->execute();
            $cartResult = $cartStmt->get_result();
            if ($cartRow = $cartResult->fetch_assoc()) {
                $cartCount = (int)($cartRow['total'] ?? 0);
            }
            $cartStmt->close();
        }
        
        // Get favorites count
        $favSql = "SELECT COUNT(*) as total FROM wishlist WHERE user_id = ?";
        $favStmt = $conn->prepare($favSql);
        if ($favStmt) {
            $favStmt->bind_param("s", $userId);
            $favStmt->execute();
            $favResult = $favStmt->get_result();
            if ($favRow = $favResult->fetch_assoc()) {
                $favoriteCount = (int)($favRow['total'] ?? 0);
            }
            $favStmt->close();
        }
        
        closeDbConnection($conn);
    }
} else {
    // Get favorites count from session for guests
    if (isset($_SESSION['wishlist']) && is_array($_SESSION['wishlist'])) {
        $favoriteCount = count($_SESSION['wishlist']);
    }
}
?>

<header>
        <!-- announcement bar -->
        <?php include ('include/announcement-bar.php'); ?>

         <!-- Main navigation bar with logo, search, and cart -->
        <div class="py-3 bg-white">
            <div class="container mx-auto px-4 max-w-7xl">
                <div class="flex flex-col md:flex-row gap-4 md:gap-3 items-center">
                    <!-- Mobile menu toggle and logo -->
                    <div class="flex items-center justify-between w-full md:w-auto md:flex-grow md:basis-0">
                        <!-- Mobile menu toggle (visible on small screens) -->
                        <button id="mobileMenuToggle" class="md:hidden touch-target px-3 py-2">
                            <i class="fas fa-bars text-gray-800 text-xl"></i>
                        </button>
                        
                        <!-- Logo -->
                        <div class="md:flex-grow md:basis-0">
                            <figure class="m-0 flex p-2 justify-center md:justify-start">
                                <a href="/commercial pool" class="no-underline" aria-label="Commercial Pool Equipment and Supplies Limited">
                                    <picture class="mx-auto block">
                                        <img src="assets/img/logo.webp" alt="Commercial Pool Equipment and Supplies Limited" class="w-[100px] md:w-[180px]" onerror="this.style.display='none'; this.parentElement.innerHTML='<div class=\'text-xl md:text-2xl font-bold text-gray-800\'>Commercial Pool Equipment</div>'">
                                    </picture>
                                </a>
                            </figure>
                        </div>
                        
                        <!-- Mobile cart and icons -->
                        <div class="flex items-center md:hidden">
                            <a href="cart.php" class="p-2 no-underline text-sm font-normal text-gray-800 inline-flex items-center relative gap-1 touch-target">
                                <span class="sr-only">Cart</span>
                                <span class="pointer-events-none text-sm">0</span>
                                <span class="pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="9" cy="21" r="1"></circle>
                                        <circle cx="20" cy="21" r="1"></circle>
                                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                    </svg>
                                </span>
                            </a>
                        </div>
                    </div>
                    
                     <!-- Search Bar -->
                    <div class="w-full md:flex-grow md:basis-0 order-3 md:order-none">
                        <div class="flex items-center h-full my-auto justify-center md:justify-end">
                            <div class="w-full max-w-lg relative">
                                <div class="relative">
                                    <span class="absolute top-0 left-0 px-3 flex items-center h-full pointer-events-none z-10">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                                            <circle cx="11" cy="11" r="8"></circle>
                                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                        </svg>
                                    </span>
                                    <button type="button" id="clearSearchBtn" class="absolute top-0 right-0 h-full px-3 hidden z-10 hover:text-gray-700" aria-label="Clear search">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                        </svg>
                                    </button>
                                    <input type="text" id="headerSearchInput" class="w-full rounded-full py-3 md:py-2 pl-11 pr-10 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm text-gray-600" placeholder="Search the store" autocomplete="off">
                                    
                                    <!-- Search Suggestions Dropdown -->
                                    <div id="searchSuggestions" class="hidden absolute top-full left-0 right-0 mt-2 bg-white rounded-lg shadow-lg border border-gray-200 max-h-96 overflow-y-auto z-50">
                                        <!-- Loading state -->
                                        <div id="searchLoading" class="hidden p-4 text-center">
                                            <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-[#022658]"></div>
                                            <p class="text-sm text-gray-600 mt-2">Searching...</p>
                                        </div>
                                        
                                        <!-- Auto-correct suggestion -->
                                        <div id="autoCorrectSuggestion" class="hidden px-4 py-3 bg-blue-50 border-b border-blue-100">
                                            <p class="text-sm text-gray-700">
                                                Did you mean: <button type="button" id="correctedTerm" class="font-semibold text-blue-600 hover:text-blue-700 underline"></button>?
                                            </p>
                                        </div>
                                        
                                        <!-- Suggestions list -->
                                        <div id="suggestionsList" class="py-2">
                                            <!-- Populated by JavaScript -->
                                        </div>
                                        
                                        <!-- No results -->
                                        <div id="noResults" class="hidden p-4 text-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                            <p class="text-sm text-gray-600 mt-2">No results found</p>
                                        </div>
                                        
                                        <!-- View all results -->
                                        <div id="viewAllResults" class="hidden border-t border-gray-200 p-3">
                                            <a href="#" id="viewAllLink" class="block text-center text-sm text-[#022658] hover:text-[#011a3d] font-medium">
                                                View all results →
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Navigation Icons (Desktop) -->
                    <div class="hidden md:flex md:flex-grow md:basis-0 justify-end">
                        <nav class="flex py-0 flex-row justify-end text-right">
                            <ul class="flex flex-nowrap flex-row gap-1 items-center">
                                <!-- User Account / Profile -->
                                <li class="nav-item relative">
                                    <?php if ($isLoggedIn): ?>
                                        <!-- Logged In User - Profile Dropdown with FILLED icon -->
                                        <div class="relative group">
                                            <button class="p-2 no-underline bg-gray-300 rounded-full text-sm font-normal text-gray-800 inline-flex items-center touch-target" title="My Account">
                                                <span class="sr-only">My Account</span>
                                                <span class="pointer-events-none">
                                                    <!-- FILLED ICON for logged in users -->
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                        <circle cx="12" cy="7" r="4"></circle>
                                                    </svg>
                                                </span>
                                            </button>
                                            
                                            <!-- Dropdown Menu -->
                                            <div class="hidden group-hover:block absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                                                <!-- User Info -->
                                                <div class="px-4 py-3 border-b border-gray-200">
                                                    <p class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($userName); ?></p>
                                                    <p class="text-xs text-gray-600 mt-1"><?php echo htmlspecialchars($userEmail); ?></p>
                                                </div>
                                                
                                                <!-- Menu Items -->
                                                <div class="py-2">
                                                    <a href="dashboard.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mr-3">
                                                            <rect x="3" y="3" width="7" height="7"></rect>
                                                            <rect x="14" y="3" width="7" height="7"></rect>
                                                            <rect x="14" y="14" width="7" height="7"></rect>
                                                            <rect x="3" y="14" width="7" height="7"></rect>
                                                        </svg>
                                                        Dashboard
                                                    </a>
                                                    <a href="dashboard.php#orders" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mr-3">
                                                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                                            <line x1="3" y1="6" x2="21" y2="6"></line>
                                                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                                                        </svg>
                                                        My Orders
                                                    </a>
                                                    <a href="dashboard.php#account" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mr-3">
                                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                            <circle cx="12" cy="7" r="4"></circle>
                                                        </svg>
                                                        My Profile
                                                    </a>
                                                </div>
                                                
                                                <!-- Logout -->
                                                <div class="border-t border-gray-200 py-2">
                                                    <a href="logout.php" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mr-3">
                                                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                                            <polyline points="16 17 21 12 16 7"></polyline>
                                                            <line x1="21" y1="12" x2="9" y2="12"></line>
                                                        </svg>
                                                        Logout
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <!-- Not Logged In - Login Link with OUTLINED icon -->
                                        <a href="login.php" class="p-2 no-underline hover:bg-gray-100 rounded-full text-sm font-normal text-gray-800 inline-flex items-center touch-target" title="Trade Login" target="_blank">
                                            <span class="sr-only">Trade Login</span>
                                            <span class="pointer-events-none">
                                                <!-- OUTLINED ICON for non-logged in users -->
                                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                    <circle cx="12" cy="7" r="4"></circle>
                                                </svg>
                                            </span>
                                        </a>
                                    <?php endif; ?>
                                </li>
                                
                                <li class="nav-item relative">
                                    <a href="wishlist.php" class="p-2 no-underline hover:bg-gray-100 rounded-full text-sm font-normal text-gray-800 inline-flex items-center touch-target" title="Wishlist">
                                        <span class="sr-only">Favorites</span>
                                        <span class="pointer-events-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                            </svg>
                                        </span>
                                        <?php if ($favoriteCount > 0): ?>
                                        <span class="wishlist-count-badge absolute -top-1 -right-1 text-xs font-bold leading-none bg-gray-800 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px]"><?php echo $favoriteCount; ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                                
                                <li class="nav-item">
                                    <a href="cart.php" class="p-2 no-underline hover:bg-gray-100 rounded-full text-sm font-normal text-gray-800 inline-flex items-center relative gap-1 touch-target" title="Shopping Cart">
                                        <span class="sr-only">Cart</span>
                                        <span class="pointer-events-none text-sm font-semibold cart-count" id="cartCount"><?php echo $cartCount; ?></span>
                                        <span class="pointer-events-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="9" cy="21" r="1"></circle>
                                                <circle cx="20" cy="21" r="1"></circle>
                                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                            </svg>
                                        </span>
                                    </a>
                                </li>
                        </nav>
                    </div>
                
                </div>
            </div>
        </div>

        <!-- Mobile Menu (Hidden by default) -->
        <div id="mobileMenu" class="mobile-menu md:hidden bg-white border-t border-gray-200 overflow-y-auto max-h-[calc(100vh-140px)]">
            <div class="container mx-auto px-4 max-w-7xl">
                <nav class="py-4">
                    <!-- Mobile user section -->
                    <?php if ($isLoggedIn): ?>
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <div class="px-4 py-3 bg-gray-50 rounded-lg">
                                <p class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($userName); ?></p>
                                <p class="text-xs text-gray-600 mt-1"><?php echo htmlspecialchars($userEmail); ?></p>
                            </div>
                            <div class="mt-3 space-y-1">
                                <a href="dashboard.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mr-3">
                                        <rect x="3" y="3" width="7" height="7"></rect>
                                        <rect x="14" y="3" width="7" height="7"></rect>
                                        <rect x="14" y="14" width="7" height="7"></rect>
                                        <rect x="3" y="14" width="7" height="7"></rect>
                                    </svg>
                                    Dashboard
                                </a>
                                <a href="dashboard.php#orders" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mr-3">
                                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                        <line x1="3" y1="6" x2="21" y2="6"></line>
                                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                                    </svg>
                                    My Orders
                                </a>
                                <a href="dashboard.php#account" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mr-3">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    My Profile
                                </a>
                                <a href="logout.php" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mr-3">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                        <polyline points="16 17 21 12 16 7"></polyline>
                                        <line x1="21" y1="12" x2="9" y2="12"></line>
                                    </svg>
                                    Logout
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                
                    <ul class="space-y-1 mobile-menu-nav">
                        <!-- Shop All -->
                        <li class="mobile-menu-item group">
                            <a href="product.php" class="mobile-menu-toggle flex items-center justify-between w-full px-4 py-3 no-underline font-normal uppercase text-sm text-gray-900 tracking-wide border-b border-gray-100">
                                <span>Shop All</span>
                            </a>
                        </li>

                        <!-- Pump & Filters -->
                        <li class="mobile-menu-item group">
                            <button class="mobile-menu-toggle flex items-center justify-between w-full px-4 py-3 no-underline font-normal uppercase text-sm text-gray-900 tracking-wide border-b border-gray-100">
                                <span>Pump & Filters</span>
                                <svg class="w-4 h-4 transition-transform duration-200 group-[.active]:rotate-180" 
                                    xmlns="http://www.w3.org/2000/svg" 
                                    viewBox="0 0 24 24" 
                                    fill="none" 
                                    stroke="currentColor" 
                                    stroke-width="2" 
                                    stroke-linecap="round" 
                                    stroke-linejoin="round">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </button>
                            <div class="mobile-submenu hidden">
                                <div class="bg-gray-50">
                                    <div class="px-4 py-3 border-b border-gray-200">
                                        <a href="product.php?category=bathroom" class="text-sm font-medium text-gray-900 hover:text-[#022658]">
                                            All Pump & Filters
                                        </a>
                                    </div>
                                    <ul class="space-y-1">
                                        <li><a href="product.php?category=bathroom" class="block px-8 py-2 text-sm text-gray-700 hover:bg-gray-100">See all Pump & Filters</a></li>
                                        <li><a href="product.php?category=bathroom&subcategory=toilets" class="block px-8 py-2 text-sm text-gray-700 hover:bg-gray-100">Pumps</a></li>
                                        <li><a href="product.php?category=bathroom&subcategory=shower-trays" class="block px-8 py-2 text-sm text-gray-700 hover:bg-gray-100">Filters</a></li>
                                    </ul>
                                </div>
                            </div>
                        </li>

                        <!-- Cleaners -->
                        <li class="mobile-menu-item group">
                            <a href="product.php?category=cleaners" class="mobile-menu-toggle flex items-center justify-between w-full px-4 py-3 no-underline font-normal uppercase text-sm text-gray-900 tracking-wide border-b border-gray-100">
                                <span>Cleaners</span>
                            </a>
                        </li>

                        <!-- Lights -->
                        <li class="mobile-menu-item group">
                            <a href="product.php?category=lights" class="mobile-menu-toggle flex items-center justify-between w-full px-4 py-3 no-underline font-normal uppercase text-sm text-gray-900 tracking-wide border-b border-gray-100">
                                <span>Lights</span>
                            </a>
                        </li>

                        <!-- Covers -->
                        <li class="mobile-menu-item group">
                            <button class="mobile-menu-toggle flex items-center justify-between w-full px-4 py-3 no-underline font-normal uppercase text-sm text-gray-900 tracking-wide border-b border-gray-100">
                                <span>Covers</span>
                                <svg class="w-4 h-4 transition-transform duration-200 group-[.active]:rotate-180" 
                                    xmlns="http://www.w3.org/2000/svg" 
                                    viewBox="0 0 24 24" 
                                    fill="none" 
                                    stroke="currentColor" 
                                    stroke-width="2" 
                                    stroke-linecap="round" 
                                    stroke-linejoin="round">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </button>
                            <div class="mobile-submenu hidden">
                                <div class="bg-gray-50">
                                    <div class="px-4 py-3 border-b border-gray-200">
                                        <a href="product.php?category=gas-water" class="text-sm font-medium text-gray-900 hover:text-[#022658]">
                                            All Covers
                                        </a>
                                    </div>
                                    <ul class="space-y-1">
                                        <li><a href="product.php?category=gas-water" class="block px-8 py-2 text-sm text-gray-700 hover:bg-gray-100">See all Covers</a></li>
                                        <li><a href="product.php?category=gas-water&subcategory=gas" class="block px-8 py-2 text-sm text-gray-700 hover:bg-gray-100">Enclosures</a></li>
                                    </ul>
                                </div>
                            </div>
                        </li>

                        <!-- Heaters -->
                        <li class="mobile-menu-item group">
                            <a href="product.php?category=heaters" class="mobile-menu-toggle flex items-center justify-between w-full px-4 py-3 no-underline font-normal uppercase text-sm text-gray-900 tracking-wide border-b border-gray-100">
                                <span>Heaters</span>
                            </a>
                        </li>

                        <!-- Competition -->
                        <li class="mobile-menu-item group">
                            <button class="mobile-menu-toggle flex items-center justify-between w-full px-4 py-3 no-underline font-normal uppercase text-sm text-gray-900 tracking-wide border-b border-gray-100">
                                <span>Competition</span>
                                <svg class="w-4 h-4 transition-transform duration-200 group-[.active]:rotate-180" 
                                    xmlns="http://www.w3.org/2000/svg" 
                                    viewBox="0 0 24 24" 
                                    fill="none" 
                                    stroke="currentColor" 
                                    stroke-width="2" 
                                    stroke-linecap="round" 
                                    stroke-linejoin="round">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </button>
                            <div class="mobile-submenu hidden">
                                <div class="bg-gray-50">
                                    <div class="px-4 py-3 border-b border-gray-200">
                                        <a href="product.php?category=all-competition" class="text-sm font-medium text-gray-900 hover:text-[#022658]">
                                            All Competition
                                        </a>
                                    </div>
                                    <ul class="space-y-1">
                                        <li><a href="product.php?category=all-competition" class="block px-8 py-2 text-sm text-gray-700 hover:bg-gray-100">See all Competition</a></li>
                                        <li><a href="product.php?category=all-competition&subcategory=line-ropes" class="block px-8 py-2 text-sm text-gray-700 hover:bg-gray-100">Line Ropes</a></li>
                                        <li><a href="product.php?category=all-competition&subcategory=life-guide-equipments" class="block px-8 py-2 text-sm text-gray-700 hover:bg-gray-100">Life Guide Equipments</a></li>
                                        <li><a href="product.php?category=all-competition&subcategory=starting-blocks" class="block px-8 py-2 text-sm text-gray-700 hover:bg-gray-100">Starting Blocks</a></li>
                                        <li><a href="product.php?category=all-competition&subcategory=turn-indicators" class="block px-8 py-2 text-sm text-gray-700 hover:bg-gray-100">Turn Indicators</a></li>
                                        <li><a href="product.php?category=all-competition&subcategory=water-polo" class="block px-8 py-2 text-sm text-gray-700 hover:bg-gray-100">Water Polo</a></li>
                                        <li><a href="product.php?category=heater-air-cons&subcategory=accessories" class="block px-8 py-2 text-sm text-gray-700 hover:bg-gray-100">Pool Separation Walls</a></li>
                                    </ul>
                                </div>
                            </div>
                        </li>

                        <!-- Pool Side -->
                        <li class="mobile-menu-item group">
                            <button class="mobile-menu-toggle flex items-center justify-between w-full px-4 py-3 no-underline font-normal uppercase text-sm text-gray-900 tracking-wide border-b border-gray-100">
                                <span>Pool Side</span>
                                <svg class="w-4 h-4 transition-transform duration-200 group-[.active]:rotate-180" 
                                    xmlns="http://www.w3.org/2000/svg" 
                                    viewBox="0 0 24 24" 
                                    fill="none" 
                                    stroke="currentColor" 
                                    stroke-width="2" 
                                    stroke-linecap="round" 
                                    stroke-linejoin="round">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </button>
                            <div class="mobile-submenu hidden">
                                <div class="bg-gray-50">
                                    <div class="px-4 py-3 border-b border-gray-200">
                                        <a href="product.php?category=pool-size" class="text-sm font-medium text-gray-900 hover:text-[#022658]">
                                            All Pool Side
                                        </a>
                                    </div>
                                    <ul class="space-y-1">
                                        <li><a href="product.php?category=pool-size" class="block px-8 py-2 text-sm text-gray-700 hover:bg-gray-100">See All Pool Side</a></li>
                                        <li><a href="product.php?category=pool-size&subcategory=fountains" class="block px-8 py-2 text-sm text-gray-700 hover:bg-gray-100">Fountains</a></li>
                                        <li><a href="product.php?category=pool-size&subcategory=ladders" class="block px-8 py-2 text-sm text-gray-700 hover:bg-gray-100">Ladders</a></li>
                                        <li><a href="product.php?category=pool-size&subcategory=hoists" class="block px-8 py-2 text-sm text-gray-700 hover:bg-gray-100">Hoists</a></li>
                                    </ul>
                                </div>
                            </div>
                        </li>

                        <!-- Sauna, Spa & Therapy -->
                        <li class="mobile-menu-item group">
                            <a href="product.php?category=sauna-spa-therapy" class="mobile-menu-toggle flex items-center justify-between w-full px-4 py-3 no-underline font-normal uppercase text-sm text-gray-900 tracking-wide border-b border-gray-100">
                                <span>Sauna, Spa & Therapy</span>
                            </a>
                        </li>
                    </ul>
                    
                    <!-- Mobile user actions (for non-logged-in users) -->
                    <?php if (!$isLoggedIn): ?>
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <ul class="space-y-2">
                                <li>
                                    <a href="login.php" class="flex items-center px-4 py-3 no-underline text-sm font-normal text-gray-800">
                                        <span class="mr-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg>
                                        </span>
                                        Trade Login
                                    </a>
                                </li>
                                <li>
                                    <a href="/en-gb/favorites" class="flex items-center px-4 py-3 no-underline text-sm font-normal text-gray-800">
                                        <span class="mr-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                            </svg>
                                        </span>
                                        Favorites (<?php echo $favoriteCount; ?>)
                                    </a>
                                </li>
                                <li>
                                    <a href="/en-gb/cart" class="flex items-center px-4 py-3 no-underline text-sm font-normal text-gray-800">
                                        <span class="mr-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="9" cy="21" r="1"></circle>
                                                <circle cx="20" cy="21" r="1"></circle>
                                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                            </svg>
                                        </span>
                                        Cart (<?php echo $cartCount; ?>)
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </nav>
            </div>
        </div>

        <!-- Desktop Mega Menu -->
        <div class="py-0 bg-white hidden md:block">
            <div class="container mx-auto px-4 max-w-7xl">
                <div class="flex flex-row">
                    <div class="w-full">
                        <div class="flex justify-center text-center">
                            <!-- Main categories menu -->
                            <nav class="flex py-0 justify-center text-center">
                                <ul class="flex flex-wrap justify-center">
                                    <!-- Shop All -->
                                    <li class="group relative">
                                        <a class="px-3 lg:px-4 py-3 no-underline hover:underline font-normal uppercase text-xs inline-flex items-center text-gray-900 tracking-wide touch-target" href="product.php">
                                            <span class="whitespace-nowrap">Shop All</span>
                                        </a>
                                    </li>

                                    <!-- Pump & Filters -->
                                    <li class="group relative">
                                        <a class="px-3 lg:px-4 py-3 no-underline hover:underline font-normal uppercase text-xs inline-flex items-center text-gray-900 tracking-wide touch-target" href="product.php?category=pump-and-filters">
                                            <span class="whitespace-nowrap">Pump & Filters</span>
                                        </a>
                                        <!-- Mega Dropdown -->
                                        <div class="hidden group-hover:block fixed left-0 top-[var(--nav-height)] bg-white shadow-lg border-t border-gray-200 z-50 w-full">
                                            <div class="container mx-auto px-4 py-6 lg:py-8 max-w-7xl">
                                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
                                                    <div>
                                                        <h3 class="font-bold text-sm mb-4 text-left">All Pump & Filters</h3>
                                                        <ul class="space-y-2 text-left">
                                                            <li><a href="product.php?category=pump-and-filters" class="text-sm hover:underline flex items-center gap-1"><span>›</span> See all Pump & Filters</a></li>
                                                            <li class="submenu-parent">
                                                                <a href="product.php?category=pump-and-filters&subcategory=pumps" class="text-sm hover:underline flex items-center justify-between group/submenu">
                                                                    <span>Pumps</span>
                                                                </a>
                                                            </li>
                                                            <li><a href="product.php?category=pump-and-filters&subcategory=filters" class="text-sm hover:underline">Filters </a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                    <!-- Cleaners -->
                                    <li class="group relative">
                                        <a class="px-3 lg:px-4 py-3 no-underline hover:underline font-normal uppercase text-xs inline-flex items-center text-gray-900 tracking-wide touch-target" href="product.php?category=cleaners">
                                            <span class="whitespace-nowrap">Cleaners</span>
                                        </a>
                                    </li>

                                     <!-- Lights -->
                                     <li class="group relative">
                                        <a class="px-3 lg:px-4 py-3 no-underline hover:underline font-normal uppercase text-xs inline-flex items-center text-gray-900 tracking-wide touch-target" href="product.php?category=lights">
                                            <span class="whitespace-nowrap">Lights</span>
                                        </a>
                                    </li>

                                    <!-- Covers -->
                                    <li class="group relative">
                                        <a class="px-3 lg:px-4 py-3 no-underline hover:underline font-normal uppercase text-xs inline-flex items-center text-gray-900 tracking-wide touch-target" href="product.php?category=covers">
                                            <span class="whitespace-nowrap">Covers</span>
                                        </a>
                                        <!-- Mega Dropdown -->
                                        <div class="hidden group-hover:block fixed left-0 top-[var(--nav-height)] bg-white shadow-lg border-t border-gray-200 z-50 w-full">
                                            <div class="container mx-auto px-4 py-6 lg:py-8 max-w-7xl">
                                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
                                                    <div>
                                                        <h3 class="font-bold text-sm mb-4 text-left">All Covers</h3>
                                                        <ul class="space-y-2 text-left">
                                                            <li><a href="product.php?category=covers" class="text-sm hover:underline flex items-center gap-1"><span>›</span> See all Covers</a></li>
                                                            <li class="submenu-parent">
                                                                <a href="product.php?category=covers&subcategory=enclosures" class="text-sm hover:underline flex items-center justify-between group/submenu">
                                                                    <span>Enclosures</span>
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                     <!-- Heaters -->
                                     <li class="group relative">
                                        <a class="px-3 lg:px-4 py-3 no-underline hover:underline font-normal uppercase text-xs inline-flex items-center text-gray-900 tracking-wide touch-target" href="product.php?category=heaters">
                                            <span class="whitespace-nowrap">Heaters</span>
                                        </a>
                                    </li>

                                    <!-- Competition -->
                                    <li class="group relative">
                                        <a class="px-3 lg:px-4 py-3 no-underline hover:underline font-normal uppercase text-xs inline-flex items-center text-gray-900 tracking-wide touch-target" href="product.php?category=competition">
                                            <span class="whitespace-nowrap">Competition</span>
                                        </a>
                                        <!-- Mega Dropdown -->
                                        <div class="hidden group-hover:block fixed left-0 top-[var(--nav-height)] bg-white shadow-lg border-t border-gray-200 z-50 w-full">
                                            <div class="container mx-auto px-4 py-6 lg:py-8 max-w-7xl">
                                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
                                                    <div>
                                                        <h3 class="font-bold text-sm mb-4 text-left">All Competition</h3>
                                                        <ul class="space-y-2 text-left">
                                                            <li><a href="product.php?category=competition" class="text-sm hover:underline flex items-center gap-1"><span>›</span> See all Competition</a></li>
                                                            <li><a href="product.php?category=competition&subcategory=line-ropes" class="text-sm hover:underline">Line Ropes</a></li>
                                                            <li><a href="product.php?category=competition&subcategory=life-guide-equipments" class="text-sm hover:underline">Life Guide Equipments</a></li>
                                                            <li><a href="product.php?category=competition&subcategory=starting-blocks" class="text-sm hover:underline">Starting Blocks</a></li>
                                                            <li><a href="product.php?category=competition&subcategory=turn-indicators" class="text-sm hover:underline">Turn Indicators</a></li>
                                                            <li><a href="product.php?category=competition&subcategory=water-polo" class="text-sm hover:underline">Water Polo</a></li>
                                                            <li><a href="product.php?category=competition&subcategory=pool-separation-walls" class="text-sm hover:underline">Pool Separation Walls</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                    <!-- Pool Side -->
                                    <li class="group relative">
                                        <a class="px-3 lg:px-4 py-3 no-underline hover:underline font-normal uppercase text-xs inline-flex items-center text-gray-900 tracking-wide touch-target" href="product.php?category=pool-side">
                                            <span class="whitespace-nowrap">Pool Side </span>
                                        </a>
                                        <!-- Mega Dropdown -->
                                        <div class="hidden group-hover:block fixed left-0 top-[var(--nav-height)] bg-white shadow-lg border-t border-gray-200 z-50 w-full">
                                            <div class="container mx-auto px-4 py-6 lg:py-8 max-w-7xl">
                                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
                                                    <div>
                                                        <h3 class="font-bold text-sm mb-4 text-left">All Pool Side</h3>
                                                        <ul class="space-y-2 text-left">
                                                            <li><a href="product.php?category=pool-side" class="text-sm hover:underline flex items-center gap-1"><span>›</span> See All Pool Side</a></li>
                                                          
                                                            <li class="">
                                                                <a href="product.php?category=pool-side&subcategory=fountains" class="text-sm hover:underline flex items-center justify-between group/submenu">
                                                                    <span>Fountains</span>
                                                                </a>
                                                            </li>

                                                            <li class="">
                                                                <a href="product.php?category=pool-side&subcategory=ladders" class="text-sm hover:underline flex items-center justify-between group/submenu">
                                                                    <span>Ladders</span>
                                                                </a>
                                                            </li>

                                                            <li class="">
                                                                <a href="product.php?category=pool-side&subcategory=hoists" class="text-sm hover:underline flex items-center justify-between group/submenu">
                                                                    <span>Hoists</span>
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                     <li class="group relative">
                                        <a class="px-3 lg:px-4 py-3 no-underline hover:underline font-normal uppercase text-xs inline-flex items-center text-gray-900 tracking-wide touch-target" href="product.php?category=sauna-spa-and-therapy">
                                            <span class="whitespace-nowrap">Sauna, Spa & Therapy</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>