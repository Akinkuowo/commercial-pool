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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Commercial Pool Equipment</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="assets/css/styles.css" rel="stylesheet" />

    <?php include('include/style.php') ?>
    
    <style>
        .quantity-input {
            -moz-appearance: textfield;
        }
        
        .quantity-input::-webkit-outer-spin-button,
        .quantity-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        
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
        
        .cart-item {
            transition: all 0.3s ease;
        }
        
        .cart-item.removing {
            opacity: 0;
            transform: translateX(-100px);
        }
        
        .loading-overlay {
            background: rgba(255, 255, 255, 0.9);
        }

        /* Confirmation Modal Styles */
        .modal-enter {
            animation: modalFadeIn 0.3s ease-out;
        }

        .modal-leave {
            animation: modalFadeOut 0.2s ease-in;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes modalFadeOut {
            from {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
            to {
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
            }
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
                <span class="text-gray-900">Shopping Cart</span>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Shopping Cart</h1>

        <!-- Loading State -->
        <div id="loadingState" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900"></div>
            <p class="mt-4 text-gray-600">Loading your cart...</p>
        </div>

        <!-- Empty Cart State -->
        <div id="emptyCart" class="hidden bg-white rounded-lg shadow-sm p-12 text-center">
            <i class="fas fa-shopping-cart text-gray-300 text-6xl mb-4"></i>
            <h2 class="text-2xl font-semibold text-gray-700 mb-2">Your cart is empty</h2>
            <p class="text-gray-500 mb-6">Looks like you haven't added any items to your cart yet.</p>
            <a href="product.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg transition">
                Continue Shopping
            </a>
        </div>

        <!-- Cart Content -->
        <div id="cartContent" class="hidden">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Cart Items -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-semibold text-gray-900">
                                    Cart Items (<span id="totalItems">0</span>)
                                </h2>
                                <button id="clearCartBtn" class="text-red-600 hover:text-red-700 text-sm font-medium transition">
                                    Clear Cart
                                </button>
                            </div>
                        </div>
                        
                        <div id="cartItemsContainer" class="divide-y divide-gray-200">
                            <!-- Cart items will be inserted here -->
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-sm p-6 sticky top-4">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Order Summary</h2>
                        
                        <div class="space-y-4 mb-6">
                            <div class="flex justify-between text-gray-700">
                                <span>Subtotal</span>
                                <span id="summarySubtotal" class="font-medium">£0.00</span>
                            </div>
                            
                            <div class="flex justify-between text-gray-700">
                                <span>Shipping</span>
                                <span id="summaryShipping" class="font-medium">£0.00</span>
                            </div>
                            
                            <div class="flex justify-between text-gray-700">
                                <span>Tax (20%)</span>
                                <span id="summaryTax" class="font-medium">£0.00</span>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-4">
                                <div class="flex justify-between text-lg font-bold text-gray-900">
                                    <span>Total</span>
                                    <span id="summaryTotal">£0.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Promo Code -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Promo Code</label>
                            <div class="flex gap-2">
                                <input type="text" id="promoCode" placeholder="Enter code" 
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                <button id="applyPromoBtn" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-md text-sm transition">
                                    Apply
                                </button>
                            </div>
                        </div>

                        <!-- Shipping Info -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-truck text-blue-600 mt-1"></i>
                                <div class="text-sm">
                                    <p class="font-medium text-gray-900 mb-1">Free Shipping</p>
                                    <p class="text-gray-600" id="shippingMessage">Add £<span id="freeShippingRemaining">0.00</span> more for free shipping</p>
                                </div>
                            </div>
                        </div>

                        <!-- Checkout Button -->
                        <button id="checkoutBtn" class="w-full bg-[#022658] hover:bg-[#011a3d] text-white font-semibold py-3 px-6 rounded-lg transition mb-3">
                            Proceed to Checkout
                        </button>
                        
                        <a href="product.php" class="block text-center text-blue-600 hover:text-blue-700 text-sm font-medium">
                            Continue Shopping
                        </a>

                        <!-- Payment Methods -->
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <p class="text-xs text-gray-500 text-center mb-3">We Accept</p>
                            <div class="flex items-center justify-center gap-3">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg" alt="Visa" class="h-6">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="Mastercard" class="h-6">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg" alt="PayPal" class="h-6">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/f/fa/Apple_logo_black.svg" alt="Apple Pay" class="h-6">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- You May Also Like -->
            <div id="recommendedProducts" class="mt-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">You May Also Like</h2>
                <div id="recommendedProductsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Recommended products will be inserted here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden flex items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="p-6 text-center">
                <!-- Modal Icon -->
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                </div>
                
                <!-- Modal Content -->
                <h3 id="modalTitle" class="text-xl font-semibold text-gray-900 mb-2"></h3>
                <div class="mt-2 mb-6">
                    <p id="modalMessage" class="text-gray-600"></p>
                </div>
                
                <!-- Modal Actions -->
                <div class="flex items-center justify-center gap-3">
                    <button id="modalCancelBtn" 
                            type="button" 
                            class="cancel-btn flex-1 px-5 py-2.5 bg-gray-100 text-gray-800 text-sm font-medium rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300 transition duration-200">
                        Cancel
                    </button>
                    <button id="modalConfirmBtn" 
                            type="button" 
                            class="confirm-btn flex-1 px-5 py-2.5 text-white text-sm font-medium rounded-lg hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-opacity-50 transition duration-200">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include('include/footer.php'); ?>
    <?php include('include/script.php') ?>

    <script>
        let cart = {
            items: [],
            subtotal: 0,
            shipping: 0,
            tax: 0,
            total: 0,
            currencySymbol: '£',
            exchangeRate: 1.0
        };

        const FREE_SHIPPING_THRESHOLD = 600;
        const SHIPPING_COST = 25;
        const TAX_RATE = 0.20;

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadCart();
            loadRecommendedProducts();
            setupEventListeners();
        });

        function setupEventListeners() {
            document.getElementById('clearCartBtn')?.addEventListener('click', clearCart);
            document.getElementById('checkoutBtn')?.addEventListener('click', proceedToCheckout);
            document.getElementById('applyPromoBtn')?.addEventListener('click', applyPromoCode);
            
            // Close modal when clicking outside
            document.getElementById('confirmationModal')?.addEventListener('click', (e) => {
                if (e.target.id === 'confirmationModal') {
                    hideModal();
                }
            });
        }

        // Load cart from API
        async function loadCart() {
            try {
                const response = await fetch('api/cart.php');
                const data = await response.json();

                // Debug: Log the response
                console.log('Cart API Response:', data);

                document.getElementById('loadingState').classList.add('hidden');

                if (data.success && data.cart && data.cart.length > 0) {
                    cart.items = data.cart;
                    cart.subtotal = parseFloat(data.subtotal || 0); // Keep base GBP subtotal for logic
                    cart.currencySymbol = data.currency || '£';
                    cart.exchangeRate = data.rate || 1.0;
                    
                    document.getElementById('emptyCart').classList.add('hidden');
                    document.getElementById('cartContent').classList.remove('hidden');
                    
                    renderCartItems();
                    updateOrderSummary();
                    updateCartBadge(data.total_items);
                } else {
                    console.log('No cart items found or empty cart');
                    showEmptyCart();
                }
            } catch (error) {
                console.error('Error loading cart:', error);
                document.getElementById('loadingState').classList.add('hidden');
                showNotification('Failed to load cart. Please refresh the page.', 'error');
            }
        }

        function showEmptyCart() {
            document.getElementById('emptyCart').classList.remove('hidden');
            document.getElementById('cartContent').classList.add('hidden');
            updateCartBadge(0);
        }

        function renderCartItems() {
            const container = document.getElementById('cartItemsContainer');
            container.innerHTML = '';

            cart.items.forEach(item => {
                const itemHTML = `
                    <div class="cart-item p-6" data-product-id="${item.product_id}">
                        <div class="flex flex-col sm:flex-row gap-4">
                            <!-- Product Image -->
                            <div class="flex-shrink-0">
                                <img src="${item.image}" alt="${item.name}" 
                                     class="w-24 h-24 object-cover rounded-lg"
                                     onerror="this.onerror=null; this.src='assets/img/Products/product1.webp';">
                            </div>

                            <!-- Product Details -->
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h3 class="font-semibold text-gray-900 mb-1">
                                            <a href="product_detail.php?id=${item.product_id}" class="hover:text-blue-600">
                                                ${item.name}
                                            </a>
                                        </h3>
                                        ${item.sku ? `<p class="text-sm text-gray-500">SKU: ${item.sku}</p>` : ''}
                                    </div>
                                    <button class="remove-item text-gray-400 hover:text-red-600 transition" 
                                            data-product-id="${item.product_id}"
                                            title="Remove item">
                                        <i class="fas fa-times text-xl"></i>
                                    </button>
                                </div>

                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                    <!-- Quantity Controls -->
                                    <div class="flex items-center gap-3">
                                        <span class="text-sm text-gray-600">Quantity:</span>
                                        <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
                                            <button class="decrease-qty px-3 py-2 hover:bg-gray-100 transition" 
                                                    data-product-id="${item.product_id}">
                                                <i class="fas fa-minus text-gray-600 text-sm"></i>
                                            </button>
                                            <input type="number" 
                                                   class="quantity-input w-16 text-center border-x border-gray-300 py-2 focus:outline-none" 
                                                   value="${item.quantity}" 
                                                   min="1" 
                                                   max="${item.max_quantity}"
                                                   data-product-id="${item.product_id}">
                                            <button class="increase-qty px-3 py-2 hover:bg-gray-100 transition" 
                                                    data-product-id="${item.product_id}">
                                                <i class="fas fa-plus text-gray-600 text-sm"></i>
                                            </button>
                                        </div>
                                        ${item.max_quantity <= 5 ? 
                                            `<span class="text-xs text-orange-600">Only ${item.max_quantity} left</span>` : ''}
                                    </div>

                                    <!-- Price -->
                                    <div class="text-right">
                                        <p class="text-sm text-gray-500 line-through">
                                            ${cart.currencySymbol}${((parseFloat(item.price) * 1.17) * cart.exchangeRate).toFixed(2)}
                                        </p>
                                        <p class="text-lg font-bold text-gray-900">${item.formatted_subtotal}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                            </div>
                        </div>
                        
                        <!-- Delivery Method Badge -->
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            ${item.delivery_method === 'collection' 
                                ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"><i class="fas fa-store mr-1"></i> Click & Collect</span>' 
                                : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#022658]/10 text-[#022658]"><i class="fas fa-truck mr-1"></i> Home Delivery</span>'}
                        </div>
                    </div>
                `;
                container.innerHTML += itemHTML;
            });

            // Attach event listeners to cart items
            attachCartItemListeners();

            // Update total items count
            const totalItems = cart.items.reduce((sum, item) => sum + parseInt(item.quantity), 0);
            document.getElementById('totalItems').textContent = totalItems;
        }

        function attachCartItemListeners() {
            // Remove buttons
            document.querySelectorAll('.remove-item').forEach(btn => {
                btn.addEventListener('click', () => removeItem(parseInt(btn.dataset.productId)));
            });

            // Quantity decrease buttons
            document.querySelectorAll('.decrease-qty').forEach(btn => {
                btn.addEventListener('click', () => {
                    const input = document.querySelector(`.quantity-input[data-product-id="${btn.dataset.productId}"]`);
                    if (input && parseInt(input.value) > 1) {
                        input.value = parseInt(input.value) - 1;
                        updateQuantity(parseInt(btn.dataset.productId), parseInt(input.value));
                    }
                });
            });

            // Quantity increase buttons
            document.querySelectorAll('.increase-qty').forEach(btn => {
                btn.addEventListener('click', () => {
                    const input = document.querySelector(`.quantity-input[data-product-id="${btn.dataset.productId}"]`);
                    const max = parseInt(input.max);
                    if (input && parseInt(input.value) < max) {
                        input.value = parseInt(input.value) + 1;
                        updateQuantity(parseInt(btn.dataset.productId), parseInt(input.value));
                    } else if (input && parseInt(input.value) >= max) {
                        showNotification(`Only ${max} available in stock`, 'warning');
                    }
                });
            });

            // Quantity input change
            document.querySelectorAll('.quantity-input').forEach(input => {
                input.addEventListener('change', () => {
                    let value = parseInt(input.value);
                    const max = parseInt(input.max);
                    
                    if (value < 1) value = 1;
                    if (value > max) {
                        value = max;
                        showNotification(`Only ${max} available in stock`, 'warning');
                    }
                    
                    input.value = value;
                    updateQuantity(parseInt(input.dataset.productId), value);
                });
            });
        }

        async function updateQuantity(productId, quantity) {
            try {
                const response = await fetch('api/cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'update',
                        product_id: productId,
                        quantity: quantity
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Reload cart to get updated data
                    await loadCart();
                } else {
                    showNotification(data.message || 'Failed to update quantity', 'error');
                    await loadCart(); // Reload to restore correct state
                }
            } catch (error) {
                console.error('Error updating quantity:', error);
                showNotification('An error occurred. Please try again.', 'error');
            }
        }

        // Confirmation Modal Functions
        function showConfirmationModal(title, message, confirmCallback, confirmText = 'Confirm', cancelText = 'Cancel', type = 'warning') {
            const modal = document.getElementById('confirmationModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalMessage = document.getElementById('modalMessage');
            const confirmBtn = document.getElementById('modalConfirmBtn');
            const cancelBtn = document.getElementById('modalCancelBtn');
            const modalIcon = modal.querySelector('.mx-auto');
            
            // Set modal content
            modalTitle.textContent = title;
            modalMessage.textContent = message;
            confirmBtn.textContent = confirmText;
            cancelBtn.textContent = cancelText;
            
            // Style based on type
            if (type === 'warning') {
                modalIcon.className = 'mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4';
                modalIcon.innerHTML = '<i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>';
                confirmBtn.className = 'confirm-btn flex-1 px-5 py-2.5 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50 transition duration-200';
            } else if (type === 'info') {
                modalIcon.className = 'mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 mb-4';
                modalIcon.innerHTML = '<i class="fas fa-info-circle text-blue-600 text-2xl"></i>';
                confirmBtn.className = 'confirm-btn flex-1 px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition duration-200';
            } else if (type === 'success') {
                modalIcon.className = 'mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-[#022658]/10 mb-4';
                modalIcon.innerHTML = '<i class="fas fa-check-circle text-[#022658] text-2xl"></i>';
                confirmBtn.className = 'confirm-btn flex-1 px-5 py-2.5 bg-[#022658] text-white text-sm font-medium rounded-lg hover:bg-[#011a3d] focus:outline-none focus:ring-2 focus:ring-[#022658] focus:ring-opacity-50 transition duration-200';
            }
            
            // Show modal with animation
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.add('modal-enter');
            }, 10);
            
            // Set up event listeners (remove old ones first)
            const handleConfirm = () => {
                confirmCallback();
                hideModal();
            };
            
            const handleCancel = () => {
                hideModal();
            };
            
            const handleKeyDown = (e) => {
                if (e.key === 'Escape') {
                    hideModal();
                } else if (e.key === 'Enter') {
                    handleConfirm();
                }
            };
            
            // Remove existing listeners
            confirmBtn.replaceWith(confirmBtn.cloneNode(true));
            cancelBtn.replaceWith(cancelBtn.cloneNode(true));
            
            // Get fresh references
            const newConfirmBtn = document.getElementById('modalConfirmBtn');
            const newCancelBtn = document.getElementById('modalCancelBtn');
            
            // Add new listeners
            newConfirmBtn.addEventListener('click', handleConfirm);
            newCancelBtn.addEventListener('click', handleCancel);
            document.addEventListener('keydown', handleKeyDown);
            
            // Focus the cancel button for accessibility
            newCancelBtn.focus();
            
            // Store cleanup function
            modal._cleanup = () => {
                document.removeEventListener('keydown', handleKeyDown);
            };
        }

        function hideModal() {
            const modal = document.getElementById('confirmationModal');
            
            // Add leave animation
            modal.classList.remove('modal-enter');
            modal.classList.add('modal-leave');
            
            // Clean up event listeners after animation
            setTimeout(() => {
                if (modal._cleanup) {
                    modal._cleanup();
                    delete modal._cleanup;
                }
                
                modal.classList.remove('modal-leave');
                modal.classList.add('hidden');
            }, 200);
        }

        async function removeItem(productId) {
            const item = cart.items.find(item => item.product_id === productId);
            const itemName = item ? item.name : 'this item';
            
            showConfirmationModal(
                'Remove Item',
                `Are you sure you want to remove "${itemName}" from your cart?`,
                () => performRemoveItem(productId),
                'Remove Item',
                'Keep Item',
                'warning'
            );
        }

        async function performRemoveItem(productId) {
            const cartItem = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
            if (cartItem) {
                cartItem.classList.add('removing');
            }

            try {
                const response = await fetch('api/cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'remove',
                        product_id: productId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showNotification(data.message || 'Item removed from cart', 'success');
                    
                    setTimeout(async () => {
                        await loadCart();
                    }, 300);
                } else {
                    showNotification(data.message || 'Failed to remove item', 'error');
                    if (cartItem) {
                        cartItem.classList.remove('removing');
                    }
                }
            } catch (error) {
                console.error('Error removing item:', error);
                showNotification('An error occurred. Please try again.', 'error');
                if (cartItem) {
                    cartItem.classList.remove('removing');
                }
            }
        }

        async function clearCart() {
            if (cart.items.length === 0) {
                showNotification('Your cart is already empty', 'info');
                return;
            }
            
            const itemCount = cart.items.reduce((sum, item) => sum + parseInt(item.quantity), 0);
            const itemText = itemCount === 1 ? '1 item' : `${itemCount} items`;
            
            showConfirmationModal(
                'Clear Cart',
                `Are you sure you want to remove all ${itemText} from your cart? This action cannot be undone.`,
                () => performClearCart(),
                'Clear All Items',
                'Keep Items',
                'warning'
            );
        }

        async function performClearCart() {
            const btn = document.getElementById('clearCartBtn');
            const originalText = btn.textContent;
            btn.textContent = 'Clearing...';
            btn.disabled = true;
            btn.classList.add('btn-loading');

            try {
                const response = await fetch('api/cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'clear'
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showNotification('Cart cleared successfully', 'success');
                    setTimeout(() => {
                        showEmptyCart();
                    }, 300);
                } else {
                    showNotification(data.message || 'Failed to clear cart', 'error');
                }
            } catch (error) {
                console.error('Error clearing cart:', error);
                showNotification('An error occurred. Please try again.', 'error');
            } finally {
                btn.textContent = originalText;
                btn.disabled = false;
                btn.classList.remove('btn-loading');
            }
        }

        function updateOrderSummary() {
            const subtotal = cart.subtotal;
            const shipping = subtotal >= FREE_SHIPPING_THRESHOLD ? 0 : SHIPPING_COST;
            const tax = subtotal * TAX_RATE;
            const total = subtotal + shipping + tax;

            // Convert and format for display
            const rate = cart.exchangeRate;
            const symbol = cart.currencySymbol;

            document.getElementById('summarySubtotal').textContent = `${symbol}${(subtotal * rate).toFixed(2)}`;
            document.getElementById('summaryShipping').textContent = shipping === 0 ? 'FREE' : `${symbol}${(shipping * rate).toFixed(2)}`;
            document.getElementById('summaryTax').textContent = `${symbol}${(tax * rate).toFixed(2)}`;
            document.getElementById('summaryTotal').textContent = `${symbol}${(total * rate).toFixed(2)}`;

            // Update free shipping message (threshold is in GBP)
            if (subtotal >= FREE_SHIPPING_THRESHOLD) {
                document.getElementById('shippingMessage').innerHTML = '<span class="text-[#022658] font-medium">You qualify for free shipping!</span>';
            } else {
                const remaining = FREE_SHIPPING_THRESHOLD - subtotal;
                const rate = cart.exchangeRate;
                const symbol = cart.currencySymbol;
                document.getElementById('freeShippingRemaining').textContent = `${symbol}${(remaining * rate).toFixed(2)}`;
            }
        }

        function updateCartBadge(count) {
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

        async function loadRecommendedProducts() {
            try {
                const response = await fetch('api/products.php?limit=4');
                const data = await response.json();

                if (data.success && data.products && data.products.length > 0) {
                    renderRecommendedProducts(data.products);
                }
            } catch (error) {
                console.error('Error loading recommended products:', error);
            }
        }

        function renderRecommendedProducts(products) {
            const container = document.getElementById('recommendedProductsGrid');
            container.innerHTML = '';

            products.forEach(product => {
                const inStock = product.stock === 'In Stock' || product.quantity > 0;
                const productHTML = `
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition overflow-hidden">
                        <div class="relative">
                            <a href="product_detail.php?id=${product.id}">
                                <img src="${product.image}" alt="${product.name}" 
                                     class="w-full h-48 object-cover"
                                     onerror="this.onerror=null; this.src='assets/img/Products/product1.webp';">
                            </a>
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">
                                <a href="product_detail.php?id=${product.id}" class="hover:text-blue-600">
                                    ${product.name}
                                </a>
                            </h3>
                            <div class="flex justify-between items-center">
                                <span class="text-xl font-bold text-gray-900">£${parseFloat(product.price).toFixed(2)}</span>
                                ${inStock ? `
                                    <button class="add-to-cart-quick bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm transition" 
                                            data-product-id="${product.id}">
                                        Add to Cart
                                    </button>
                                ` : `
                                    <span class="text-sm text-gray-500">Out of Stock</span>
                                `}
                            </div>
                        </div>
                    </div>
                `;
                container.innerHTML += productHTML;
            });

            // Attach event listeners to add to cart buttons
            document.querySelectorAll('.add-to-cart-quick').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const productId = parseInt(btn.dataset.productId);
                    const originalText = btn.textContent;
                    btn.textContent = 'Adding...';
                    btn.disabled = true;

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
                            showNotification('Added to cart!', 'success');
                            await loadCart();
                        } else {
                            showNotification(data.message || 'Failed to add to cart', 'error');
                        }
                    } catch (error) {
                        console.error('Error adding to cart:', error);
                        showNotification('An error occurred. Please try again.', 'error');
                    } finally {
                        btn.textContent = originalText;
                        btn.disabled = false;
                    }
                });
            });
        }

        function applyPromoCode() {
            const code = document.getElementById('promoCode').value.trim();
            if (!code) {
                showNotification('Please enter a promo code', 'warning');
                return;
            }

            // Simulate promo code validation
            showNotification('Promo code feature coming soon!', 'info');
        }

        function proceedToCheckout() {
            if (cart.items.length === 0) {
                showNotification('Your cart is empty', 'warning');
                return;
            }

            // Redirect to checkout page
            window.location.href = 'checkout.php';
        }

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

        // Optional: Show success modal for important actions (optional enhancement)
        function showSuccessModal(title, message, buttonText = 'OK') {
            showConfirmationModal(
                title,
                message,
                () => {},
                buttonText,
                '',
                'success'
            );
            
            // Hide cancel button for success modals
            const cancelBtn = document.getElementById('modalCancelBtn');
            if (cancelBtn) {
                cancelBtn.style.display = 'none';
            }
            
            // Center the confirm button
            const confirmBtn = document.getElementById('modalConfirmBtn');
            if (confirmBtn) {
                confirmBtn.className = confirmBtn.className.replace('flex-1', 'px-8');
            }
        }
    </script>

</body>
</html>