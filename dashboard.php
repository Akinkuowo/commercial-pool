<?php
session_start();
require_once('config.php');

// Redirect if not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$conn = getDbConnection();
$user_id = $_SESSION['user_id'];
$user = [];

// Fetch User Details
if ($conn) {
    $stmt = $conn->prepare("SELECT first_name, last_name, email, phone_number, address_line1, town_city, postcode, 
                                  business_name, business_type, vat_number, company_registration_no 
                           FROM users WHERE id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trader Dashboard - Jacksons Leisure</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Adobe Fonts - Myriad Pro -->
    <link rel="stylesheet" href="https://use.typekit.net/yzr5vmg.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="assets/css/styles.css" rel="stylesheet" />

    <?php include('include/style.php') ?>
    
    <style>
        .nav-link.active {
            background-color: #f3f4f6;
            color: #111827;
            border-left: 3px solid #2563eb;
        }
        .section-content {
            display: none;
        }
        .section-content.active {
            display: block;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">

    <?php include('include/header.php'); ?>

    <div class="min-h-screen container mx-auto px-4 py-8 max-w-7xl">
        
        <!-- Welcome Section -->
        <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
                <p class="text-gray-500 mt-1">Manage your orders and account details.</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="logout.php" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-2 px-4 rounded-lg transition shadow-sm">
                    <i class="fas fa-sign-out-alt mr-2"></i> Log Out
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- Sidebar Navigation -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm overflow-hidden sticky top-8">
                    <div class="p-6 bg-gray-900 text-white">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center">
                                <span class="text-lg font-bold"><?php echo substr($_SESSION['user_name'], 0, 1); ?></span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-sm"><?php echo htmlspecialchars($_SESSION['user_name']); ?></h3>
                                <p class="text-xs text-gray-400"><?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
                            </div>
                        </div>
                    </div>
                    <nav class="p-2 space-y-1">
                        <button onclick="showSection('overview')" id="nav-overview" class="nav-link w-full flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg transition text-left">
                            <i class="fas fa-home w-5 text-center"></i> Overview
                        </button>
                        <button onclick="showSection('orders')" id="nav-orders" class="nav-link w-full flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg transition text-left">
                            <i class="fas fa-box w-5 text-center"></i> My Orders
                        </button>
                        <button onclick="showSection('account')" id="nav-account" class="nav-link w-full flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg transition text-left">
                            <i class="fas fa-user-circle w-5 text-center"></i> Account Details
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-3">
                
                <!-- Overview Section -->
                <div id="section-overview" class="section-content active space-y-8">
                    <!-- Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-gray-500 text-sm font-medium">Total Spent</h3>
                                <div class="w-8 h-8 rounded-full bg-green-50 text-green-600 flex items-center justify-center">
                                    <i class="fas fa-pound-sign text-sm"></i>
                                </div>
                            </div>
                            <p class="text-2xl font-bold text-gray-900" id="stat-spent">£0.00</p>
                        </div>
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-gray-500 text-sm font-medium">Total Orders</h3>
                                <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
                                    <i class="fas fa-shopping-bag text-sm"></i>
                                </div>
                            </div>
                            <p class="text-2xl font-bold text-gray-900" id="stat-orders">0</p>
                        </div>
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-gray-500 text-sm font-medium">Pending Orders</h3>
                                <div class="w-8 h-8 rounded-full bg-orange-50 text-orange-600 flex items-center justify-center">
                                    <i class="fas fa-clock text-sm"></i>
                                </div>
                            </div>
                            <p class="text-2xl font-bold text-gray-900" id="stat-pending">0</p>
                        </div>
                    </div>
                </div>

                <!-- Orders Section -->
                <div id="section-orders" class="section-content">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                            <h2 class="text-lg font-bold text-gray-900">Order History</h2>
                             <div class="relative w-full sm:w-auto">
                                <input type="text" placeholder="Search orders..." class="w-full sm:w-64 pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                             </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-gray-600">
                                <thead class="bg-gray-50 text-gray-900 font-semibold uppercase text-xs">
                                    <tr>
                                        <th class="px-6 py-4">Order ID</th>
                                        <th class="px-6 py-4">Date</th>
                                        <th class="px-6 py-4">Status</th>
                                        <th class="px-6 py-4">Total</th>
                                        <th class="px-6 py-4">Method</th>
                                        <th class="px-6 py-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="orders-table-body" class="divide-y divide-gray-100">
                                    <!-- Populated via JS -->
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                            <i class="fas fa-spinner fa-spin mr-2"></i> Loading orders...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Account Details Section -->
                <div id="section-account" class="section-content">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-6">Account Details</h2>
                        <form class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                    <input type="text" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-gray-50" readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                    <input type="text" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-gray-50" readonly>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                    <input type="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-gray-50" readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                    <input type="tel" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-gray-50" readonly>
                                </div>
                            </div>

                            <div class="border-t border-gray-100 pt-6">
                                <h3 class="text-md font-semibold text-gray-900 mb-4">Address</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Street Address</label>
                                        <input type="text" value="<?php echo htmlspecialchars($user['address_line1'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-gray-50" readonly>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                            <input type="text" value="<?php echo htmlspecialchars($user['town_city'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-gray-50" readonly>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Postcode</label>
                                            <input type="text" value="<?php echo htmlspecialchars($user['postcode'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-gray-50" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-gray-100 pt-6">
                                <h3 class="text-md font-semibold text-gray-900 mb-4">Business Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Business Name</label>
                                        <input type="text" value="<?php echo htmlspecialchars($user['business_name'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-gray-50" readonly>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Business Type</label>
                                        <input type="text" value="<?php echo htmlspecialchars($user['business_type'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-gray-50" readonly>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">VAT Number</label>
                                        <input type="text" value="<?php echo htmlspecialchars($user['vat_number'] ?? 'N/A'); ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-gray-50" readonly>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Company Reg No</label>
                                        <input type="text" value="<?php echo htmlspecialchars($user['company_registration_no'] ?? 'N/A'); ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-gray-50" readonly>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
    
    <!-- Order Details Modal -->
    <div id="orderModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center p-6 border-b border-gray-100">
                <h3 class="text-xl font-bold text-gray-900">Order Details #<span id="modalOrderId">...</span></h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="p-6 space-y-6">
                <!-- Order Summary -->
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Date Placed</p>
                        <p class="font-medium text-gray-900" id="modalOrderDate">...</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Total Amount</p>
                        <p class="font-medium text-gray-900">£<span id="modalOrderTotal">...</span></p>
                    </div>
                    <div>
                        <p class="text-gray-500">Payment Method</p>
                        <p class="font-medium text-gray-900 capitalize" id="modalPayment">...</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Delivery Method</p>
                        <p class="font-medium text-gray-900" id="modalDelivery">...</p>
                    </div>
                </div>

                <!-- Click & Collect Info (Hidden by default) -->
                <div id="modalCollectionInfo" class="hidden bg-blue-50 p-4 rounded-lg border border-blue-100 flex items-start gap-4">
                    <div class="bg-white p-2 rounded shadow-sm">
                        <img id="modalQr" src="" alt="QR Code" class="w-24 h-24 object-contain">
                    </div>
                    <div>
                        <h4 class="font-bold text-blue-900">Collection Code</h4>
                        <p class="text-2xl font-mono font-bold text-blue-600 my-1" id="modalCollectionCode">------</p>
                        <p class="text-xs text-blue-700">Expires: <span id="modalExpires">...</span></p>
                    </div>
                </div>
                
                <!-- Items Table -->
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-700">
                            <tr>
                                <th class="px-4 py-3 font-medium">Product</th>
                                <th class="px-4 py-3 font-medium text-right">Price</th>
                                <th class="px-4 py-3 font-medium text-center">Qty</th>
                                <th class="px-4 py-3 font-medium text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody id="modalItemsBody" class="divide-y divide-gray-100">
                            <!-- Items go here -->
                        </tbody>
                    </table>
                </div>

                <!-- Shipping Address -->
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Shipping Address</h4>
                    <p class="text-gray-600 text-sm leading-relaxed" id="modalAddress">...</p>
                </div>
            </div>
            
            <div class="p-6 border-t border-gray-100 bg-gray-50 flex justify-end">
                <button onclick="closeModal()" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
                    Close
                </button>
            </div>
        </div>
    </div>

    <?php include('include/footer.php') ?>
    
    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
             // Initial Load based on hash
             const hash = window.location.hash.substring(1);
             if (hash) {
                 showSection(hash);
             } else {
                 showSection('overview');
             }

             loadOrders();
        });
        
        // Navigation Logic
        function showSection(sectionId) {
            // Update URL hash
            window.location.hash = sectionId;
            
            // Hide all sections
            document.querySelectorAll('.section-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));
            
            // Show target
            const target = document.getElementById('section-' + sectionId);
            const nav = document.getElementById('nav-' + sectionId);
            
            if (target) {
                target.classList.add('active');
                if (nav) nav.classList.add('active');
            } else {
                // Default to overview if target doesn't exist
                document.getElementById('section-overview').classList.add('active');
                document.getElementById('nav-overview').classList.add('active');
            }
        }

        async function loadOrders() {
            try {
                const response = await fetch('api/get_user_orders.php');
                const result = await response.json();
                
                const tbody = document.getElementById('orders-table-body');
                
                if (result.success) {
                    const orders = result.orders;
                    
                    // Update Stats
                    document.getElementById('stat-orders').textContent = orders.length;
                    
                    let totalSpent = 0;
                    let pending = 0;
                    
                    if (orders.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No orders found.</td></tr>`;
                        return;
                    }
                    
                    let html = '';
                    orders.forEach(order => {
                        totalSpent += parseFloat(order.total_amount);
                        if (order.status === 'Pending') pending++;
                        
                        const statusColor = getStatusColor(order.status);
                        
                        html += `
                            <tr class="hover:bg-gray-50 transition group">
                                <td class="px-6 py-4 font-medium text-gray-900">#${order.id}</td>
                                <td class="px-6 py-4">${order.formatted_date}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold ${statusColor}">
                                        ${order.status}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900">${order.formatted_total}</td>
                                <td class="px-6 py-4 capitalize text-sm text-gray-500">${(order.delivery_method || 'Delivery')}</td>
                                <td class="px-6 py-4">
                                    <button onclick="viewOrder(${order.id})" class="text-blue-600 hover:text-blue-800 font-medium text-sm border border-blue-200 hover:bg-blue-50 px-3 py-1 rounded transition">
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    
                    tbody.innerHTML = html;
                    document.getElementById('stat-spent').textContent = '£' + totalSpent.toFixed(2);
                    document.getElementById('stat-pending').textContent = pending;
                    
                } else {
                    tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-8 text-center text-red-500">Failed to load orders.</td></tr>`;
                }
            } catch (error) {
                console.error('Error loading orders:', error);
                document.getElementById('orders-table-body').innerHTML = `<tr><td colspan="6" class="px-6 py-8 text-center text-red-500">An error occurred.</td></tr>`;
            }
        }
        
        async function viewOrder(orderId) {
            const modal = document.getElementById('orderModal');
            const itemsBody = document.getElementById('modalItemsBody');
            
            // Show loading state in modal or just open
            modal.classList.remove('hidden');
            itemsBody.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center"><i class="fas fa-spinner fa-spin"></i> Loading details...</td></tr>';
            
            try {
                const response = await fetch(`api/get_order_details.php?id=${orderId}`);
                const data = await response.json();
                
                if (data.success) {
                    const order = data.order;
                    
                    document.getElementById('modalOrderId').textContent = order.id;
                    document.getElementById('modalOrderDate').textContent = order.date;
                    document.getElementById('modalOrderTotal').textContent = order.total;
                    document.getElementById('modalPayment').textContent = order.payment_method;
                    document.getElementById('modalDelivery').textContent = order.delivery_method;
                    document.getElementById('modalAddress').textContent = order.shipping_address;
                    
                    // Click & Collect
                    const collectionDiv = document.getElementById('modalCollectionInfo');
                    if (data.collection) {
                        collectionDiv.classList.remove('hidden');
                        document.getElementById('modalCollectionCode').textContent = data.collection.code;
                        document.getElementById('modalExpires').textContent = data.collection.expires_at;
                        document.getElementById('modalQr').src = data.collection.qr_url;
                    } else {
                        collectionDiv.classList.add('hidden');
                    }
                    
                    // Items
                    let itemsHtml = '';
                    data.items.forEach(item => {
                        itemsHtml += `
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 bg-gray-100 rounded border border-gray-200 overflow-hidden flex-shrink-0">
                                            <img src="${item.image}" class="w-full h-full object-cover">
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 text-sm whitespace-nowrap overflow-hidden text-ellipsis max-w-[150px]">${item.product_name}</p>
                                            <p class="text-xs text-gray-500">Qty: ${item.quantity}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right text-gray-600">£${item.price}</td>
                                <td class="px-4 py-3 text-center text-gray-900 font-medium">${item.quantity}</td>
                                <td class="px-4 py-3 text-right font-bold text-gray-900">£${item.total}</td>
                            </tr>
                        `;
                    });
                    itemsBody.innerHTML = itemsHtml;
                    
                } else {
                    alert('Failed to load details: ' + data.message);
                    closeModal();
                }
            } catch (error) {
                console.error(error);
                alert('An error occurred loading order details.');
                closeModal();
            }
        }
        
        function closeModal() {
            document.getElementById('orderModal').classList.add('hidden');
        }
        
        // Close modal on outside click
        document.getElementById('orderModal').addEventListener('click', (e) => {
            if (e.target === document.getElementById('orderModal')) {
                closeModal();
            }
        });
        
        function getStatusColor(status) {
            switch(status.toLowerCase()) {
                case 'pending': return 'bg-orange-100 text-orange-700';
                case 'completed': return 'bg-green-100 text-green-700';
                case 'ready': return 'bg-blue-100 text-blue-700';
                case 'cancelled': return 'bg-red-100 text-red-700';
                case 'processing': return 'bg-blue-100 text-blue-700';
                default: return 'bg-gray-100 text-gray-700';
            }
        }
    </script>
</body>
</html>
