<?php
// admin/dashboard.php (or admin-dashboard.php)
session_start();
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php?session=expired');
    exit;
}

// Get database connection
$conn = getDbConnection();

// Get pending orders count
$pending_query = "SELECT COUNT(*) as pending_count FROM orders WHERE status = 'Pending'";
$pending_result = $conn->query($pending_query);
$pending_count = $pending_result ? $pending_result->fetch_assoc()['pending_count'] : 0;

// Get dashboard statistics
$stats_query = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_orders,
    SUM(CASE WHEN status = 'Processing' THEN 1 ELSE 0 END) as processing_orders,
    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_orders,
    SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
    COALESCE(SUM(total_amount), 0) as total_revenue,
    COUNT(DISTINCT customer_email) as total_customers
FROM orders";
$stats_result = $conn->query($stats_query);
$stats = $stats_result ? $stats_result->fetch_assoc() : [
    'total_orders' => 0,
    'pending_orders' => 0,
    'processing_orders' => 0,
    'completed_orders' => 0,
    'cancelled_orders' => 0,
    'total_revenue' => 0,
    'total_customers' => 0
];

// Get products count
$products_query = "SELECT COUNT(*) as total_products FROM products";
$products_result = $conn->query($products_query);
$total_products = $products_result ? $products_result->fetch_assoc()['total_products'] : 0;

// Get recent orders
$recent_orders_query = "SELECT id, user_id, total_amount, status, created_at, customer_name, customer_email
                        FROM orders 
                        ORDER BY created_at DESC 
                        LIMIT 5";
$recent_orders_result = $conn->query($recent_orders_query);
$recent_orders = $recent_orders_result ? $recent_orders_result->fetch_all(MYSQLI_ASSOC) : [];

// Get top products (from order_items)
$top_products_query = "SELECT 
                       p.product_name as name, 
                    --    p.sku, 
                       p.price, 
                       COUNT(oi.id) as times_sold,
                       COALESCE(SUM(oi.quantity), 0) as total_quantity
                       FROM products p
                       LEFT JOIN order_items oi ON p.id = oi.product_id
                       GROUP BY p.id, p.product_name, p.price
                       HAVING total_quantity > 0
                       ORDER BY total_quantity DESC
                       LIMIT 5";
$top_products_result = $conn->query($top_products_query);
$top_products = $top_products_result ? $top_products_result->fetch_all(MYSQLI_ASSOC) : [];

// Get monthly revenue data for chart (last 6 months)
$revenue_query = "SELECT 
    DATE_FORMAT(created_at, '%b') as month,
    YEAR(created_at) as year,
    MONTH(created_at) as month_num,
    COALESCE(SUM(total_amount), 0) as revenue
    FROM orders
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY YEAR(created_at), MONTH(created_at), DATE_FORMAT(created_at, '%b')
    ORDER BY year ASC, month_num ASC";
$revenue_result = $conn->query($revenue_query);
$revenue_data = $revenue_result ? $revenue_result->fetch_all(MYSQLI_ASSOC) : [];

