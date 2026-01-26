<?php
// admin/activity.php
session_start();
require_once '../config.php';
require_once 'include/auth_check.php';

// Check if user is logged in
checkAdminAuth();

$admin_name = $_SESSION['admin_name'] ?? 'Admin User';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
$current_page = 'activity';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log | Admin Dashboard</title>
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
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">Activity Log</h1>
                            <p class="text-gray-600 mt-1">Audit trail of system actions</p>
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
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="flex justify-between items-center px-6 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-800">Recent Activity</h2>
                    <button onclick="loadActivity(1)" class="text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-sync-alt mr-1"></i> Refresh
                    </button>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase">User</th>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase">Action</th>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase">Description</th>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase">Date</th>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase">IP Address</th>
                            </tr>
                        </thead>
                        <tbody id="activityTable" class="divide-y divide-gray-100">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-between items-center">
                    <span class="text-sm text-gray-600" id="paginationInfo">Showing 0 of 0</span>
                    <div class="space-x-2">
                        <button id="prevBtn" class="px-4 py-2 border border-gray-300 rounded-lg disabled:opacity-50" disabled>Previous</button>
                        <button id="nextBtn" class="px-4 py-2 border border-gray-300 rounded-lg disabled:opacity-50" disabled>Next</button>
                    </div>
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

        let currentPage = 1;

        function loadActivity(page) {
            currentPage = page;
            const tbody = document.getElementById('activityTable');
            tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500"><i class="fas fa-spinner fa-spin mr-2"></i>Loading...</td></tr>';

            fetch(`../api/admin/get_activity_log.php?page=${page}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderActivity(data.logs);
                        updatePagination(data.pagination);
                    }
                })
                .catch(err => {
                    tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-red-500">Failed to load activity log</td></tr>';
                    console.error(err);
                });
        }

        function renderActivity(logs) {
            const tbody = document.getElementById('activityTable');
            tbody.innerHTML = '';
            
            if (logs.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No activity recorded</td></tr>';
                return;
            }

            logs.forEach(log => {
                const date = new Date(log.created_at).toLocaleString();
                
                // Color coding actions
                let actionColor = 'text-gray-600 bg-gray-100';
                if (log.action.includes('login')) actionColor = 'text-[#022658] bg-[#022658]/10';
                if (log.action.includes('delete')) actionColor = 'text-red-600 bg-red-100';
                if (log.action.includes('update')) actionColor = 'text-blue-600 bg-blue-100';
                if (log.action.includes('create')) actionColor = 'text-purple-600 bg-purple-100';

                const html = `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <span class="font-medium text-gray-900">${log.admin_name}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${actionColor} capitalize">
                                ${log.action.replace('_', ' ')}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate" title="${log.description}">
                            ${log.description}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            ${date}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-400 font-mono">
                            ${log.ip_address}
                        </td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', html);
            });
        }

        function updatePagination(pagination) {
            document.getElementById('paginationInfo').textContent = `Page ${pagination.current_page} of ${pagination.total_pages} (${pagination.total_records} records)`;
            
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');

            prevBtn.disabled = pagination.current_page <= 1;
            nextBtn.disabled = pagination.current_page >= pagination.total_pages;

            prevBtn.onclick = () => loadActivity(pagination.current_page - 1);
            nextBtn.onclick = () => loadActivity(pagination.current_page + 1);
        }

        // Initial Load
        loadActivity(1);
    </script>
</body>
</html>
