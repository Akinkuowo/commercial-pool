<?php

    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);

    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.gc_maxlifetime', 3600);
    
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Function to get product image with fallback
    function getProductImage($image, $category = '') {
        // If image exists in database, use it
        if (!empty($image)) {
            return $image;
        }
        
        // Otherwise use category-based fallback
        $categoryLower = strtolower($category);
        $imageMap = [
            'awning' => 'assets/img/Products/product2.webp',
            'camping' => 'assets/img/Products/product1.webp',
            'caravan' => 'assets/img/Products/product8.jpg',
            'electrical' => 'assets/img/Products/product7.jpeg',
            'heating' => 'assets/img/Products/product4.jpg',
            'kitchen' => 'assets/img/Products/product3.jpg',
            'fridge' => 'assets/img/Products/product5.jpg',
            'water' => 'assets/img/Products/product6.jpeg'
        ];
        
        foreach ($imageMap as $key => $url) {
            if (strpos($categoryLower, $key) !== false) {
                return $url;
            }
        }
        
        return 'assets/img/Products/product1.webp'; // default
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Products - Jacksons Leisure</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Adobe Fonts - Myriad Pro -->
    <link rel="stylesheet" href="https://use.typekit.net/yzr5vmg.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="assets/css/styles.css" rel="stylesheet" />

    <?php include('include/style.php') ?>
    
    <style>
        /* Custom styles for range slider */
        input[type="range"] {
            -webkit-appearance: none;
            appearance: none;
            width: 100%;
            height: 6px;
            border-radius: 3px;
            background: #e5e7eb;
            outline: none;
        }
        
        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #2563eb;
            cursor: pointer;
        }
        
        input[type="range"]::-moz-range-thumb {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #2563eb;
            cursor: pointer;
            border: none;
        }
        
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        /* Wishlist and compare button animations */
        .wishlist-btn, .compare-btn {
            transition: all 0.2s ease;
        }
        
        .wishlist-btn:hover, .compare-btn:hover {
            transform: scale(1.1);
        }
        
        .wishlist-btn.active {
            color: #ef4444;
        }
        
        .compare-btn.active {
            color: #3b82f6;
        }
        
        /* Compare floating badge */
        .compare-badge {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                transform: translateY(100px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        /* Loading button state */
        .btn-loading {
            position: relative;
            pointer-events: none;
            opacity: 0.7;
        }
        
        .btn-loading::after {
            content: "";
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.6s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-50">
    
    <?php include('include/header.php'); ?>

    <!-- Breadcrumb -->
    <div class="bg-white border-b border-gray-200 py-3">
        <div class="container mx-auto px-8 max-w-7xl">
            <nav class="flex text-sm">
                <a href="/" class="text-gray-500 hover:text-gray-700">Home</a>
                <span class="mx-2 text-gray-400">/</span>
                <span id="breadcrumbCategory" class="text-gray-900">New Products</span>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-8 py-8 max-w-7xl">
        <!-- Products Section -->
        <main class="w-full">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 id="pageTitle" class="text-2xl font-bold text-gray-900">New Products</h1>
                        <p id="productCount" class="text-sm text-gray-500 mt-1">Loading...</p>
                    </div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <!-- Filters Button -->
                        <div class="relative">
                            <button id="filtersBtn" class="border border-gray-300 rounded-md px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-2 bg-white hover:bg-gray-50 transition">
                                <i class="fas fa-filter"></i>
                                <span>Filters</span>
                                <span id="filterCount" class="hidden bg-blue-600 text-white text-xs px-2 py-0.5 rounded-full ml-1">0</span>
                            </button>
                            
                            <!-- Filters Dropdown Panel -->
                            <div id="filtersDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50 max-h-[80vh] overflow-y-auto">
                                <div class="p-6">
                                    <div class="flex justify-between items-center mb-4">
                                        <h2 class="text-lg font-bold">Filters</h2>
                                        <button id="closeFiltersBtn" class="text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Search Filter -->
                                    <div class="mb-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                                        <input type="text" id="searchInput" placeholder="Search products..." 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                    </div>
                                    
                                    <!-- Price Range Filter -->
                                    <div class="mb-6 pb-6 border-b border-gray-200">
                                        <h3 class="text-sm font-medium text-gray-700 mb-3">Price Range</h3>
                                        <div class="space-y-3">
                                            <div>
                                                <label class="text-xs text-gray-600">Min: £<span id="minPriceLabel">0</span></label>
                                                <input type="range" id="minPriceRange" min="0" max="1000" value="0" step="10" class="w-full">
                                            </div>
                                            <div>
                                                <label class="text-xs text-gray-600">Max: £<span id="maxPriceLabel">1000</span></label>
                                                <input type="range" id="maxPriceRange" min="0" max="1000" value="1000" step="10" class="w-full">
                                            </div>
                                            <div class="flex gap-2">
                                                <input type="number" id="minPriceInput" placeholder="Min" 
                                                       class="w-1/2 px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <input type="number" id="maxPriceInput" placeholder="Max" 
                                                       class="w-1/2 px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Brand Filter -->
                                    <div class="mb-6 pb-6 border-b border-gray-200">
                                        <h3 class="text-sm font-medium text-gray-700 mb-3">Brand</h3>
                                        <div id="brandFilters" class="space-y-2 max-h-48 overflow-y-auto">
                                            <!-- Populated by JavaScript -->
                                        </div>
                                    </div>
                                    
                                    <!-- Stock Status Filter -->
                                    <div class="mb-6">
                                        <h3 class="text-sm font-medium text-gray-700 mb-3">Availability</h3>
                                        <label class="flex items-center cursor-pointer">
                                            <input type="checkbox" id="inStockOnly" class="mr-2 rounded text-blue-600 focus:ring-blue-500">
                                            <span class="text-sm text-gray-700">In Stock Only</span>
                                        </label>
                                    </div>
                                    
                                    <!-- Clear Filters -->
                                    <button id="clearFilters" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-4 rounded-md transition">
                                        Clear All Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- View Toggle -->
                        <div class="flex bg-gray-100 rounded-md p-1">
                            <button id="gridViewBtn" class="view-toggle px-3 py-2 rounded text-sm transition bg-white shadow-sm">
                                <i class="fas fa-th"></i>
                            </button>
                            <button id="listViewBtn" class="view-toggle px-3 py-2 rounded text-sm transition">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                        
                        <!-- Sort -->
                        <select id="sortSelect" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="popular">Most Popular</option>
                            <option value="new">Newest First</option>
                            <option value="name-asc">Name (A-Z)</option>
                            <option value="name-desc">Name (Z-A)</option>
                            <option value="price-asc">Price (Low to High)</option>
                            <option value="price-desc">Price (High to Low)</option>
                        </select>
                    </div>
                </div>
            </div>

                <!-- Products Grid/List -->
                <div id="productsContainer">
                    <div id="productsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Populated by JavaScript -->
                    </div>
                </div>

                <!-- Loading State -->
                <div id="loadingState" class="text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900"></div>
                    <p class="mt-4 text-gray-600">Loading products...</p>
                </div>

                <!-- Empty State -->
                <div id="emptyState" class="hidden text-center py-12 bg-white rounded-lg shadow-sm">
                    <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No products found</h3>
                    <p class="text-gray-500">Try adjusting your filters or search terms</p>
                </div>

                <!-- Load More Button -->
                <div id="loadMoreContainer" class="hidden mt-8 text-center">
                    <button id="loadMoreBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-md transition">
                        Load More Products
                    </button>
                </div>
            </div>
        </main>
    </div>

    <!-- Compare Floating Badge -->
    <div id="compareBadge" class="compare-badge hidden">
        <div class="bg-[#0e703a] text-white rounded-lg shadow-lg p-4 flex items-center gap-3">
            <div class="flex-1">
                <p class="font-semibold">Compare Products</p>
                <p class="text-sm opacity-90"><span id="compareCount">0</span> products selected</p>
            </div>
            <button id="viewCompareBtn" class="bg-white text-[#0e703a] px-4 py-2 rounded hover:bg-blue-50 transition font-medium text-sm">
                Compare
            </button>
            <button id="clearCompareBtn" class="text-white hover:text-gray-200 transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
    </div>

    <?php include('include/footer.php'); ?>
    <?php include('include/script.php') ?>
    

    <script>
        // Get URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const filters = {
            category: urlParams.get('category') || '',
            subcategory: urlParams.get('subcategory') || '',
            type: urlParams.get('type') || '',
            brand: urlParams.get('brand') || '',
            search: urlParams.get('search') || '',
            sort: 'popular',
            minPrice: 0,
            maxPrice: 10000,
            inStockOnly: false
        };

        let products = [];
        let filteredProducts = [];
        let displayedCount = 0;
        let currentView = 'grid';
        const productsPerPage = 12;
        
        // Compare storage
        let compareList = JSON.parse(localStorage.getItem('compareList') || '[]');
        

        
        // Load cart count from server
        async function loadCartCount() {
            try {
                const response = await fetch('api/cart.php');
                const data = await response.json();
                
                if (data.success) {
                    updateCartCount(data.total_items || 0);
                }
            } catch (error) {
                console.error('Error loading cart count:', error);
            }
        }
        

        // Initialize page
        document.addEventListener('DOMContentLoaded', () => {
            loadProducts();
            setupEventListeners();
            updateBreadcrumb();
            initializePriceRange();
            updateCompareBadge();
            loadCartCount();
        });

        // Load products from API
        async function loadProducts() {
            try {
                const queryParams = new URLSearchParams({
                    category: filters.category,
                    subcategory: filters.subcategory,
                    type: filters.type,
                    brand: filters.brand,
                    search: filters.search
                });
                
                const response = await fetch(`api/products.php?${queryParams}`);
                const data = await response.json();

                if (data.success) {
                    products = data.products;
                    filteredProducts = [...products];
                    
                    updatePriceRangeFromProducts();
                    updatePageTitle();
                    applyAllFilters();
                    renderBrandFilters(data.brands);
                    
                    document.getElementById('loadingState').classList.add('hidden');
                } else {
                    showError('Failed to load products');
                }
            } catch (error) {
                console.error('Error loading products:', error);
                showError('An error occurred while loading products');
            }
        }



        // Compare functions
        function toggleCompare(productId, event) {
            event.stopPropagation();
            
            const index = compareList.indexOf(productId);
            if (index > -1) {
                compareList.splice(index, 1);
                showNotification('Removed from compare', 'info');
            } else {
                if (compareList.length >= 4) {
                    showNotification('Maximum 4 products can be compared', 'warning');
                    // Revert checkbox state if it was the trigger
                    if (event.target && event.target.type === 'checkbox') {
                        event.target.checked = false;
                    }
                    return;
                }
                compareList.push(productId);
                showNotification('Added to compare', 'success');
            }
            
            localStorage.setItem('compareList', JSON.stringify(compareList));
            updateCompareButtons();
            updateCompareBadge();
        }

        function updateCompareButtons() {
            document.querySelectorAll('.compare-checkbox').forEach(checkbox => {
                const productId = parseInt(checkbox.dataset.productId);
                checkbox.checked = compareList.includes(productId);
            });
        }

        function updateCompareBadge() {
            const badge = document.getElementById('compareBadge');
            const count = document.getElementById('compareCount');
            
            if (compareList.length > 0) {
                badge.classList.remove('hidden');
                count.textContent = compareList.length;
            } else {
                badge.classList.add('hidden');
            }
        }

        function clearCompare() {
            compareList = [];
            localStorage.setItem('compareList', JSON.stringify(compareList));
            updateCompareButtons();
            updateCompareBadge();
            showNotification('Compare list cleared', 'info');
        }

        function viewCompare() {
            if (compareList.length < 2) {
                showNotification('Please select at least 2 products to compare', 'warning');
                return;
            }
            window.location.href = `compare.php?ids=${compareList.join(',')}`;
        }

        // Notification system
        function showNotification(message, type = 'info') {
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                warning: 'bg-yellow-500',
                info: 'bg-blue-500'
            };
            
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300`;
            notification.style.transform = 'translateX(400px)';
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 10);
            
            setTimeout(() => {
                notification.style.transform = 'translateX(400px)';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Initialize price range
        function initializePriceRange() {
            const minRange = document.getElementById('minPriceRange');
            const maxRange = document.getElementById('maxPriceRange');
            const minInput = document.getElementById('minPriceInput');
            const maxInput = document.getElementById('maxPriceInput');
            const minLabel = document.getElementById('minPriceLabel');
            const maxLabel = document.getElementById('maxPriceLabel');
            
            minRange.addEventListener('input', (e) => {
                const value = parseInt(e.target.value);
                if (value >= parseInt(maxRange.value)) {
                    e.target.value = parseInt(maxRange.value) - 10;
                    return;
                }
                minLabel.textContent = value;
                minInput.value = value;
                filters.minPrice = value;
                debouncedFilterProducts();
            });
            
            maxRange.addEventListener('input', (e) => {
                const value = parseInt(e.target.value);
                if (value <= parseInt(minRange.value)) {
                    e.target.value = parseInt(minRange.value) + 10;
                    return;
                }
                maxLabel.textContent = value;
                maxInput.value = value;
                filters.maxPrice = value;
                debouncedFilterProducts();
            });
            
            minInput.addEventListener('change', (e) => {
                let value = parseInt(e.target.value) || 0;
                value = Math.max(0, Math.min(value, filters.maxPrice - 10));
                minRange.value = value;
                minLabel.textContent = value;
                minInput.value = value;
                filters.minPrice = value;
                applyAllFilters();
            });
            
            maxInput.addEventListener('change', (e) => {
                let value = parseInt(e.target.value) || 10000;
                value = Math.min(10000, Math.max(value, filters.minPrice + 10));
                maxRange.value = value;
                maxLabel.textContent = value;
                maxInput.value = value;
                filters.maxPrice = value;
                applyAllFilters();
            });
        }

        function updatePriceRangeFromProducts() {
            if (products.length === 0) return;
            
            const prices = products.map(p => parseFloat(p.price)).filter(p => !isNaN(p));
            const minProductPrice = Math.floor(Math.min(...prices));
            const maxProductPrice = Math.ceil(Math.max(...prices));
            
            const minRange = document.getElementById('minPriceRange');
            const maxRange = document.getElementById('maxPriceRange');
            const minInput = document.getElementById('minPriceInput');
            const maxInput = document.getElementById('maxPriceInput');
            const minLabel = document.getElementById('minPriceLabel');
            const maxLabel = document.getElementById('maxPriceLabel');
            
            minRange.min = 0;
            minRange.max = maxProductPrice;
            maxRange.min = 0;
            maxRange.max = maxProductPrice;
            
            minRange.value = 0;
            maxRange.value = maxProductPrice;
            
            minLabel.textContent = '0';
            maxLabel.textContent = maxProductPrice;
            
            minInput.value = 0;
            maxInput.value = maxProductPrice;
            
            filters.minPrice = 0;
            filters.maxPrice = maxProductPrice;
        }

        function applyAllFilters() {
            let result = [...products];
            
            result = result.filter(p => {
                const price = parseFloat(p.price);
                return price >= filters.minPrice && price <= filters.maxPrice;
            });
            
            if (filters.inStockOnly) {
                result = result.filter(p => p.stock === 'In Stock' || p.quantity > 0);
            }
            
            const checkedBrands = Array.from(document.querySelectorAll('.brand-filter:checked'))
                .map(cb => cb.value);
            
            if (checkedBrands.length > 0) {
                result = result.filter(p => checkedBrands.includes(p.brand));
            }
            
            const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
            if (searchTerm) {
                result = result.filter(p => 
                    p.name.toLowerCase().includes(searchTerm) ||
                    (p.description && p.description.toLowerCase().includes(searchTerm)) ||
                    (p.sku && p.sku.toLowerCase().includes(searchTerm))
                );
            }
            
            filteredProducts = result;
            applySorting();
            updateProductCount();
            updateFilterCount();
            renderProducts();
        }

        function updateFilterCount() {
            let count = 0;
            
            // Count active filters
            const searchTerm = document.getElementById('searchInput')?.value || '';
            if (searchTerm) count++;
            
            if (filters.inStockOnly) count++;
            
            const checkedBrands = document.querySelectorAll('.brand-filter:checked').length;
            if (checkedBrands > 0) count++;
            
            // Check if price range is not at default
            if (filters.minPrice > 0 || filters.maxPrice < 1000) count++;
            
            const filterCountBadge = document.getElementById('filterCount');
            if (count > 0) {
                filterCountBadge.textContent = count;
                filterCountBadge.classList.remove('hidden');
            } else {
                filterCountBadge.classList.add('hidden');
            }
        }

        const debouncedFilterProducts = debounce(applyAllFilters, 300);

        function renderProducts(reset = true) {
            if (reset) displayedCount = 0;
            
            const container = document.getElementById('productsContainer');
            const emptyState = document.getElementById('emptyState');
            const loadMoreContainer = document.getElementById('loadMoreContainer');
            
            if (filteredProducts.length === 0) {
                container.innerHTML = '';
                emptyState.classList.remove('hidden');
                loadMoreContainer.classList.add('hidden');
                return;
            }
            
            emptyState.classList.add('hidden');
            
            const endIndex = Math.min(displayedCount + productsPerPage, filteredProducts.length);
            const productsToShow = filteredProducts.slice(displayedCount, endIndex);
            
            if (reset) {
                if (currentView === 'grid') {
                    container.innerHTML = '<div id="productsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"></div>';
                } else {
                    container.innerHTML = '<div id="productsGrid" class="space-y-4"></div>';
                }
            }
            
            const grid = document.getElementById('productsGrid');
            productsToShow.forEach(product => {
                const productHTML = currentView === 'grid' 
                    ? createProductCardGrid(product)
                    : createProductCardList(product);
                grid.innerHTML += productHTML;
            });
            
            displayedCount = endIndex;
            
            // Centralized updates handled by Wishlist utility
            if (typeof Wishlist !== 'undefined') {
                Wishlist.updateUI();
            }
            
            updateCompareButtons();
            attachProductEventListeners();
            
            if (displayedCount < filteredProducts.length) {
                loadMoreContainer.classList.remove('hidden');
            } else {
                loadMoreContainer.classList.add('hidden');
            }
        }

        function attachProductEventListeners() {
            // Wishlist utility handles clicks via data-wishlist-btn
            
            document.querySelectorAll('.compare-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', (e) => toggleCompare(parseInt(checkbox.dataset.productId), e));
            });
            
            // Attach add to cart event listeners
            document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const productId = parseInt(btn.dataset.productId);
                    addToCart(productId, btn);
                });
            });
        }

        function createProductCardGrid(product) {
            const isNew = product.is_new == 1;
            const isPopular = product.is_popular == 1;
            const inStock = product.stock === 'In Stock' || product.quantity > 0;
            const inWishlist = wishlist.includes(product.id);
            const inCompare = compareList.includes(product.id);
            
            return `
                <div  class="bg-white rounded-lg shadow-sm hover:shadow-md transition overflow-hidden group block">
                    <div class="relative">
                        <a href="product_detail.php?id=${product.id}">
                            <img src="${product.image}" alt="${product.name}" 
                                 class="w-full h-48 object-cover"
                                 onerror="this.onerror=null; this.src='assets/img/Products/product1.webp';">
                        </a>
                        <div class="absolute top-2 left-2 flex flex-col gap-2">
                            <button class="wishlist-btn bg-white rounded-full w-10 h-10 flex items-center justify-center shadow-md hover:shadow-lg text-gray-600 hover:text-red-500" 
                                    data-wishlist-btn
                                    data-wishlist-id="${product.id}" 
                                    title="Add to Wishlist">
                                <i class="far fa-heart"></i>
                            </button>

                        </div>

                        <div class="absolute top-2 right-2 flex flex-col gap-1">
                        ${isNew ? '<span class="bg-green-500 text-white text-xs px-2 py-1 rounded">NEW</span>' : ''}
                        ${isPopular ? '<span class="bg-blue-500 text-white text-xs px-2 py-1 rounded">POPULAR</span>' : ''}
                    </div>
                    ${!inStock ? '<div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center"><span class="bg-red-500 text-white px-4 py-2 rounded">Out of Stock</span></div>' : ''}
                </div>
                <div class="p-4">
                    <a href="product_detail.php?id=${product.id}" >
                        <p class="text-xs text-gray-500 mb-1">${product.brand || 'Generic'}</p>
                        <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">${product.name}</h3>
                        <p class="text-sm text-gray-600 mb-3 line-clamp-2">${product.description || ''}</p>
                    </a>
                    <div class="flex justify-between items-center">
                        <span class="text-xl font-bold text-[#CC4514]">£${parseFloat(product.price).toFixed(2)}</span>
                        ${inStock ? `
                            <button class="add-to-cart-btn bg-[#0e703a] hover:bg-[#CC4514] text-white px-6 py-2 rounded transition" data-product-id="${product.id}">
                                Add to Cart
                            </button>
                        ` : `
                            <button disabled class="bg-gray-300 text-gray-500 px-4 py-2 rounded text-sm cursor-not-allowed">
                                Out of Stock
                            </button>
                        `}
                    </div>
                    <div class="mt-3 flex items-center">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="compare-checkbox form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out" data-product-id="${product.id}" ${inCompare ? 'checked' : ''}>
                            <span class="ml-2 text-sm text-gray-600">Compare</span>
                        </label>
                </div>
            </div>
        `;
    }

    function createProductCardList(product) {
        const isNew = product.is_new == 1;
        const isPopular = product.is_popular == 1;
        const inStock = product.stock === 'In Stock' || product.quantity > 0;
        const inWishlist = wishlist.includes(product.id);
        const inCompare = compareList.includes(product.id);
        
        return `
            <div  class="bg-white rounded-lg shadow-sm hover:shadow-md transition overflow-hidden block">
                <div class="flex flex-col sm:flex-row">
                    <a href="product_detail.php?id=${product.id}">
                        <div class="relative sm:w-64 flex-shrink-0">
                            <img src="${product.image}" alt="${product.name}" 
                                 class="w-full h-48 sm:h-full object-cover"
                                 onerror="this.onerror=null; this.src='assets/img/Products/product1.webp';">
                            
                            <div class="absolute top-2 left-2 flex gap-2">
                                <button class="wishlist-btn bg-white rounded-full w-10 h-10 flex items-center justify-center shadow-md hover:shadow-lg text-gray-600 hover:text-red-500" 
                                        data-wishlist-btn
                                        data-wishlist-id="${product.id}" 
                                        title="Add to Wishlist">
                                    <i class="far fa-heart"></i>
                                </button>

                            </div>
                            
                            ${!inStock ? '<div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center"><span class="bg-red-500 text-white px-4 py-2 rounded text-sm">Out of Stock</span></div>' : ''}
                        </div>
                    </a>

                    <div class="flex-1 p-6 flex flex-col">
                        <a href="product_detail.php?id=${product.id}">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">${product.brand || 'Generic'}</p>
                                    <h3 class="font-semibold text-lg text-gray-900">${product.name}</h3>
                                    ${product.sku ? `<p class="text-xs text-gray-400 mt-1">SKU: ${product.sku}</p>` : ''}
                                </div>
                                <div class="flex flex-col gap-1">
                                    ${isNew ? '<span class="bg-green-500 text-white text-xs px-2 py-1 rounded">NEW</span>' : ''}
                                    ${isPopular ? '<span class="bg-blue-500 text-white text-xs px-2 py-1 rounded">POPULAR</span>' : ''}
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mb-4 line-clamp-3 flex-1">${product.description || 'No description available'}</p>
                        </a>

                        <div class="flex items-center justify-between mt-auto">
                            <span class="text-2xl font-bold text-[#CC4514]">£${parseFloat(product.price).toFixed(2)}</span>
                            ${inStock ? `
                                <div class="flex gap-2">
                                    <button onclick="event.preventDefault(); event.stopPropagation(); window.location.href='product_detail.php?id=${product.id}'" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded transition">
                                        View Details
                                    </button>
                                    <button class="add-to-cart-btn bg-[#0e703a] hover:bg-[#CC4514] text-white px-6 py-2 rounded transition" data-product-id="${product.id}">
                                        Add to Cart
                                    </button>
                                    
                                </div>
                            ` : `
                                <button disabled class="bg-gray-300 text-gray-500 px-6 py-2 rounded cursor-not-allowed">
                                    Out of Stock
                                </button>
                            `}
                        </div>
                         <div class="mt-2 flex items-center">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="compare-checkbox form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out" data-product-id="${product.id}" ${inCompare ? 'checked' : ''}>
                                <span class="ml-2 text-sm text-gray-600">Compare</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function renderBrandFilters(brands) {
        const container = document.getElementById('brandFilters');
        
        if (!brands || brands.length === 0) {
            container.innerHTML = '<p class="text-sm text-gray-500">No brands available</p>';
            return;
        }
        
        container.innerHTML = brands.map(brand => `
            <label class="flex items-center cursor-pointer hover:bg-gray-50 p-1 rounded">
                <input type="checkbox" value="${brand.brand}" 
                       class="brand-filter mr-2 rounded text-blue-600 focus:ring-blue-500"
                       ${filters.brand === brand.brand ? 'checked' : ''}>
                <span class="text-sm text-gray-700">${brand.brand} <span class="text-gray-400">(${brand.count})</span></span>
            </label>
        `).join('');
        
        document.querySelectorAll('.brand-filter').forEach(checkbox => {
            checkbox.addEventListener('change', applyAllFilters);
        });
    }

    function applySorting() {
        const sortValue = document.getElementById('sortSelect').value;
        
        switch(sortValue) {
            case 'name-asc':
                filteredProducts.sort((a, b) => a.name.localeCompare(b.name));
                break;
            case 'name-desc':
                filteredProducts.sort((a, b) => b.name.localeCompare(a.name));
                break;
            case 'price-asc':
                filteredProducts.sort((a, b) => parseFloat(a.price) - parseFloat(b.price));
                break;
            case 'price-desc':
                filteredProducts.sort((a, b) => parseFloat(b.price) - parseFloat(a.price));
                break;
            case 'popular':
                filteredProducts.sort((a, b) => (b.is_popular || 0) - (a.is_popular || 0));
                break;
            case 'new':
                filteredProducts.sort((a, b) => (b.is_new || 0) - (a.is_new || 0));
                break;
        }
    }

    function setupEventListeners() {
        const searchInput = document.getElementById('searchInput');
        searchInput.value = filters.search;
        searchInput.addEventListener('input', debounce(applyAllFilters, 300));
        
        document.getElementById('sortSelect').addEventListener('change', (e) => {
            filters.sort = e.target.value;
            applySorting();
            renderProducts();
        });
        
        document.getElementById('inStockOnly').addEventListener('change', (e) => {
            filters.inStockOnly = e.target.checked;
            applyAllFilters();
        });
        
        document.getElementById('gridViewBtn').addEventListener('click', () => {
            currentView = 'grid';
            updateViewButtons();
            renderProducts();
        });
        
        document.getElementById('listViewBtn').addEventListener('click', () => {
            currentView = 'list';
            updateViewButtons();
            renderProducts();
        });
        
        document.getElementById('clearFilters').addEventListener('click', () => {
            window.location.href = 'product.php';
        });
        
        document.getElementById('loadMoreBtn').addEventListener('click', () => {
            renderProducts(false);
        });
        
        document.getElementById('viewCompareBtn').addEventListener('click', viewCompare);
        document.getElementById('clearCompareBtn').addEventListener('click', clearCompare);
        
        // Filters dropdown functionality
        const filtersBtn = document.getElementById('filtersBtn');
        const filtersDropdown = document.getElementById('filtersDropdown');
        const closeFiltersBtn = document.getElementById('closeFiltersBtn');
        
        filtersBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            filtersDropdown.classList.toggle('hidden');
            updateFilterCount();
        });
        
        closeFiltersBtn.addEventListener('click', () => {
            filtersDropdown.classList.add('hidden');
        });
        
        // Click outside to close
        document.addEventListener('click', (e) => {
            if (!filtersBtn.contains(e.target) && !filtersDropdown.contains(e.target)) {
                filtersDropdown.classList.add('hidden');
            }
        });
        
        // Prevent dropdown from closing when clicking inside
        filtersDropdown.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    }

    function updateViewButtons() {
        const gridBtn = document.getElementById('gridViewBtn');
        const listBtn = document.getElementById('listViewBtn');
        
        if (currentView === 'grid') {
            gridBtn.classList.add('bg-white', 'shadow-sm');
            listBtn.classList.remove('bg-white', 'shadow-sm');
        } else {
            listBtn.classList.add('bg-white', 'shadow-sm');
            gridBtn.classList.remove('bg-white', 'shadow-sm');
        }
    }

    function updatePageTitle() {
        let title = 'Products';
        
        if (filters.category) {
            title = formatCategoryName(filters.category);
        }
        if (filters.subcategory) {
            title += ' - ' + formatCategoryName(filters.subcategory);
        }
        if (filters.type) {
            title += ' - ' + formatCategoryName(filters.type);
        }
        
        document.getElementById('pageTitle').textContent = title;
        document.title = title + ' - Jacksons Leisure';
    }

    function updateBreadcrumb() {
        const breadcrumb = document.getElementById('breadcrumb-category');
        let path = 'Products';
        
        if (filters.category) {
            path = formatCategoryName(filters.category);
        }
        
        breadcrumb.textContent = path;
    }

    function updateProductCount() {
        const count = document.getElementById('productCount');
        count.textContent = `${filteredProducts.length} product${filteredProducts.length !== 1 ? 's' : ''} found`;
    }

    function formatCategoryName(slug) {
        return slug.split('-').map(word => 
            word.charAt(0).toUpperCase() + word.slice(1)
        ).join(' ');
    }

    function showError(message) {
        document.getElementById('loadingState').classList.add('hidden');
        document.getElementById('productsContainer').innerHTML = `
            <div class="col-span-full text-center py-12 bg-white rounded-lg shadow-sm">
                <i class="fas fa-exclamation-triangle text-red-500 text-6xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">Error</h3>
                <p class="text-gray-500">${message}</p>
            </div>
        `;
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Add to cart function with loading state
    async function addToCart(productId, buttonElement = null) {
        // Add loading state to button
        if (buttonElement) {
            buttonElement.disabled = true;
            buttonElement.classList.add('btn-loading');
            const originalText = buttonElement.textContent;
            buttonElement.textContent = '';
        }
        
        try {
            const response = await fetch('api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'add',
                    product_id: productId,
                    quantity: 1
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showNotification(data.message || 'Added to cart successfully', 'success');
                updateCartCount(data.cart_count || data.total_items);
            } else {
                showNotification(data.message || 'Failed to add to cart', 'error');
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
            showNotification('An error occurred. Please try again.', 'error');
        } finally {
            // Remove loading state
            if (buttonElement) {
                buttonElement.disabled = false;
                buttonElement.classList.remove('btn-loading');
                buttonElement.textContent = 'Add to Cart';
            }
        }
    }
    
    // Update cart count in header
    function updateCartCount(count) {
        const cartBadge = document.querySelector('.cart-count, #cartCount');
        if (cartBadge) {
            cartBadge.textContent = count;
            if (count > 0) {
                cartBadge.classList.remove('hidden');
            } else {
                cartBadge.classList.add('hidden');
            }
        }
        
        // Also update cart icon badge if it exists
        const cartBadgeAlt = document.querySelector('[data-cart-count]');
        if (cartBadgeAlt) {
            cartBadgeAlt.setAttribute('data-cart-count', count);
            cartBadgeAlt.textContent = count;
        }
    }
</script>
</body>
</html> 