// If no revenue data, create empty array for display
if (empty($revenue_data)) {
    $current_month = date('n');
    $months_names = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $revenue_data = [];
    for ($i = 5; $i >= 0; $i--) {
        $month_index = ($current_month - $i - 1 + 12) % 12;
        $revenue_data[] = [
            'month' => $months_names[$month_index],
            'revenue' => 0
        ];
    }
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin User';
$admin_role = $_SESSION['admin_role'] ?? 'admin';

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Commercial Pool Equipment Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar { width: 260px; transition: all 0.3s; }
        .sidebar.collapsed { width: 80px; }
        .sidebar.collapsed .sidebar-text { display: none; }
        .sidebar.collapsed .logo-text { display: none; }
        .main-content { margin-left: 260px; transition: all 0.3s; }
        .main-content.expanded { margin-left: 80px; }
        .stat-card { transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-4px); }
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
        <!-- Top Header -->
        <header class="bg-white border-b border-gray-200 sticky top-0 z-40">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <button id="mobileSidebarToggle" class="lg:hidden mr-4 text-gray-600">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Search -->
                        <div class="hidden md:block relative">
                            <input type="text" placeholder="Search..." 
                                   class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                        
                        <!-- Notifications -->
                        <div class="relative">
                            <button class="relative p-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                                <i class="fas fa-bell text-xl"></i>
                                <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                            </button>
                        </div>
                        
                        <!-- User Menu -->
                        <div class="relative">
                            <button id="userMenuBtn" class="flex items-center space-x-3 p-2 hover:bg-gray-100 rounded-lg">
                                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                                    A
                                </div>
                                <div class="hidden md:block text-left">
                                    <div class="text-sm font-semibold text-gray-700">Admin User</div>
                                    <div class="text-xs text-gray-500">Administrator</div>
                                </div>
                                <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                            </button>
                            
                            <!-- Dropdown -->
                            <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2">
                                <a href="#profile" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i>Profile
                                </a>
                                <a href="#settings" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-cog mr-2"></i>Settings
                                </a>
                                <hr class="my-2">
                                <a href="api/admin/logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Dashboard Content -->
        <main class="p-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Total Revenue</p>
                            <h3 class="text-3xl font-bold text-gray-800">£<?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-pound-sign text-green-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="flex items-center text-sm">
                        <span class="text-green-600 font-medium">+12.5%</span>
                        <span class="text-gray-500 ml-2">vs last month</span>
                    </div>
                </div>

                <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Orders</p>
                            <h3 class="text-3xl font-bold text-gray-800"><?php echo number_format($stats['total_orders'] ?? 0); ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="flex items-center text-sm">
                        <span class="text-yellow-600 font-medium"><?php echo $pending_count; ?> pending</span>
                    </div>
                </div>

                <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Customers</p>
                            <h3 class="text-3xl font-bold text-gray-800"><?php echo number_format($stats['total_customers'] ?? 0); ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-users text-purple-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="flex items-center text-sm">
                        <span class="text-green-600 font-medium">+15.3%</span>
                        <span class="text-gray-500 ml-2">vs last month</span>
                    </div>
                </div>

                <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Products</p>
                            <h3 class="text-3xl font-bold text-gray-800"><?php echo number_format($total_products ?? 0); ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-box text-orange-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="flex items-center text-sm">
                        <span class="text-red-600 font-medium">-2.4%</span>
                        <span class="text-gray-500 ml-2">vs last month</span>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Revenue Chart -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Revenue Overview</h3>
                    <canvas id="revenueChart" height="300"></canvas>
                </div>

                <!-- Orders Chart -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Order Status</h3>
                    <canvas id="ordersChart" height="300"></canvas>
                </div>
            </div>

            <!-- Recent Orders & Top Products -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Orders -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-800">Recent Orders</h3>
                            <a href="#orders" class="text-blue-600 text-sm font-medium hover:underline">View All</a>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                           <tbody class="divide-y divide-gray-100">
                                <?php if (empty($recent_orders)): ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                            <i class="fas fa-inbox text-3xl mb-2"></i>
                                            <p>No recent orders</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <?php
                                        $status_colors = [
                                            'completed' => 'bg-green-100 text-green-800',
                                            'processing' => 'bg-blue-100 text-blue-800',
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                            'on_hold' => 'bg-gray-100 text-gray-800',
                                            'refunded' => 'bg-purple-100 text-purple-800'
                                        ];
                                        $status_class = $status_colors[$order['status']] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                                <a href="order_details.php?id=<?php echo $order['id']; ?>" class="text-blue-600 hover:underline">
                                                    <?php echo htmlspecialchars($order['id']); ?>
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">£<?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td class="px-6 py-4">
                                                <span class="px-2 py-1 text-xs font-medium <?php echo $status_class; ?> rounded-full">
                                                    <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top Products -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-800">Top Products</h3>
                            <a href="#products" class="text-blue-600 text-sm font-medium hover:underline">View All</a>
                        </div>
                    </div>
                    <div class="p-6 space-y-4">
                        <?php if (empty($top_products)): ?>
                            <p class="text-center text-gray-500 py-4">No product data available</p>
                        <?php else: ?>
                            <?php foreach ($top_products as $product): ?>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 bg-gray-200 rounded"></div>
                                        <div>
                                            <p class="font-medium text-gray-800"><?php echo htmlspecialchars($product['name']); ?></p>
                                            <!-- <p class="text-sm text-gray-500">SKU: <?php echo htmlspecialchars($product['sku']); ?></p> -->
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold text-gray-800">£<?php echo number_format($product['price'], 2); ?></p>
                                        <p class="text-sm text-gray-500"><?php echo $product['total_quantity'] ?? 0; ?> sold</p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');

        sidebarToggle?.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });

        mobileSidebarToggle?.addEventListener('click', () => {
            sidebar.classList.toggle('mobile-open');
        });

        // User Menu Toggle
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userMenu = document.getElementById('userMenu');

        userMenuBtn?.addEventListener('click', () => {
            userMenu.classList.toggle('hidden');
        });

        document.addEventListener('click', (e) => {
            if (!userMenuBtn.contains(e.target) && !userMenu.contains(e.target)) {
                userMenu.classList.add('hidden');
            }
        });

        // Revenue Chart with dynamic data
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($revenue_data, 'month')); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode(array_column($revenue_data, 'revenue')); ?>,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '£' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

// Orders Chart with dynamic data
const ordersCtx = document.getElementById('ordersChart').getContext('2d');
new Chart(ordersCtx, {
    type: 'doughnut',
    data: {
        labels: ['Completed', 'Processing', 'Pending', 'Cancelled'],
        datasets: [{
            data: [
                <?php echo $stats['completed_orders'] ?? 0; ?>,
                <?php echo $stats['processing_orders'] ?? 0; ?>,
                <?php echo $stats['pending_orders'] ?? 0; ?>,
                <?php echo $stats['cancelled_orders'] ?? 0; ?>
            ],
            backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444']
        }]
    },
    options: { responsive: true, maintainAspectRatio: false }
});

        const ordersCtx = document.getElementById('ordersChart').getContext('2d');
        new Chart(ordersCtx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Processing', 'Pending', 'Cancelled'],
                datasets: [{
                    data: [450, 280, 120, 50],
                    backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    </script>
</body>
</html>