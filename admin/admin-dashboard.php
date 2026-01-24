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
    <aside id="sidebar" class="sidebar fixed left-0 top-0 h-screen bg-gray-900 text-white shadow-xl overflow-y-auto">
        <div class="p-4 border-b border-gray-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-shield-halved text-2xl text-blue-400"></i>
                    <span class="logo-text ml-3 font-bold text-lg">Jacksons Admin</span>
                </div>
                <button id="sidebarToggle" class="lg:block hidden text-gray-400 hover:text-white">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>

        <nav class="p-4">
            <div class="mb-6">
                <div class="text-xs text-gray-500 uppercase mb-2 px-3 sidebar-text">Main</div>
                <a href="#" class="flex items-center px-3 py-2.5 bg-blue-600 rounded-lg mb-1">
                    <i class="fas fa-home w-5"></i>
                    <span class="sidebar-text ml-3">Dashboard</span>
                </a>
                <a href="#orders" class="flex items-center px-3 py-2.5 text-gray-300 hover:bg-gray-800 rounded-lg mb-1">
                    <i class="fas fa-shopping-cart w-5"></i>
                    <span class="sidebar-text ml-3">Orders</span>
                    <span class="sidebar-text ml-auto bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">12</span>
                </a>
                <a href="#products" class="flex items-center px-3 py-2.5 text-gray-300 hover:bg-gray-800 rounded-lg mb-1">
                    <i class="fas fa-box w-5"></i>
                    <span class="sidebar-text ml-3">Products</span>
                </a>
                <a href="#customers" class="flex items-center px-3 py-2.5 text-gray-300 hover:bg-gray-800 rounded-lg mb-1">
                    <i class="fas fa-users w-5"></i>
                    <span class="sidebar-text ml-3">Customers</span>
                </a>
            </div>

            <div class="mb-6">
                <div class="text-xs text-gray-500 uppercase mb-2 px-3 sidebar-text">Content</div>
                <a href="#categories" class="flex items-center px-3 py-2.5 text-gray-300 hover:bg-gray-800 rounded-lg mb-1">
                    <i class="fas fa-folder w-5"></i>
                    <span class="sidebar-text ml-3">Categories</span>
                </a>
                <a href="#media" class="flex items-center px-3 py-2.5 text-gray-300 hover:bg-gray-800 rounded-lg mb-1">
                    <i class="fas fa-images w-5"></i>
                    <span class="sidebar-text ml-3">Media Library</span>
                </a>
            </div>

            <div class="mb-6">
                <div class="text-xs text-gray-500 uppercase mb-2 px-3 sidebar-text">Analytics</div>
                <a href="#reports" class="flex items-center px-3 py-2.5 text-gray-300 hover:bg-gray-800 rounded-lg mb-1">
                    <i class="fas fa-chart-bar w-5"></i>
                    <span class="sidebar-text ml-3">Reports</span>
                </a>
                <a href="#analytics" class="flex items-center px-3 py-2.5 text-gray-300 hover:bg-gray-800 rounded-lg mb-1">
                    <i class="fas fa-chart-line w-5"></i>
                    <span class="sidebar-text ml-3">Analytics</span>
                </a>
            </div>

            <div class="mb-6" id="adminOnlySection">
                <div class="text-xs text-gray-500 uppercase mb-2 px-3 sidebar-text">Administration</div>
                <a href="#users" class="flex items-center px-3 py-2.5 text-gray-300 hover:bg-gray-800 rounded-lg mb-1">
                    <i class="fas fa-user-shield w-5"></i>
                    <span class="sidebar-text ml-3">Admin Users</span>
                </a>
                <a href="#settings" class="flex items-center px-3 py-2.5 text-gray-300 hover:bg-gray-800 rounded-lg mb-1">
                    <i class="fas fa-cog w-5"></i>
                    <span class="sidebar-text ml-3">Settings</span>
                </a>
                <a href="#activity" class="flex items-center px-3 py-2.5 text-gray-300 hover:bg-gray-800 rounded-lg mb-1">
                    <i class="fas fa-history w-5"></i>
                    <span class="sidebar-text ml-3">Activity Log</span>
                </a>
            </div>
        </nav>
    </aside>

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
                            <h3 class="text-3xl font-bold text-gray-800">£45,231</h3>
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
                            <h3 class="text-3xl font-bold text-gray-800">1,254</h3>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="flex items-center text-sm">
                        <span class="text-green-600 font-medium">+8.2%</span>
                        <span class="text-gray-500 ml-2">vs last month</span>
                    </div>
                </div>

                <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Customers</p>
                            <h3 class="text-3xl font-bold text-gray-800">3,842</h3>
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
                            <h3 class="text-3xl font-bold text-gray-800">582</h3>
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
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">#ORD-1234</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">John Smith</td>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">£234.50</td>
                                    <td class="px-6 py-4"><span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Completed</span></td>
                                </tr>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">#ORD-1235</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">Jane Doe</td>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">£456.00</td>
                                    <td class="px-6 py-4"><span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">Processing</span></td>
                                </tr>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">#ORD-1236</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">Mike Johnson</td>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">£189.99</td>
                                    <td class="px-6 py-4"><span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Pending</span></td>
                                </tr>
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
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-gray-200 rounded"></div>
                                <div>
                                    <p class="font-medium text-gray-800">Leisure Battery 12V</p>
                                    <p class="text-sm text-gray-500">SKU: LB-12V-100</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-800">£89.99</p>
                                <p class="text-sm text-gray-500">142 sold</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-gray-200 rounded"></div>
                                <div>
                                    <p class="font-medium text-gray-800">Water Pump Kit</p>
                                    <p class="text-sm text-gray-500">SKU: WP-KIT-01</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-800">£45.50</p>
                                <p class="text-sm text-gray-500">98 sold</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-gray-200 rounded"></div>
                                <div>
                                    <p class="font-medium text-gray-800">Solar Panel 100W</p>
                                    <p class="text-sm text-gray-500">SKU: SP-100W</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-800">£199.00</p>
                                <p class="text-sm text-gray-500">76 sold</p>
                            </div>
                        </div>
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

        // Charts
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Revenue',
                    data: [12000, 19000, 15000, 25000, 22000, 30000],
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
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