<?php
// admin/analytics.php
session_start();
require_once '../config.php';
require_once 'include/auth_check.php';

// Check if user is logged in
checkAdminAuth();

$admin_name = $_SESSION['admin_name'] ?? 'Admin User';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
$current_page = 'analytics';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics | Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">Analytics</h1>
                            <p class="text-gray-600 mt-1">Inventory & Category Insights</p>
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
            <!-- Summary Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Sales by Category -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Revenue by Category</h3>
                    <div class="h-[300px] flex justify-center">
                        <canvas id="categorySalesChart"></canvas>
                    </div>
                </div>

                <!-- Stock Health -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Stock Health</h3>
                    <div class="h-[300px] flex justify-center">
                        <canvas id="stockHealthChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Inventory Value Row -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Inventory Value by Category</h3>
                <canvas id="inventoryValueChart" height="300"></canvas>
            </div>

            <!-- Low Stock Table -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Low Stock Alert</h3>
                    <a href="products.php?stock=low_stock" class="text-sm text-blue-600 hover:text-blue-800">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="pb-3 text-sm font-semibold text-gray-600">Product Name</th>
                                <th class="pb-3 text-sm font-semibold text-gray-600">SKU</th>
                                <th class="pb-3 text-sm font-semibold text-gray-600">Category</th>
                                <th class="pb-3 text-sm font-semibold text-gray-600">Stock</th>
                            </tr>
                        </thead>
                        <tbody id="lowStockTable" class="divide-y divide-gray-100">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        // User menu toggle
        document.getElementById('userMenuBtn').addEventListener('click', function() {
            document.getElementById('userMenu').classList.toggle('hidden');
        });

        // Mobile sidebar toggle
        document.getElementById('mobileSidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('mobile-open');
        });

        function loadAnalytics() {
            fetch('../api/admin/get_analytics.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCategorySalesChart(data.category_sales);
                        updateStockHealthChart(data.stock_health);
                        updateInventoryValueChart(data.inventory_value);
                        updateLowStockTable(data.low_stock_products);
                    }
                })
                .catch(err => console.error('Error loading analytics:', err));
        }

        function updateCategorySalesChart(data) {
            const ctx = document.getElementById('categorySalesChart').getContext('2d');
            
            // Random vibrant colors
            const colors = ['#3b82f6', '#022658', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#6366f1'];

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.map(d => d.category),
                    datasets: [{
                        data: data.map(d => d.revenue),
                        backgroundColor: colors.slice(0, data.length)
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        function updateStockHealthChart(data) {
            const ctx = document.getElementById('stockHealthChart').getContext('2d');
            
            const colorMap = {
                'In Stock': '#022658', // dark blue
                'Low Stock': '#f59e0b', // orange
                'Out of Stock': '#ef4444' // red
            };

            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: data.map(d => d.status),
                    datasets: [{
                        data: data.map(d => d.count),
                        backgroundColor: data.map(d => colorMap[d.status] || '#cbd5e1')
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        function updateInventoryValueChart(data) {
            const ctx = document.getElementById('inventoryValueChart').getContext('2d');
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(d => d.category),
                    datasets: [{
                        label: 'Inventory Value (£)',
                        data: data.map(d => d.total_value),
                        backgroundColor: '#3b82f6',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        function updateLowStockTable(products) {
            const tbody = document.getElementById('lowStockTable');
            tbody.innerHTML = '';
            
            if (products.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="py-4 text-center text-gray-500 text-sm">No low stock alerts</td></tr>';
                return;
            }

            products.forEach(p => {
                const stockClass = p.stock === 0 ? 'text-red-600 bg-red-50' : 'text-orange-600 bg-orange-50';
                const html = `
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 text-sm text-gray-800">${p.product_name}</td>
                        <td class="py-3 text-sm text-gray-600">${p.sku_number}</td>
                        <td class="py-3 text-sm text-gray-600">${p.category || 'N/A'}</td>
                        <td class="py-3">
                            <span class="px-2 py-1 rounded text-xs font-bold ${stockClass}">
                                ${p.stock} units
                            </span>
                        </td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', html);
            });
        }

        // Initial Load
        loadAnalytics();
    </script>
</body>
</html>
