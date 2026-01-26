<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);

    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.gc_maxlifetime', 3600);
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Function to get product image with fallback
    function getProductImage($image, $category = '') {
        if (!empty($image)) {
            return $image;
        }
        
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
        
        return 'assets/img/Products/product1.webp';
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compare Products - Commercial Pool Equipment</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="assets/css/styles.css" rel="stylesheet" />

    <?php include('include/style.php') ?>
    
    <style>
        .comparison-table {
            overflow-x: auto;
        }
        
        .comparison-table table {
            min-width: 100%;
        }
        
        .comparison-table th {
            position: sticky;
            top: 0;
            background: white;
            z-index: 10;
        }
        
        .product-column {
            min-width: 250px;
            max-width: 300px;
        }
        
        .feature-row:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .sticky-header {
            position: sticky;
            top: 0;
            background: white;
            z-index: 20;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .remove-btn {
            transition: all 0.2s ease;
        }
        
        .remove-btn:hover {
            transform: scale(1.1);
        }
        
        @media (max-width: 768px) {
            .product-column {
                min-width: 200px;
                max-width: 250px;
            }
        }
        
        .highlight-difference {
            background-color: #fef3c7;
        }
        
        .add-to-cart-btn {
            transition: all 0.2s ease;
        }
        
        .add-to-cart-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .loading-spinner {
            border: 3px solid #f3f4f6;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-50">
    
    <?php include('include/header.php'); ?>

    <!-- Breadcrumb -->
    <div class="bg-white border-b border-gray-200 py-3">
        <div class="container mx-auto px-4 max-w-7xl">
            <nav class="flex text-sm">
                <a href="/" class="text-gray-500 hover:text-gray-700">Home</a>
                <span class="mx-2 text-gray-400">/</span>
                <a href="product.php" class="text-gray-500 hover:text-gray-700">Products</a>
                <span class="mx-2 text-gray-400">/</span>
                <span class="text-gray-900">Compare</span>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Compare Products</h1>
                    <p id="compareCount" class="text-sm text-gray-500 mt-1">Loading products...</p>
                </div>
                <div class="flex gap-3">
                    <button id="clearAllBtn" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-md transition">
                        <i class="fas fa-trash mr-2"></i>Clear All
                    </button>
                    <a href="product.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md transition inline-block">
                        <i class="fas fa-plus mr-2"></i>Add More Products
                    </a>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="text-center py-12 bg-white rounded-lg shadow-sm">
            <div class="loading-spinner mx-auto mb-4"></div>
            <p class="text-gray-600">Loading comparison...</p>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden text-center py-12 bg-white rounded-lg shadow-sm">
            <i class="fas fa-balance-scale text-gray-300 text-6xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">No products to compare</h3>
            <p class="text-gray-500 mb-6">Add products from the products page to compare them</p>
            <a href="product.php" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-md transition inline-block">
                Browse Products
            </a>
        </div>

        <!-- Comparison Table -->
        <div id="comparisonContainer" class="hidden">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="comparison-table">
                    <table class="w-full">
                        <thead class="sticky-header">
                            <tr>
                                <th class="p-4 text-left font-semibold text-gray-700 border-b-2 border-gray-200">
                                    Feature
                                </th>
                                <th id="productHeaders" class="border-b-2 border-gray-200">
                                    <!-- Product headers will be inserted here -->
                                </th>
                            </tr>
                        </thead>
                        <tbody id="comparisonBody">
                            <!-- Comparison rows will be inserted here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Highlight Differences Toggle -->
            <div class="mt-6 bg-white rounded-lg shadow-sm p-4">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" id="highlightDifferences" class="mr-3 rounded text-blue-600 focus:ring-blue-500">
                    <span class="text-sm font-medium text-gray-700">Highlight Differences</span>
                </label>
            </div>
        </div>

    </div>

    <?php include('include/footer.php'); ?>
    <?php include('include/script.php') ?>

    <script>
        let products = [];
        let compareList = [];

        // Get product IDs from URL
        const urlParams = new URLSearchParams(window.location.search);
        const productIds = urlParams.get('ids') ? urlParams.get('ids').split(',').map(id => parseInt(id)) : [];

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            if (productIds.length === 0) {
                // Try to get from localStorage
                const stored = localStorage.getItem('compareList');
                if (stored) {
                    compareList = JSON.parse(stored);
                    if (compareList.length > 0) {
                        // Redirect with IDs in URL
                        window.location.href = `compare.php?ids=${compareList.join(',')}`;
                        return;
                    }
                }
                showEmptyState();
            } else {
                compareList = productIds;
                loadProducts();
            }
            
            setupEventListeners();
        });

        // Load products from API
        async function loadProducts() {
            try {
                // Fetch all products in one call using the new API endpoint
                const response = await fetch(`api/products.php?ids=${productIds.join(',')}`);
                const data = await response.json();
                
                console.log('API Response:', data); // Debug log
                
                if (!data.success || !data.products || data.products.length === 0) {
                    showEmptyState();
                    return;
                }
                
                products = data.products;
                
                document.getElementById('loadingState').classList.add('hidden');
                document.getElementById('comparisonContainer').classList.remove('hidden');
                
                updateCompareCount();
                renderComparison();
                
            } catch (error) {
                console.error('Error loading products:', error);
                showError();
            }
        }

        // Render comparison table
        function renderComparison() {
            renderProductHeaders();
            renderComparisonRows();
        }

        // Render product headers
        function renderProductHeaders() {
            const container = document.getElementById('productHeaders');
            
            const headers = products.map(product => {
                const inStock = product.stock === 'In Stock' || product.quantity > 0;
                
                return `
                    <th class="product-column border-b-2 border-gray-200 p-4">
                        <div class="relative">
                            <button class="remove-btn absolute top-0 right-0 bg-red-500 hover:bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center shadow-md z-10" 
                                    onclick="removeProduct(${product.id})" 
                                    title="Remove from comparison">
                                <i class="fas fa-times"></i>
                            </button>
                            
                            <a href="product_detail.php?id=${product.id}" class="block">
                                <img src="${product.image}" 
                                     alt="${product.name}" 
                                     class="w-full h-48 object-cover rounded-lg mb-3"
                                     onerror="this.onerror=null; this.src='assets/img/Products/product1.webp';">
                                
                                <div class="text-left">
                                    <p class="text-xs text-gray-500 mb-1">${product.brand || 'Generic'}</p>
                                    <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">${product.name}</h3>
                                    <p class="text-2xl font-bold text-[#CC4514] mb-3">£${parseFloat(product.price).toFixed(2)}</p>
                                </div>
                            </a>
                            
                            ${inStock ? `
                                <button class="add-to-cart-btn w-full bg-[#022658] hover:bg-[#CC4514] text-white py-2 px-4 rounded-md transition text-sm font-medium" 
                                        onclick="addToCart(${product.id}, this)">
                                    <i class="fas fa-shopping-cart mr-2"></i>Add to Cart
                                </button>
                            ` : `
                                <button disabled class="w-full bg-gray-300 text-gray-500 py-2 px-4 rounded-md text-sm cursor-not-allowed">
                                    Out of Stock
                                </button>
                            `}
                            
                            <a href="product_detail.php?id=${product.id}" 
                               class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md transition text-sm font-medium mt-2">
                                View Details
                            </a>
                        </div>
                    </th>
                `;
            }).join('');
            
            container.innerHTML = headers;
        }

        // Render comparison rows
        function renderComparisonRows() {
            const tbody = document.getElementById('comparisonBody');
            
            // Define comparison features
            const features = [
                { label: 'SKU', key: 'sku', format: (v) => v || 'N/A' },
                { label: 'Brand', key: 'brand', format: (v) => v || 'Generic' },
                { label: 'Price', key: 'price', format: (v) => `£${parseFloat(v).toFixed(2)}` },
                { label: 'Stock Status', key: 'stock', format: (v, p) => {
                    const inStock = v === 'In Stock' || p.quantity > 0;
                    return `<span class="px-2 py-1 rounded text-xs font-medium ${inStock ? 'bg-[#022658]/10 text-[#022658]' : 'bg-red-100 text-red-800'}">${inStock ? 'In Stock' : 'Out of Stock'}</span>`;
                }},
                { label: 'Quantity Available', key: 'quantity', format: (v) => v > 0 ? v : 'N/A' },
                { label: 'Description', key: 'description', format: (v) => v || 'No description available' },
                { label: 'Size/Model', key: 'size', format: (v) => v || 'N/A' },
                { label: 'Color/Type', key: 'color', format: (v) => v || 'N/A' },
                { label: 'Category', key: 'category', format: (v) => v || 'N/A' },
                { label: 'New Product', key: 'is_new', format: (v) => v == 1 ? '<i class="fas fa-check text-[#022658]"></i>' : '<i class="fas fa-times text-gray-400"></i>' },
                { label: 'Popular Product', key: 'is_popular', format: (v) => v == 1 ? '<i class="fas fa-check text-blue-600"></i>' : '<i class="fas fa-times text-gray-400"></i>' }
            ];
            
            const rows = features.map(feature => {
                const values = products.map(p => {
                    const value = p[feature.key];
                    return {
                        raw: value,
                        formatted: feature.format(value, p)
                    };
                });
                
                // Check if all values are different
                const allDifferent = new Set(values.map(v => v.raw)).size === values.length;
                
                return `
                    <tr class="feature-row" data-feature="${feature.key}">
                        <td class="p-4 font-medium text-gray-700 border-r border-gray-200 whitespace-nowrap">
                            ${feature.label}
                        </td>
                        ${values.map((v, idx) => `
                            <td class="product-column p-4 text-center border-r border-gray-200 ${allDifferent ? 'difference' : ''}">
                                ${v.formatted}
                            </td>
                        `).join('')}
                    </tr>
                `;
            }).join('');
            
            tbody.innerHTML = rows;
        }

        // Remove product from comparison
        function removeProduct(productId) {
            compareList = compareList.filter(id => id !== productId);
            localStorage.setItem('compareList', JSON.stringify(compareList));
            
            if (compareList.length === 0) {
                window.location.href = 'compare.php';
            } else {
                window.location.href = `compare.php?ids=${compareList.join(',')}`;
            }
        }

        // Clear all products
        function clearAll() {
            if (confirm('Are you sure you want to clear all products from comparison?')) {
                compareList = [];
                localStorage.setItem('compareList', JSON.stringify(compareList));
                window.location.href = 'compare.php';
            }
        }

        // Add to cart
        async function addToCart(productId, buttonElement) {
            buttonElement.disabled = true;
            const originalHTML = buttonElement.innerHTML;
            buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            
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
                    showNotification('Added to cart successfully', 'success');
                    updateCartCount(data.cart_count || data.total_items);
                    buttonElement.innerHTML = '<i class="fas fa-check mr-2"></i>Added!';
                    setTimeout(() => {
                        buttonElement.innerHTML = originalHTML;
                        buttonElement.disabled = false;
                    }, 2000);
                } else {
                    showNotification(data.message || 'Failed to add to cart', 'error');
                    buttonElement.innerHTML = originalHTML;
                    buttonElement.disabled = false;
                }
            } catch (error) {
                console.error('Error adding to cart:', error);
                showNotification('An error occurred. Please try again.', 'error');
                buttonElement.innerHTML = originalHTML;
                buttonElement.disabled = false;
            }
        }

        // Update cart count
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
        }

        // Highlight differences toggle
        function toggleHighlightDifferences(enabled) {
            const cells = document.querySelectorAll('.difference');
            cells.forEach(cell => {
                if (enabled) {
                    cell.classList.add('highlight-difference');
                } else {
                    cell.classList.remove('highlight-difference');
                }
            });
        }

        // Setup event listeners
        function setupEventListeners() {
            document.getElementById('clearAllBtn')?.addEventListener('click', clearAll);
            document.getElementById('highlightDifferences')?.addEventListener('change', (e) => {
                toggleHighlightDifferences(e.target.checked);
            });
        }

        // Update compare count
        function updateCompareCount() {
            const countElement = document.getElementById('compareCount');
            if (countElement) {
                countElement.textContent = `Comparing ${products.length} product${products.length !== 1 ? 's' : ''}`;
            }
        }

        // Show empty state
        function showEmptyState() {
            document.getElementById('loadingState').classList.add('hidden');
            document.getElementById('emptyState').classList.remove('hidden');
            document.getElementById('comparisonContainer').classList.add('hidden');
        }

        // Show error
        function showError() {
            document.getElementById('loadingState').classList.add('hidden');
            document.getElementById('comparisonContainer').classList.add('hidden');
            
            const container = document.querySelector('.container');
            container.innerHTML += `
                <div class="text-center py-12 bg-white rounded-lg shadow-sm mt-6">
                    <i class="fas fa-exclamation-triangle text-red-500 text-6xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Error Loading Products</h3>
                    <p class="text-gray-500 mb-6">An error occurred while loading the comparison</p>
                    <a href="product.php" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-md transition inline-block">
                        Back to Products
                    </a>
                </div>
            `;
        }

        // Notification system
        function showNotification(message, type = 'info') {
            const colors = {
                success: 'bg-[#022658]',
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
    </script>
</body>
</html>