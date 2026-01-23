<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - Jacksons Leisure</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.typekit.net/yzr5vmg.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="assets/css/styles.css" rel="stylesheet" />
    <?php include('include/style.php') ?>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">
    
    <?php include('include/header.php'); ?>

    <!-- Breadcrumb -->
    <div class="bg-white border-b border-gray-200 py-3">
        <div class="container mx-auto px-4 max-w-7xl">
            <nav class="flex text-sm text-gray-500">
                <a href="/" class="hover:text-gray-700">HOME</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900 font-medium">WISHLIST</span>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-grow container mx-auto px-4 py-8 max-w-7xl">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">My Wishlist</h1>
        
        <div id="wishlistContainer">
            <!-- Loading State -->
            <div id="wishlistLoading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900"></div>
                <p class="mt-4 text-gray-600">Loading your wishlist...</p>
            </div>
            
            <!-- Empty State -->
            <div id="wishlistEmpty" class="hidden text-center py-16 bg-white rounded-lg shadow-sm">
                <div class="mb-6">
                    <i class="far fa-heart text-gray-200 text-8xl"></i>
                </div>
                <h3 class="text-2xl font-semibold text-gray-700 mb-2">Your wishlist is empty</h3>
                <p class="text-gray-500 mb-8">Save items you like to your wishlist to find them easily later.</p>
                <a href="product.php" class="inline-block bg-[#0e703a] hover:bg-lime-600 text-white font-semibold px-8 py-3 rounded-md transition">
                    Continue Shopping
                </a>
            </div>
            
            <!-- Wishlist Items Grid -->
            <div id="wishlistGrid" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Populated by JavaScript -->
            </div>
        </div>
    </div>

    <?php include('include/footer.php'); ?>
    <?php include('include/script.php') ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            loadWishlist();
            
            // Re-initialize wishlist utility to handle removals here
            if (typeof Wishlist !== 'undefined') {
                const originalUpdateUI = Wishlist.updateUI;
                Wishlist.updateUI = function() {
                    originalUpdateUI.call(this);
                    // After updating heart Icons, we might want to refresh the wishlist page if items were removed
                    // but usually, removing from wishlist page is done via a dedicated button
                };
            }
        });

        async function loadWishlist() {
            const loading = document.getElementById('wishlistLoading');
            const empty = document.getElementById('wishlistEmpty');
            const grid = document.getElementById('wishlistGrid');
            
            try {
                const response = await fetch('api/wishlist.php?action=get');
                const data = await response.json();
                
                loading.classList.add('hidden');
                
                if (data.success && data.wishlist && data.wishlist.length > 0) {
                    empty.classList.add('hidden');
                    grid.classList.remove('hidden');
                    renderWishlist(data.wishlist);
                } else {
                    empty.classList.remove('hidden');
                    grid.classList.add('hidden');
                }
            } catch (error) {
                console.error('Error loading wishlist:', error);
                loading.classList.add('hidden');
                empty.classList.remove('hidden');
                grid.classList.add('hidden');
                // Could show an error message here
            }
        }

        function renderWishlist(items) {
            const grid = document.getElementById('wishlistGrid');
            grid.innerHTML = '';
            
            items.forEach(item => {
                const card = `
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition overflow-hidden flex flex-col h-full" id="wishlist-item-${item.id}">
                        <div class="relative">
                            <a href="product_detail.php?id=${item.id}">
                                <img src="${item.image || 'assets/img/Products/product1.webp'}" 
                                     alt="${item.product_name}" 
                                     class="w-full h-48 object-cover"
                                     onerror="this.onerror=null; this.src='assets/img/Products/product1.webp';">
                            </a>
                            <button class="absolute top-2 right-2 bg-white rounded-full w-8 h-8 flex items-center justify-center shadow-md hover:shadow-lg text-red-500 transition remove-item-btn" 
                                    data-wishlist-id="${item.id}"
                                    title="Remove from wishlist">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="p-4 flex flex-col flex-grow">
                            <p class="text-xs text-gray-400 uppercase mb-1">${item.category_name || 'Category'}</p>
                            <a href="product_detail.php?id=${item.id}" class="hover:text-blue-600 transition">
                                <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">${item.product_name}</h3>
                            </a>
                            <div class="mt-auto pt-4 flex flex-col gap-3">
                                <div class="text-xl font-bold text-[#CC4514]">£${parseFloat(item.price).toFixed(2)}</div>
                                <button onclick="addToCart(${item.id}, this)" class="w-full bg-[#0e703a] hover:bg-lime-600 text-white font-semibold py-2 rounded transition flex items-center justify-center gap-2">
                                    <i class="fas fa-shopping-cart text-sm"></i>
                                    <span>Add to Cart</span>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                grid.innerHTML += card;
            });
            
            // Attach event listeners for remove buttons
            document.querySelectorAll('.remove-item-btn').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    const productId = btn.dataset.wishlistId;
                    if (typeof Wishlist !== 'undefined') {
                        // Use the centralized toggle function
                        await Wishlist.toggle(productId, btn);
                        // Remove the item from the grid
                        const itemElem = document.getElementById(`wishlist-item-${productId}`);
                        if (itemElem) {
                            itemElem.style.opacity = '0';
                            itemElem.style.transform = 'scale(0.9)';
                            setTimeout(() => {
                                itemElem.remove();
                                if (document.getElementById('wishlistGrid').children.length === 0) {
                                    document.getElementById('wishlistGrid').classList.add('hidden');
                                    document.getElementById('wishlistEmpty').classList.remove('hidden');
                                }
                            }, 300);
                        }
                    }
                });
            });
        }

        async function addToCart(productId, btn) {
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;
            
            try {
                const response = await fetch('api/cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'add', product_id: productId, quantity: 1 })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    if (typeof showNotification === 'function') {
                        showNotification('Added to cart!', 'success');
                    } else {
                        alert('Added to cart!');
                    }
                    if (typeof updateCartCount === 'function') {
                        updateCartCount(data.cart_count || data.total_items);
                    }
                } else {
                    alert(data.message || 'Failed to add to cart');
                }
            } catch (error) {
                console.error('Error adding to cart:', error);
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>
