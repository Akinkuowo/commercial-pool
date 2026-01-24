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
$is_trader = isset($_SESSION['trader_account']) && $_SESSION['trader_account'] === true;

// Fetch User Details with trader-specific fields
if ($conn) {
    $stmt = $conn->prepare("SELECT first_name, last_name, email, phone_number, address_line1, town_city, postcode, 
                                  business_name, business_type, vat_number, company_registration_no,
                                  credit_limit, payment_terms, price_tier, approved_by_admin
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
    <title><?php echo $is_trader ? 'Trade Dashboard' : 'Customer Dashboard'; ?> - Commercial Pool Equipment</title>
    
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
        .badge-trader {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .badge-residential {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">

    <?php include('include/header.php'); ?>

    <div class="min-h-screen container mx-auto px-4 py-8 max-w-7xl">
        
        <!-- Welcome Section -->
        <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!
                    <?php if ($is_trader): ?>
                        <span class="ml-2 text-sm px-3 py-1 rounded-full badge-trader">TRADE ACCOUNT</span>
                    <?php endif; ?>
                </h1>
                <p class="text-gray-500 mt-1">
                    <?php echo $is_trader ? 'Manage your trade orders, quotes, and account.' : 'Manage your orders and pool profile.'; ?>
                </p>
            </div>
            <div class="mt-4 md:mt-0 flex gap-3">
                <?php if ($is_trader): ?>
                    <a href="quick-order.php" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition shadow-sm flex items-center gap-2">
                        <i class="fas fa-bolt"></i> Quick Order
                    </a>
                <?php endif; ?>
                <a href="logout.php" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-2 px-4 rounded-lg transition shadow-sm">
                    <i class="fas fa-sign-out-alt mr-2"></i> Log Out
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- Sidebar Navigation -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm overflow-hidden sticky top-8">
                    <div class="p-6 <?php echo $is_trader ? 'bg-gradient-to-r from-blue-900 to-purple-800' : 'bg-gray-900'; ?> text-white">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full <?php echo $is_trader ? 'bg-purple-600' : 'bg-gray-700'; ?> flex items-center justify-center">
                                <span class="text-lg font-bold"><?php echo substr($_SESSION['user_name'], 0, 1); ?></span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-sm"><?php echo htmlspecialchars($_SESSION['user_name']); ?></h3>
                                <p class="text-xs text-gray-300"><?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
                                <?php if ($is_trader && isset($user['price_tier'])): ?>
                                    <p class="text-xs text-yellow-200 mt-1">Price Tier: <?php echo htmlspecialchars($user['price_tier']); ?></p>
                                <?php endif; ?>
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
                        <?php if ($is_trader): ?>
                        <button onclick="showSection('quotes')" id="nav-quotes" class="nav-link w-full flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg transition text-left">
                            <i class="fas fa-file-invoice-dollar w-5 text-center"></i> Quotes
                        </button>
                        <button onclick="showSection('quick-order')" id="nav-quick-order" class="nav-link w-full flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg transition text-left">
                            <i class="fas fa-bolt w-5 text-center"></i> Quick Order Form
                        </button>
                        <?php endif; ?>
                        <button onclick="showSection('pool-profile')" id="nav-pool-profile" class="nav-link w-full flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg transition text-left">
                            <i class="fas fa-swimming-pool w-5 text-center"></i> My Pool Profile
                        </button>
                        <button onclick="showSection('account')" id="nav-account" class="nav-link w-full flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg transition text-left">
                            <i class="fas fa-user-circle w-5 text-center"></i> Account Details
                        </button>
                        <?php if ($is_trader): ?>
                        <button onclick="showSection('documents')" id="nav-documents" class="nav-link w-full flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg transition text-left">
                            <i class="fas fa-download w-5 text-center"></i> Downloads
                        </button>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-3">
                
                <!-- Overview Section -->
                <div id="section-overview" class="section-content active space-y-8">
                    <!-- Trader Stats -->
                    <?php if ($is_trader): ?>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-gray-500 text-sm font-medium">Credit Available</h3>
                                <div class="w-8 h-8 rounded-full bg-green-50 text-green-600 flex items-center justify-center">
                                    <i class="fas fa-credit-card text-sm"></i>
                                </div>
                            </div>
                            <p class="text-2xl font-bold text-gray-900">
                                £<?php echo number_format($user['credit_limit'] ?? 0, 2); ?>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Payment Terms: <?php echo htmlspecialchars($user['payment_terms'] ?? '30 days'); ?></p>
                        </div>
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-gray-500 text-sm font-medium">Total Spent</h3>
                                <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
                                    <i class="fas fa-pound-sign text-sm"></i>
                                </div>
                            </div>
                            <p class="text-2xl font-bold text-gray-900" id="stat-spent">£0.00</p>
                        </div>
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-gray-500 text-sm font-medium">Active Quotes</h3>
                                <div class="w-8 h-8 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center">
                                    <i class="fas fa-file-invoice text-sm"></i>
                                </div>
                            </div>
                            <p class="text-2xl font-bold text-gray-900" id="stat-quotes">0</p>
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
                    
                    <!-- Quick Actions -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Actions</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <a href="quick-order.php" class="p-4 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-200 transition group">
                                <div class="flex items-center justify-between mb-2">
                                    <i class="fas fa-bolt text-blue-600 text-lg"></i>
                                    <i class="fas fa-arrow-right text-gray-400 group-hover:text-blue-600 transition"></i>
                                </div>
                                <p class="font-medium text-gray-900">Quick Order</p>
                                <p class="text-sm text-gray-500">Bulk SKU entry</p>
                            </a>
                            <a href="quote-request.php" class="p-4 border border-gray-200 rounded-lg hover:bg-purple-50 hover:border-purple-200 transition group">
                                <div class="flex items-center justify-between mb-2">
                                    <i class="fas fa-file-invoice-dollar text-purple-600 text-lg"></i>
                                    <i class="fas fa-arrow-right text-gray-400 group-hover:text-purple-600 transition"></i>
                                </div>
                                <p class="font-medium text-gray-900">Request Quote</p>
                                <p class="text-sm text-gray-500">For large projects</p>
                            </a>
                            <a href="product-data.php" class="p-4 border border-gray-200 rounded-lg hover:bg-green-50 hover:border-green-200 transition group">
                                <div class="flex items-center justify-between mb-2">
                                    <i class="fas fa-download text-green-600 text-lg"></i>
                                    <i class="fas fa-arrow-right text-gray-400 group-hover:text-green-600 transition"></i>
                                </div>
                                <p class="font-medium text-gray-900">Product Data</p>
                                <p class="text-sm text-gray-500">CSV/PDF downloads</p>
                            </a>
                            <a href="compatibility-finder.php" class="p-4 border border-gray-200 rounded-lg hover:bg-orange-50 hover:border-orange-200 transition group">
                                <div class="flex items-center justify-between mb-2">
                                    <i class="fas fa-search text-orange-600 text-lg"></i>
                                    <i class="fas fa-arrow-right text-gray-400 group-hover:text-orange-600 transition"></i>
                                </div>
                                <p class="font-medium text-gray-900">Compatibility</p>
                                <p class="text-sm text-gray-500">Find matching parts</p>
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Residential Customer Stats -->
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
                                <h3 class="text-gray-500 text-sm font-medium">Pool Profile</h3>
                                <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
                                    <i class="fas fa-swimming-pool text-sm"></i>
                                </div>
                            </div>
                            <p class="text-2xl font-bold text-gray-900" id="stat-pools">0</p>
                            <p class="text-xs text-gray-500 mt-1">Configured pools</p>
                        </div>
                    </div>
                    
                    <!-- Pool Tools -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Pool Tools</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <a href="pool-finder.php" class="p-4 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-200 transition group">
                                <div class="flex items-center justify-between mb-2">
                                    <i class="fas fa-search text-blue-600 text-lg"></i>
                                    <i class="fas fa-arrow-right text-gray-400 group-hover:text-blue-600 transition"></i>
                                </div>
                                <p class="font-medium text-gray-900">Compatibility Finder</p>
                                <p class="text-sm text-gray-500">Match equipment to your pool</p>
                            </a>
                            <a href="energy-calculator.php" class="p-4 border border-gray-200 rounded-lg hover:bg-green-50 hover:border-green-200 transition group">
                                <div class="flex items-center justify-between mb-2">
                                    <i class="fas fa-bolt text-green-600 text-lg"></i>
                                    <i class="fas fa-arrow-right text-gray-400 group-hover:text-green-600 transition"></i>
                                </div>
                                <p class="font-medium text-gray-900">Energy Calculator</p>
                                <p class="text-sm text-gray-500">Compare running costs</p>
                            </a>
                            <a href="pool-configurator.php" class="p-4 border border-gray-200 rounded-lg hover:bg-purple-50 hover:border-purple-200 transition group">
                                <div class="flex items-center justify-between mb-2">
                                    <i class="fas fa-sliders-h text-purple-600 text-lg"></i>
                                    <i class="fas fa-arrow-right text-gray-400 group-hover:text-purple-600 transition"></i>
                                </div>
                                <p class="font-medium text-gray-900">System Configurator</p>
                                <p class="text-sm text-gray-500">Design complete systems</p>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Recent Orders -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-100">
                            <h3 class="text-lg font-bold text-gray-900">Recent Orders</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-gray-600">
                                <thead class="bg-gray-50 text-gray-900 font-semibold uppercase text-xs">
                                    <tr>
                                        <th class="px-6 py-4">Order ID</th>
                                        <th class="px-6 py-4">Date</th>
                                        <th class="px-6 py-4">Status</th>
                                        <th class="px-6 py-4">Total</th>
                                        <th class="px-6 py-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="recent-orders-body" class="divide-y divide-gray-100">
                                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Orders Section -->
                <div id="section-orders" class="section-content">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                            <h2 class="text-lg font-bold text-gray-900">Order History</h2>
                            <div class="flex gap-3">
                                <div class="relative w-full sm:w-auto">
                                    <input type="text" placeholder="Search orders..." class="w-full sm:w-64 pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                                </div>
                                <?php if ($is_trader): ?>
                                <button onclick="exportOrders()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium">
                                    <i class="fas fa-download mr-2"></i> Export
                                </button>
                                <?php endif; ?>
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
                                        <th class="px-6 py-4">Actions</th>
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
                        
                        <?php if ($is_trader): ?>
                        <div class="p-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between">
                            <div class="text-sm text-gray-600">
                                <i class="fas fa-info-circle mr-1"></i> Use "Quick Order" for bulk reordering
                            </div>
                            <button onclick="showSection('quick-order')" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                                <i class="fas fa-redo mr-2"></i> Reorder All
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quotes Section (Traders Only) -->
                <?php if ($is_trader): ?>
                <div id="section-quotes" class="section-content">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                            <h2 class="text-lg font-bold text-gray-900">Quote Requests</h2>
                            <a href="quote-request.php" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm font-medium flex items-center gap-2">
                                <i class="fas fa-plus"></i> New Quote Request
                            </a>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-gray-600">
                                <thead class="bg-gray-50 text-gray-900 font-semibold uppercase text-xs">
                                    <tr>
                                        <th class="px-6 py-4">Quote #</th>
                                        <th class="px-6 py-4">Created</th>
                                        <th class="px-6 py-4">Status</th>
                                        <th class="px-6 py-4">Total</th>
                                        <th class="px-6 py-4">Valid Until</th>
                                        <th class="px-6 py-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="quotes-table-body" class="divide-y divide-gray-100">
                                    <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No quote requests yet.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Order Section (Traders Only) -->
                <div id="section-quick-order" class="section-content">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-6">Quick Order Form</h2>
                        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-info-circle text-blue-600 mt-1"></i>
                                <div>
                                    <p class="text-sm text-blue-900 font-medium">Bulk ordering for trade customers</p>
                                    <p class="text-xs text-blue-700 mt-1">Enter SKUs and quantities to quickly reorder. Prices reflect your trade discount tier.</p>
                                </div>
                            </div>
                        </div>
                        
                        <form id="quickOrderForm" class="space-y-4">
                            <div class="space-y-3" id="skuRows">
                                <div class="grid grid-cols-12 gap-3 items-center">
                                    <div class="col-span-5">
                                        <input type="text" name="skus[]" placeholder="Enter SKU or product name" class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                                    </div>
                                    <div class="col-span-3">
                                        <input type="number" name="quantities[]" placeholder="Qty" min="1" class="w-full px-3 py-2 border border-gray-300 rounded text-sm" value="1">
                                    </div>
                                    <div class="col-span-3">
                                        <div class="flex items-center gap-2">
                                            <span class="text-gray-500 text-sm">Price:</span>
                                            <span class="font-medium">£--.--</span>
                                        </div>
                                    </div>
                                    <div class="col-span-1">
                                        <button type="button" onclick="removeSkuRow(this)" class="text-red-500 hover:text-red-700">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex gap-3">
                                <button type="button" onclick="addSkuRow()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 text-sm">
                                    <i class="fas fa-plus mr-2"></i> Add Row
                                </button>
                                <button type="button" onclick="importFromCSV()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 text-sm">
                                    <i class="fas fa-file-import mr-2"></i> Import CSV
                                </button>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-6">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-sm text-gray-600">Estimated Total:</p>
                                        <p class="text-2xl font-bold text-gray-900" id="quickOrderTotal">£0.00</p>
                                    </div>
                                    <div class="space-x-3">
                                        <button type="button" onclick="clearQuickOrder()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                            Clear
                                        </button>
                                        <button type="button" onclick="saveAsDraft()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                                            Save Draft
                                        </button>
                                        <button type="button" onclick="submitQuickOrder()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                            Add to Cart
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Pool Profile Section -->
                <div id="section-pool-profile" class="section-content">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-lg font-bold text-gray-900">My Pool Profile</h2>
                            <a href="pool-finder.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium flex items-center gap-2">
                                <i class="fas fa-plus"></i> Add Pool
                            </a>
                        </div>
                        
                        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-lightbulb text-blue-600 mt-1"></i>
                                <div>
                                    <p class="text-sm text-blue-900 font-medium">Personalized recommendations</p>
                                    <p class="text-xs text-blue-700 mt-1">Save your pool specifications to get compatible equipment suggestions and accurate pricing.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div id="poolProfilesContainer">
                            <div class="text-center py-8">
                                <i class="fas fa-swimming-pool text-4xl text-gray-300 mb-3"></i>
                                <p class="text-gray-500">No pool profiles saved yet.</p>
                                <a href="pool-finder.php" class="inline-block mt-3 text-blue-600 hover:text-blue-800 font-medium">
                                    Create your first pool profile →
                                </a>
                            </div>
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

                            <?php if ($is_trader): ?>
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
                                
                                <!-- Trade Account Details -->
                                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Credit Limit</label>
                                        <div class="flex items-center gap-2">
                                            <input type="text" value="£<?php echo number_format($user['credit_limit'] ?? 0, 2); ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-gray-50" readonly>
                                            <span class="text-xs text-gray-500">Available</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Terms</label>
                                        <input type="text" value="<?php echo htmlspecialchars($user['payment_terms'] ?? '30 days'); ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-gray-50" readonly>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Price Tier</label>
                                        <input type="text" value="<?php echo htmlspecialchars($user['price_tier'] ?? 'Standard'); ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-gray-50" readonly>
                                    </div>
                                </div>
                                
                                <!-- Account Status -->
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Status</label>
                                    <div class="flex items-center gap-2">
                                        <?php if ($user['approved_by_admin'] ?? 0): ?>
                                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                                                <i class="fas fa-check-circle mr-1"></i> Approved
                                            </span>
                                            <span class="text-sm text-gray-600">Full trade access enabled</span>
                                        <?php else: ?>
                                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">
                                                <i class="fas fa-clock mr-1"></i> Pending Approval
                                            </span>
                                            <span class="text-sm text-gray-600">Application under review</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Documents Section (Traders Only) -->
                <?php if ($is_trader): ?>
                <div id="section-documents" class="section-content">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-6">Downloads & Resources</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <!-- Product Catalogs -->
                            <div class="border border-gray-200 rounded-lg p-5 hover:bg-gray-50 transition">
                                <div class="flex items-center gap-4 mb-3">
                                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-book text-blue-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">Product Catalogs</h3>
                                        <p class="text-sm text-gray-500">Latest PDF catalogs</p>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <a href="#" class="flex items-center justify-between text-sm hover:text-blue-600 py-1">
                                        <span>2024 Pool Equipment Catalog</span>
                                        <i class="fas fa-download text-gray-400"></i>
                                    </a>
                                    <a href="#" class="flex items-center justify-between text-sm hover:text-blue-600 py-1">
                                        <span>Commercial Products Catalog</span>
                                        <i class="fas fa-download text-gray-400"></i>
                                    </a>
                                    <a href="#" class="flex items-center justify-between text-sm hover:text-blue-600 py-1">
                                        <span>Spare Parts Catalog</span>
                                        <i class="fas fa-download text-gray-400"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Technical Data -->
                            <div class="border border-gray-200 rounded-lg p-5 hover:bg-gray-50 transition">
                                <div class="flex items-center gap-4 mb-3">
                                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-file-alt text-green-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">Technical Data</h3>
                                        <p class="text-sm text-gray-500">Spec sheets & manuals</p>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <a href="#" class="flex items-center justify-between text-sm hover:text-green-600 py-1">
                                        <span>Installation Guides</span>
                                        <i class="fas fa-download text-gray-400"></i>
                                    </a>
                                    <a href="#" class="flex items-center justify-between text-sm hover:text-green-600 py-1">
                                        <span>Technical Specifications</span>
                                        <i class="fas fa-download text-gray-400"></i>
                                    </a>
                                    <a href="#" class="flex items-center justify-between text-sm hover:text-green-600 py-1">
                                        <span>Safety Data Sheets</span>
                                        <i class="fas fa-download text-gray-400"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Marketing Materials -->
                            <div class="border border-gray-200 rounded-lg p-5 hover:bg-gray-50 transition">
                                <div class="flex items-center gap-4 mb-3">
                                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-bullhorn text-purple-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">Marketing Materials</h3>
                                        <p class="text-sm text-gray-500">For resellers</p>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <a href="#" class="flex items-center justify-between text-sm hover:text-purple-600 py-1">
                                        <span>Product Images</span>
                                        <i class="fas fa-download text-gray-400"></i>
                                    </a>
                                    <a href="#" class="flex items-center justify-between text-sm hover:text-purple-600 py-1">
                                        <span>Brand Logos</span>
                                        <i class="fas fa-download text-gray-400"></i>
                                    </a>
                                    <a href="#" class="flex items-center justify-between text-sm hover:text-purple-600 py-1">
                                        <span>Price Lists (CSV)</span>
                                        <i class="fas fa-download text-gray-400"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

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
                
                <!-- Quick Reorder Button -->
                <?php if ($is_trader): ?>
                <div class="border-t border-gray-200 pt-6">
                    <button onclick="reorderFromModal()" class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium flex items-center justify-center gap-2">
                        <i class="fas fa-redo"></i> Quick Reorder All Items
                    </button>
                </div>
                <?php endif; ?>
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
             <?php if ($is_trader): ?>
             loadQuotes();
             <?php endif; ?>
             loadPoolProfiles();
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
                const recentTbody = document.getElementById('recent-orders-body');
                
                if (result.success) {
                    const orders = result.orders;
                    
                    // Update Stats
                    document.getElementById('stat-orders').textContent = orders.length;
                    
                    let totalSpent = 0;
                    let pending = 0;
                    
                    if (orders.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No orders found.</td></tr>`;
                        recentTbody.innerHTML = `<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No orders found.</td></tr>`;
                        return;
                    }
                    
                    // Full orders table
                    let html = '';
                    // Recent orders (first 5)
                    let recentHtml = '';
                    
                    orders.forEach((order, index) => {
                        totalSpent += parseFloat(order.total_amount);
                        if (order.status === 'Pending' || order.status === 'Processing') pending++;
                        
                        const statusColor = getStatusColor(order.status);
                        const rowHtml = `
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
                                    <?php if ($is_trader): ?>
                                    <button onclick="quickReorder(${order.id})" class="ml-2 text-green-600 hover:text-green-800 font-medium text-sm border border-green-200 hover:bg-green-50 px-3 py-1 rounded transition">
                                        Reorder
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        `;
                        
                        html += rowHtml;
                        
                        // Add to recent table if first 5
                        if (index < 5) {
                            recentHtml += rowHtml.replace('colspan="6"', 'colspan="5"').replace(/<td class="px-6 py-4 capitalize text-sm text-gray-500">.*?<\/td>/, '');
                        }
                    });
                    
                    tbody.innerHTML = html;
                    recentTbody.innerHTML = recentHtml || `<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No orders found.</td></tr>`;
                    document.getElementById('stat-spent').textContent = '£' + totalSpent.toFixed(2);
                    document.getElementById('stat-pending').textContent = pending;
                    
                } else {
                    tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-8 text-center text-red-500">Failed to load orders.</td></tr>`;
                    recentTbody.innerHTML = `<tr><td colspan="5" class="px-6 py-8 text-center text-red-500">Failed to load orders.</td></tr>`;
                }
            } catch (error) {
                console.error('Error loading orders:', error);
                document.getElementById('orders-table-body').innerHTML = `<tr><td colspan="6" class="px-6 py-8 text-center text-red-500">An error occurred.</td></tr>`;
            }
        }
        
        <?php if ($is_trader): ?>
        async function loadQuotes() {
            try {
                const response = await fetch('api/get_user_quotes.php');
                const result = await response.json();
                const tbody = document.getElementById('quotes-table-body');
                
                if (result.success && result.quotes.length > 0) {
                    let html = '';
                    result.quotes.forEach(quote => {
                        const statusColor = quote.status === 'Accepted' ? 'bg-green-100 text-green-700' :
                                          quote.status === 'Pending' ? 'bg-orange-100 text-orange-700' :
                                          quote.status === 'Expired' ? 'bg-gray-100 text-gray-700' :
                                          'bg-red-100 text-red-700';
                        
                        html += `
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium">QT-${quote.id}</td>
                                <td class="px-6 py-4">${quote.created_date}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold ${statusColor}">
                                        ${quote.status}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-medium">£${quote.total}</td>
                                <td class="px-6 py-4">${quote.valid_until}</td>
                                <td class="px-6 py-4">
                                    <button onclick="viewQuote(${quote.id})" class="text-blue-600 hover:text-blue-800 text-sm">
                                        View
                                    </button>
                                    ${quote.status === 'Accepted' ? `
                                    <button onclick="convertQuoteToOrder(${quote.id})" class="ml-2 text-green-600 hover:text-green-800 text-sm">
                                        Order Now
                                    </button>` : ''}
                                </td>
                            </tr>
                        `;
                    });
                    
                    tbody.innerHTML = html;
                    document.getElementById('stat-quotes').textContent = result.quotes.length;
                } else {
                    tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No quote requests yet.</td></tr>`;
                }
            } catch (error) {
                console.error('Error loading quotes:', error);
            }
        }
        
        // Quick Order Functions
        function addSkuRow() {
            const container = document.getElementById('skuRows');
            const newRow = document.createElement('div');
            newRow.className = 'grid grid-cols-12 gap-3 items-center';
            newRow.innerHTML = `
                <div class="col-span-5">
                    <input type="text" name="skus[]" placeholder="Enter SKU or product name" class="w-full px-3 py-2 border border-gray-300 rounded text-sm" oninput="lookupSkuPrice(this)">
                </div>
                <div class="col-span-3">
                    <input type="number" name="quantities[]" placeholder="Qty" min="1" class="w-full px-3 py-2 border border-gray-300 rounded text-sm" value="1" oninput="updateQuickOrderTotal()">
                </div>
                <div class="col-span-3">
                    <div class="flex items-center gap-2">
                        <span class="text-gray-500 text-sm">Price:</span>
                        <span class="font-medium sku-price">£--.--</span>
                    </div>
                </div>
                <div class="col-span-1">
                    <button type="button" onclick="removeSkuRow(this)" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            container.appendChild(newRow);
        }
        
        function removeSkuRow(button) {
            const row = button.closest('.grid');
            if (row && document.querySelectorAll('#skuRows .grid').length > 1) {
                row.remove();
                updateQuickOrderTotal();
            }
        }
        
        async function lookupSkuPrice(input) {
            const sku = input.value.trim();
            if (sku.length > 3) {
                const priceElement = input.closest('.grid').querySelector('.sku-price');
                priceElement.innerHTML = '<span class="text-gray-400">Looking up...</span>';
                
                try {
                    const response = await fetch(`api/lookup_sku.php?sku=${encodeURIComponent(sku)}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        priceElement.textContent = `£${data.price.toFixed(2)}`;
                        priceElement.setAttribute('data-price', data.price);
                    } else {
                        priceElement.textContent = '£--.--';
                        priceElement.removeAttribute('data-price');
                    }
                } catch (error) {
                    priceElement.textContent = '£--.--';
                }
                
                updateQuickOrderTotal();
            }
        }
        
        function updateQuickOrderTotal() {
            let total = 0;
            document.querySelectorAll('#skuRows .grid').forEach(row => {
                const priceElement = row.querySelector('.sku-price');
                const price = parseFloat(priceElement.getAttribute('data-price')) || 0;
                const qtyInput = row.querySelector('input[name="quantities[]"]');
                const qty = parseInt(qtyInput.value) || 0;
                total += price * qty;
            });
            
            document.getElementById('quickOrderTotal').textContent = `£${total.toFixed(2)}`;
        }
        
        async function submitQuickOrder() {
            // Implementation for adding to cart
            alert('Quick order functionality would add items to cart');
        }
        
        function quickReorder(orderId) {
            // Implementation to reorder all items from an order
            alert(`Would reorder all items from order #${orderId}`);
        }
        <?php endif; ?>
        
        async function loadPoolProfiles() {
            try {
                const response = await fetch('api/get_pool_profiles.php');
                const result = await response.json();
                const container = document.getElementById('poolProfilesContainer');
                
                if (result.success && result.profiles.length > 0) {
                    document.getElementById('stat-pools').textContent = result.profiles.length;
                    
                    let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-6">';
                    result.profiles.forEach(profile => {
                        html += `
                            <div class="border border-gray-200 rounded-lg p-5 hover:bg-gray-50 transition">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h3 class="font-semibold text-gray-900">${profile.name}</h3>
                                        <p class="text-sm text-gray-500">${profile.type} • ${profile.size}</p>
                                    </div>
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-medium">
                                        ${profile.equipment_count} equipment
                                    </span>
                                </div>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Volume:</span>
                                        <span class="font-medium">${profile.volume}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Usage:</span>
                                        <span class="font-medium">${profile.usage}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Last Updated:</span>
                                        <span>${profile.updated}</span>
                                    </div>
                                </div>
                                <div class="mt-4 flex gap-2">
                                    <a href="pool-finder.php?profile=${profile.id}" class="flex-1 text-center px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                        View Details
                                    </a>
                                    <a href="compatibility-finder.php?pool=${profile.id}" class="flex-1 text-center px-3 py-2 border border-blue-600 text-blue-600 rounded hover:bg-blue-50 text-sm">
                                        Find Equipment
                                    </a>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    container.innerHTML = html;
                } else {
                    container.innerHTML = `
                        <div class="text-center py-8">
                            <i class="fas fa-swimming-pool text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500">No pool profiles saved yet.</p>
                            <a href="pool-finder.php" class="inline-block mt-3 text-blue-600 hover:text-blue-800 font-medium">
                                Create your first pool profile →
                            </a>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading pool profiles:', error);
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
                                            <p class="text-xs text-gray-500">SKU: ${item.sku}</p>
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
                case 'shipped': return 'bg-purple-100 text-purple-700';
                default: return 'bg-gray-100 text-gray-700';
            }
        }
        
        <?php if ($is_trader): ?>
        function exportOrders() {
            // Implementation for exporting orders to CSV
            alert('Export functionality would generate a CSV file');
        }
        
        function importFromCSV() {
            // Implementation for CSV import
            alert('CSV import functionality');
        }
        
        function saveAsDraft() {
            // Implementation for saving draft order
            alert('Draft saved');
        }
        
        function clearQuickOrder() {
            document.querySelectorAll('#skuRows .grid').forEach((row, index) => {
                if (index === 0) {
                    // Clear first row but keep it
                    row.querySelector('input[name="skus[]"]').value = '';
                    row.querySelector('input[name="quantities[]"]').value = '1';
                    row.querySelector('.sku-price').textContent = '£--.--';
                    row.querySelector('.sku-price').removeAttribute('data-price');
                } else {
                    row.remove();
                }
            });
            updateQuickOrderTotal();
        }
        
        function reorderFromModal() {
            // Implementation to reorder items from modal
            alert('Would add all items from this order to quick order form');
            showSection('quick-order');
        }

        // Add this to the existing JavaScript in dashboard.php

        async function loadPoolProfiles() {
            try {
                const response = await fetch('api/get_pool_profiles.php');
                const result = await response.json();
                const container = document.getElementById('poolProfilesContainer');
                
                if (result.success && result.profiles && result.profiles.length > 0) {
                    // Update stat counter
                    const statPools = document.getElementById('stat-pools');
                    if (statPools) {
                        statPools.textContent = result.profiles.length;
                    }
                    
                    let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-6">';
                    
                    result.profiles.forEach(profile => {
                        html += `
                            <div class="border border-gray-200 rounded-lg p-5 hover:shadow-md transition">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h3 class="font-semibold text-gray-900 text-lg">${escapeHtml(profile.name)}</h3>
                                        <p class="text-sm text-gray-500">${escapeHtml(profile.type)} • ${escapeHtml(profile.size)}</p>
                                    </div>
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-medium">
                                        <i class="fas fa-cog mr-1"></i> ${profile.equipment_count} item${profile.equipment_count !== 1 ? 's' : ''}
                                    </span>
                                </div>
                                
                                <div class="space-y-2 text-sm mb-4">
                                    <div class="flex justify-between py-1 border-b border-gray-100">
                                        <span class="text-gray-600"><i class="fas fa-water w-4 text-center mr-1"></i>Volume:</span>
                                        <span class="font-medium">${escapeHtml(profile.volume)}</span>
                                    </div>
                                    <div class="flex justify-between py-1 border-b border-gray-100">
                                        <span class="text-gray-600"><i class="fas fa-users w-4 text-center mr-1"></i>Usage:</span>
                                        <span class="font-medium">${escapeHtml(profile.usage)}</span>
                                    </div>
                                    <div class="flex justify-between py-1 border-b border-gray-100">
                                        <span class="text-gray-600"><i class="fas fa-layer-group w-4 text-center mr-1"></i>Material:</span>
                                        <span class="font-medium">${escapeHtml(profile.material)}</span>
                                    </div>
                                    ${profile.filter_type ? `
                                    <div class="flex justify-between py-1 border-b border-gray-100">
                                        <span class="text-gray-600"><i class="fas fa-filter w-4 text-center mr-1"></i>Filter:</span>
                                        <span class="font-medium">${escapeHtml(profile.filter_type)}</span>
                                    </div>
                                    ` : ''}
                                    <div class="flex justify-between py-1">
                                        <span class="text-gray-600"><i class="fas fa-clock w-4 text-center mr-1"></i>Updated:</span>
                                        <span class="text-gray-500 text-xs">${escapeHtml(profile.updated)}</span>
                                    </div>
                                </div>
                                
                                <div class="flex gap-2">
                                    <a href="pool-finder.php?profile=${profile.id}" 
                                    class="flex-1 text-center px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition">
                                        <i class="fas fa-edit mr-1"></i> Edit Profile
                                    </a>
                                    <a href="compatibility-finder.php?pool=${profile.id}" 
                                    class="flex-1 text-center px-3 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 text-sm font-medium transition">
                                        <i class="fas fa-search mr-1"></i> Find Parts
                                    </a>
                                </div>
                            </div>
                        `;
                    });
                    
                    html += '</div>';
                    container.innerHTML = html;
                    
                } else {
                    // No profiles found - show empty state
                    container.innerHTML = `
                        <div class="text-center py-12">
                            <div class="mb-4">
                                <i class="fas fa-swimming-pool text-6xl text-gray-300"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Pool Profiles Yet</h3>
                            <p class="text-gray-500 mb-6">Create your first pool profile to get personalized equipment recommendations.</p>
                            <a href="pool-finder.php" 
                            class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition shadow-sm">
                                <i class="fas fa-plus"></i>
                                Create Pool Profile
                            </a>
                        </div>
                    `;
                    
                    // Update stat to 0
                    const statPools = document.getElementById('stat-pools');
                    if (statPools) {
                        statPools.textContent = '0';
                    }
                }
                
            } catch (error) {
                console.error('Error loading pool profiles:', error);
                
                const container = document.getElementById('poolProfilesContainer');
                container.innerHTML = `
                    <div class="text-center py-12">
                        <div class="mb-4">
                            <i class="fas fa-exclamation-triangle text-6xl text-red-300"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Error Loading Profiles</h3>
                        <p class="text-gray-500 mb-6">We couldn't load your pool profiles. Please try again.</p>
                        <button onclick="loadPoolProfiles()" 
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition">
                            <i class="fas fa-redo mr-2"></i> Retry
                        </button>
                    </div>
                `;
            }
        }

        // Helper function to escape HTML and prevent XSS
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, m => map[m]);
        }

        // Enhanced Navigation Logic with Hash Support
        function showSection(sectionId) {
            // Update URL hash without page reload
            if (window.history.pushState) {
                window.history.pushState(null, null, '#' + sectionId);
            } else {
                window.location.hash = sectionId;
            }
            
            // Hide all sections
            document.querySelectorAll('.section-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));
            
            // Show target section
            const target = document.getElementById('section-' + sectionId);
            const nav = document.getElementById('nav-' + sectionId);
            
            if (target) {
                target.classList.add('active');
                if (nav) nav.classList.add('active');
                
                // Scroll to top of content area smoothly
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                // Default to overview if target doesn't exist
                document.getElementById('section-overview').classList.add('active');
                document.getElementById('nav-overview').classList.add('active');
            }
        }

        // Handle hash changes (back/forward browser buttons)
        window.addEventListener('hashchange', function() {
            const hash = window.location.hash.substring(1);
            if (hash) {
                showSectionFromHash(hash);
            }
        });

        // Helper function to show section without updating hash (prevents double hash update)
        function showSectionFromHash(sectionId) {
            // Hide all sections
            document.querySelectorAll('.section-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));
            
            // Show target section
            const target = document.getElementById('section-' + sectionId);
            const nav = document.getElementById('nav-' + sectionId);
            
            if (target) {
                target.classList.add('active');
                if (nav) nav.classList.add('active');
                
                // Scroll to top of content area
                setTimeout(() => {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 100);
            } else {
                // Default to overview
                document.getElementById('section-overview').classList.add('active');
                document.getElementById('nav-overview').classList.add('active');
            }
        }

        // Update the DOMContentLoaded event
        document.addEventListener('DOMContentLoaded', () => {
            // Initial Load based on hash
            const hash = window.location.hash.substring(1);
            if (hash) {
                showSectionFromHash(hash);
            } else {
                showSection('overview');
            }

            loadOrders();
            <?php if ($is_trader): ?>
            loadQuotes();
            <?php endif; ?>
            loadPoolProfiles();
        });
        <?php endif; ?>
    </script>
</body>
</html>