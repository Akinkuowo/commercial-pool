<?php
// admin/order_details.php
session_start();
require_once '../config.php';
require_once 'include/auth_check.php';

// Check if user is logged in
checkAdminAuth();

$admin_name = $_SESSION['admin_name'] ?? 'Admin User';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
$current_page = 'orders';

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id <= 0) {
    header('Location: orders.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order_id; ?> | Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar { width: 260px; transition: all 0.3s; }
        .main-content { margin-left: 260px; transition: all 0.3s; }
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); position: fixed; z-index: 50; }
            .sidebar.mobile-open { transform: translateX(0); }
            .main-content { margin-left: 0 !important; }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Sidebar -->
    <?php include('include/sidebar.php') ?>

    <!-- Main Content -->
    <div id="mainContent" class="main-content min-h-screen">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200 sticky top-0 z-40">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <button id="mobileSidebarToggle" class="lg:hidden mr-4 text-gray-600">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <div class="flex items-center text-sm text-gray-600">
                            <a href="orders.php" class="hover:text-blue-600">Orders</a>
                            <i class="fas fa-chevron-right mx-2 text-xs"></i>
                            <span class="font-semibold text-gray-800">Order #<?php echo $order_id; ?></span>
                        </div>
                    </div>
                    <div class="relative">
                        <button id="userMenuBtn" class="flex items-center space-x-3 p-2 hover:bg-gray-100 rounded-lg">
                            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                                <?php echo strtoupper(substr($admin_name, 0, 1)); ?>
                            </div>
                            <div class="hidden md:block text-left">
                                <div class="text-sm font-semibold text-gray-700"><?php echo htmlspecialchars($admin_name); ?></div>
                                <div class="text-xs text-gray-500"><?php echo ucfirst($admin_role); ?></div>
                            </div>
                        </button>
                        <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2">
                            <a href="../api/admin/logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="p-6">
            <div id="loading" class="text-center py-12">
                <i class="fas fa-spinner fa-spin text-4xl text-blue-600"></i>
                <p class="mt-4 text-gray-600">Loading order details...</p>
            </div>

            <div id="orderDetails" class="hidden max-w-6xl mx-auto">
                <!-- Header Actions -->
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                            Order #<span id="displayOrderId"></span>
                            <span id="orderStatusBadge" class="ml-4 px-3 py-1 text-sm rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                        </h1>
                        <p class="text-gray-500 mt-1">Placed on <span id="orderDate"></span></p>
                    </div>
                    
                    <div class="flex space-x-3">
                        <select id="updateStatusSelect" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="on_hold">On Hold</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="refunded">Refunded</option>
                        </select>
                        <button onclick="updateStatus()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                            Update
                        </button>
                        <button onclick="printOrder()" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition">
                            <i class="fas fa-print"></i>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column: Items & Totals -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Order Items -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-800">Order Items</h2>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left">
                                    <thead class="bg-gray-50 border-b border-gray-200">
                                        <tr>
                                            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Product</th>
                                            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Price</th>
                                            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Qty</th>
                                            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsTableBody" class="divide-y divide-gray-100">
                                        <!-- Items populated via JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Order Totals -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <div class="flex justify-end">
                                <div class="w-full md:w-1/2 lg:w-1/3 space-y-3">
                                    <div class="flex justify-between text-gray-600">
                                        <span>Subtotal</span>
                                        <span id="orderSubtotal">£0.00</span>
                                    </div>
                                    <div class="flex justify-between text-gray-600">
                                        <span>Shipping</span>
                                        <span id="orderShipping">£0.00</span>
                                    </div>
                                    <div class="border-t border-gray-200 pt-3 flex justify-between font-bold text-lg text-gray-800">
                                        <span>Total</span>
                                        <span id="orderTotal">£0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Customer & Info -->
                    <div class="space-y-6">
                        <!-- Customer Info -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Customer Details</h2>
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center text-gray-500 mr-4">
                                    <i class="fas fa-user text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900" id="custName"></h3>
                                    <p class="text-sm text-gray-500">Customer</p>
                                </div>
                            </div>
                            <div class="space-y-3 text-sm">
                                <div class="flex items-start">
                                    <i class="fas fa-envelope w-5 text-gray-400 mt-1"></i>
                                    <a href="#" id="custEmail" class="text-blue-600 hover:underline ml-2"></a>
                                </div>
                                <div class="flex items-start">
                                    <i class="fas fa-phone w-5 text-gray-400 mt-1"></i>
                                    <span id="custPhone" class="text-gray-600 ml-2"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Shipping Address -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Shipping Address</h2>
                            <p id="shippingAddress" class="text-sm text-gray-600 whitespace-pre-line leading-relaxed"></p>
                        </div>
                        
                        <!-- Payment Info -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Payment Info</h2>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Method:</span>
                                    <span id="paymentMethod" class="font-medium"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Status:</span>
                                    <span id="paymentStatus" class="px-2 py-0.5 rounded text-xs font-medium bg-gray-100"></span>
                                </div>
                                <div class="mt-4 pt-3 border-t border-gray-100">
                                    <div class="flex flex-col space-y-2">
                                        <select id="updatePaymentSelect" class="border border-gray-300 rounded px-2 py-1 text-xs">
                                            <option value="pending">Pending</option>
                                            <option value="paid">Paid</option>
                                            <option value="failed">Failed</option>
                                            <option value="refunded">Refunded</option>
                                        </select>
                                        <button onclick="updatePaymentStatus()" class="bg-gray-800 text-white text-xs px-3 py-1.5 rounded hover:bg-gray-700 transition">
                                            Update Payment Status
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-4 right-4 bg-gray-800 text-white px-6 py-3 rounded-lg shadow-lg transform translate-y-full opacity-0 transition-all duration-300 z-50">
        <div class="flex items-center space-x-3">
            <i id="toastIcon" class="fas fa-check-circle text-green-400"></i>
            <span id="toastMessage">Action successful</span>
        </div>
    </div>

    <script>
        const orderId = <?php echo $order_id; ?>;
        
        // User menu toggle
        document.getElementById('userMenuBtn').addEventListener('click', function() {
            document.getElementById('userMenu').classList.toggle('hidden');
        });

        // Mobile sidebar toggle
        document.getElementById('mobileSidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('mobile-open');
        });

        function loadOrder() {
            fetch(`../api/admin/get_order.php?id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayOrder(data.order, data.items);
                    } else {
                        showToast(data.message, 'error');
                        setTimeout(() => window.location.href = 'orders.php', 2000);
                    }
                    document.getElementById('loading').classList.add('hidden');
                    document.getElementById('orderDetails').classList.remove('hidden');
                })
                .catch(err => {
                    console.error(err);
                    showToast('Failed to load order', 'error');
                });
        }

        function displayOrder(order, items) {
            // Header
            document.getElementById('displayOrderId').textContent = order.id;
            document.getElementById('orderDate').textContent = new Date(order.created_at).toLocaleString();
            
            const statusBadge = document.getElementById('orderStatusBadge');
            statusBadge.textContent = order.status.charAt(0).toUpperCase() + order.status.slice(1);
            statusBadge.className = `ml-4 px-3 py-1 text-sm rounded-full ${getStatusColor(order.status)}`;
            
            document.getElementById('updateStatusSelect').value = order.status;

            // Customer
            document.getElementById('custName').textContent = order.customer_name;
            document.getElementById('custEmail').textContent = order.customer_email;
            document.getElementById('custEmail').href = `mailto:${order.customer_email}`;
            document.getElementById('custPhone').textContent = order.customer_phone || 'N/A';
            document.getElementById('shippingAddress').textContent = order.shipping_address;

            // Payment
            document.getElementById('paymentMethod').textContent = order.payment_method || 'N/A';
            const payStatus = document.getElementById('paymentStatus');
            payStatus.textContent = order.payment_status.charAt(0).toUpperCase() + order.payment_status.slice(1);
            payStatus.className = `px-2 py-0.5 rounded text-xs font-medium ${getPaymentColor(order.payment_status)}`;
            document.getElementById('updatePaymentSelect').value = order.payment_status;

            // Items
            const tbody = document.getElementById('itemsTableBody');
            tbody.innerHTML = '';
            
            if (items.length === 0) {
                // If no items in DB, show fallback row
                tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No items found (Legacy order or database mismatch)</td></tr>';
            } else {
                items.forEach(item => {
                    const price = parseFloat(item.price);
                    const qty = parseInt(item.quantity);
                    const total = price * qty;
                    
                    const name = item.product_name || 'Product #' + item.product_id;
                    const image = item.image ? `<img src="../${item.image}" class="w-8 h-8 rounded object-cover mr-3">` : '';

                    const html = `
                        <tr>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    ${image}
                                    <span class="font-medium text-gray-900">${name}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600">£${price.toFixed(2)}</td>
                            <td class="px-6 py-4 text-gray-600">${qty}</td>
                            <td class="px-6 py-4 text-right font-medium text-gray-900">£${total.toFixed(2)}</td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', html);
                });
            }

            // Totals
            // Note: If items are missing, we still rely on order.total_amount
            document.getElementById('orderTotal').textContent = '£' + parseFloat(order.total_amount).toFixed(2);
            document.getElementById('orderSubtotal').textContent = '£' + parseFloat(order.total_amount).toFixed(2); // Simplified
        }

        function getStatusColor(status) {
            switch(status) {
                case 'completed': return 'bg-green-100 text-green-800';
                case 'processing': return 'bg-blue-100 text-blue-800';
                case 'cancelled': return 'bg-red-100 text-red-800';
                case 'refunded': return 'bg-purple-100 text-purple-800';
                default: return 'bg-yellow-100 text-yellow-800';
            }
        }

        function getPaymentColor(status) {
            switch(status) {
                case 'paid': return 'bg-green-100 text-green-800';
                case 'failed': return 'bg-red-100 text-red-800';
                case 'refunded': return 'bg-purple-100 text-purple-800';
                default: return 'bg-yellow-100 text-yellow-800';
            }
        }

        function updateStatus() {
            const status = document.getElementById('updateStatusSelect').value;
            fetch('../api/admin/update_order_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ order_id: orderId, status: status })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    showToast('Status updated successfully');
                    loadOrder();
                } else {
                    showToast(data.message, 'error');
                }
            });
        }

        function updatePaymentStatus() {
            const status = document.getElementById('updatePaymentSelect').value;
            fetch('../api/admin/update_payment_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ order_id: orderId, status: status })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    showToast('Payment status updated successfully');
                    loadOrder();
                } else {
                    showToast(data.message, 'error');
                }
            });
        }

        function printOrder() {
            window.print();
        }

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const icon = document.getElementById('toastIcon');
            const msg = document.getElementById('toastMessage');

            msg.textContent = message;
            if (type === 'success') {
                icon.className = 'fas fa-check-circle text-green-400';
            } else {
                icon.className = 'fas fa-exclamation-circle text-red-400';
            }

            toast.classList.remove('translate-y-full', 'opacity-0');
            setTimeout(() => {
                toast.classList.add('translate-y-full', 'opacity-0');
            }, 3000);
        }

        loadOrder();
    </script>

    <style>
        @media print {
            .sidebar, header, .no-print { display: none !important; }
            .main-content { margin: 0 !important; }
            #orderDetails { max-width: 100%; }
        }
    </style>
</body>
</html>
