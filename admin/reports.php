<?php
// admin/reports.php
session_start();
require_once '../config.php';
require_once 'include/auth_check.php';

// Check if user is logged in
checkAdminAuth();

$admin_name = $_SESSION['admin_name'] ?? 'Admin User';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
$current_page = 'reports';

// Default dates
$date_to = date('Y-m-d');
$date_from = date('Y-m-d', strtotime('-30 days'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | Admin Dashboard</title>
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
                            <h1 class="text-2xl font-bold text-gray-800">Reports & Analytics</h1>
                            <p class="text-gray-600 mt-1">Business performance overview</p>
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
            <!-- Filter Bar -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
                <form id="reportFilterForm" class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">From Date</label>
                        <input type="date" name="from" id="dateFrom" value="<?php echo $date_from; ?>"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">To Date</label>
                        <input type="date" name="to" id="dateTo" value="<?php echo $date_to; ?>"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                        Update Reports
                    </button>
                </form>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                    <p class="text-sm text-gray-500 uppercase font-semibold">Total Revenue</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2" id="totalRevenue">£0.00</h3>
                    <div id="revenueGrowth" class="text-sm mt-2 text-gray-400">in selected period</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                    <p class="text-sm text-gray-500 uppercase font-semibold">Total Orders</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2" id="totalOrders">0</h3>
                    <div class="text-sm mt-2 text-gray-400">orders processed</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                    <p class="text-sm text-gray-500 uppercase font-semibold">Average Order Value</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2" id="avgOrderValue">£0.00</h3>
                    <div class="text-sm mt-2 text-gray-400">per transaction</div>
                </div>
            </div>

            <!-- Charts Row 1 -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Sales Chart -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Sales Overview</h3>
                    <canvas id="salesChart" height="300"></canvas>
                </div>

                <!-- Status Chart -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Orders by Status</h3>
                    <div class="h-[300px] flex justify-center">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Products -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Top Selling Products</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="pb-3 text-sm font-semibold text-gray-600">Product Name</th>
                                <th class="pb-3 text-sm font-semibold text-gray-600">Quantity Sold</th>
                                <th class="pb-3 text-sm font-semibold text-gray-600">Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody id="topProductsTable" class="divide-y divide-gray-100">
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

        let salesChartInstance = null;
        let statusChartInstance = null;

        function loadReports() {
            const formData = new FormData(document.getElementById('reportFilterForm'));
            const params = new URLSearchParams(formData).toString();

            fetch(`../api/admin/get_reports.php?${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateSummary(data.summary);
                        updateSalesChart(data.sales_chart);
                        updateStatusChart(data.status_chart);
                        updateTopProducts(data.top_products);
                    }
                })
                .catch(err => console.error('Error loading reports:', err));
        }

        function updateSummary(summary) {
            document.getElementById('totalRevenue').textContent = '£' + Number(summary.total_revenue).toLocaleString(undefined, {minimumFractionDigits: 2});
            document.getElementById('totalOrders').textContent = summary.total_orders;
            document.getElementById('avgOrderValue').textContent = '£' + Number(summary.avg_order_value).toLocaleString(undefined, {minimumFractionDigits: 2});
        }

        function updateSalesChart(data) {
            const ctx = document.getElementById('salesChart').getContext('2d');
            
            if (salesChartInstance) salesChartInstance.destroy();

            salesChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(d => formatDate(d.date)),
                    datasets: [{
                        label: 'Revenue (£)',
                        data: data.map(d => d.revenue),
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        fill: true,
                        tension: 0.4
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

        function updateStatusChart(data) {
            const ctx = document.getElementById('statusChart').getContext('2d');
            
            if (statusChartInstance) statusChartInstance.destroy();

            const colors = {
                'pending': '#fbbf24',
                'processing': '#3b82f6',
                'completed': '#10b981',
                'cancelled': '#ef4444',
                'refunded': '#6b7280'
            };

            statusChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.map(d => d.status.charAt(0).toUpperCase() + d.status.slice(1)),
                    datasets: [{
                        data: data.map(d => d.count),
                        backgroundColor: data.map(d => colors[d.status] || '#cbd5e1')
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        function updateTopProducts(products) {
            const tbody = document.getElementById('topProductsTable');
            tbody.innerHTML = '';
            
            if (products.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="py-4 text-center text-gray-500">No data available</td></tr>';
                return;
            }

            products.forEach(p => {
                const html = `
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 text-sm text-gray-800">${p.product_name}</td>
                        <td class="py-3 text-sm text-gray-600">${p.total_qty}</td>
                        <td class="py-3 text-sm font-semibold text-gray-800">£${Number(p.total_sales).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', html);
            });
        }

        function formatDate(dateString) {
            const options = { month: 'short', day: 'numeric' };
            return new Date(dateString).toLocaleDateString(undefined, options);
        }

        // Event Listeners
        document.getElementById('reportFilterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            loadReports();
        });

        // Initial Load
        loadReports();
    </script>
</body>
</html>
