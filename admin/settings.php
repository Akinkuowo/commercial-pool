<?php
// admin/settings.php
session_start();
require_once '../config.php';
require_once 'include/auth_check.php';

// Check if user is logged in
checkAdminAuth();

$admin_name = $_SESSION['admin_name'] ?? 'Admin User';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
$current_page = 'settings';

// Ideally, only admins can view/edit settings
if ($admin_role !== 'admin') {
    // Redirect or show restricted access
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | Admin Dashboard</title>
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
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .tab-btn.active { border-color: #2563eb; color: #2563eb; background-color: #eff6ff; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Sidebar -->
    <?php include('include/sidebar.php') ?>

    <!-- Main Content -->
    <div id="mainContent" class="main-content min-h-screen">
        <!-- Header -->
        <?php 
        $header_title = "Settings";
        $header_description = "Configure site preferences";
        include('include/header.php'); 
        ?>

        <main class="p-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <!-- Tabs -->
                <div class="flex border-b border-gray-200 overflow-x-auto">
                    <button onclick="switchTab('general')" class="tab-btn active px-6 py-4 text-sm font-medium text-gray-600 border-b-2 border-transparent hover:text-gray-800 flex items-center whitespace-nowrap transition-colors">
                        <i class="fas fa-sliders-h mr-2"></i>General
                    </button>
                    <button onclick="switchTab('contact')" class="tab-btn px-6 py-4 text-sm font-medium text-gray-600 border-b-2 border-transparent hover:text-gray-800 flex items-center whitespace-nowrap transition-colors">
                        <i class="fas fa-address-book mr-2"></i>Contact Info
                    </button>
                    <button onclick="switchTab('seo')" class="tab-btn px-6 py-4 text-sm font-medium text-gray-600 border-b-2 border-transparent hover:text-gray-800 flex items-center whitespace-nowrap transition-colors">
                        <i class="fas fa-search mr-2"></i>SEO Defaults
                    </button>
                    <button onclick="switchTab('analytics')" class="tab-btn px-6 py-4 text-sm font-medium text-gray-600 border-b-2 border-transparent hover:text-gray-800 flex items-center whitespace-nowrap transition-colors">
                        <i class="fas fa-chart-line mr-2"></i>Analytics
                    </button>
                </div>

                <form id="settingsForm" class="p-6">
                    <!-- General Settings -->
                    <div id="general" class="tab-content active space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Site Name</label>
                                <input type="text" name="site_name" id="site_name"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Currency Symbol</label>
                                <input type="text" name="currency_symbol" id="currency_symbol"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Contact Info -->
                    <div id="contact" class="tab-content space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Support Email</label>
                                <input type="email" name="support_email" id="support_email"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input type="text" name="contact_phone" id="contact_phone"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Physical Address</label>
                            <textarea name="address" id="address" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                    </div>

                    <!-- SEO Settings -->
                    <div id="seo" class="tab-content space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Default Meta Title</label>
                            <input type="text" name="seo_title" id="seo_title"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Default Meta Description</label>
                            <textarea name="seo_description" id="seo_description" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                    </div>

                    <!-- Analytics Settings -->
                    <div id="analytics" class="tab-content space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Google Analytics Measurement ID</label>
                            <input type="text" name="google_analytics_id" id="google_analytics_id"
                                placeholder="e.g., G-XXXXXXXXXX or UA-XXXXXXXXX-X"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Enter your Google Analytics 4 Measurement ID or Universal Analytics Tracking ID to enable tracking on all pages.</p>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition flex items-center">
                            <i class="fas fa-save mr-2"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <!-- Toast -->
    <div id="toast" class="fixed bottom-4 right-4 bg-gray-800 text-white px-6 py-3 rounded-lg shadow-lg transform translate-y-full opacity-0 transition-all duration-300 z-50">
        <div class="flex items-center space-x-3">
            <i id="toastIcon" class="fas fa-check-circle text-[#022658]"></i>
            <span id="toastMessage">Action successful</span>
        </div>
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

        function switchTab(tabId) {
            // Hide all
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            
            // Show selected
            document.getElementById(tabId).classList.add('active');
            
            // Find button (simple match)
            const btns = document.querySelectorAll('.tab-btn');
            // Assuming order matches or use data attributes for robustness, but here simple index logic or text content
            // Let's iterate and check onclick attribute content for simplicity in this swift implementation
            btns.forEach(btn => {
                if (btn.getAttribute('onclick').includes(tabId)) {
                    btn.classList.add('active');
                }
            });
        }

        function loadSettings() {
            fetch('../api/admin/get_settings.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const s = data.settings;
                        // Populate fields
                        Object.keys(s).forEach(key => {
                            const el = document.getElementById(key);
                            if (el) el.value = s[key];
                        });
                    }
                })
                .catch(console.error);
        }

        document.getElementById('settingsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = 'Saving...';
            btn.disabled = true;

            const formData = new FormData(this);

            fetch('../api/admin/update_settings.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Settings updated successfully');
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(() => showToast('Update failed', 'error'))
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        });

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const icon = document.getElementById('toastIcon');
            const msg = document.getElementById('toastMessage');

            msg.textContent = message;
            if (type === 'success') {
                icon.className = 'fas fa-check-circle text-[#022658]';
            } else {
                icon.className = 'fas fa-exclamation-circle text-red-400';
            }

            toast.classList.remove('translate-y-full', 'opacity-0');
            setTimeout(() => {
                toast.classList.add('translate-y-full', 'opacity-0');
            }, 3000);
        }

        loadSettings();
    </script>
</body>
</html>
