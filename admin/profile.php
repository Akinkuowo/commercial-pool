<?php
// admin/profile.php
session_start();
require_once '../config.php';
require_once 'include/auth_check.php';

// Check if user is logged in
checkAdminAuth();

$admin_name = getAdminName();
$admin_role = getAdminRole();
$admin_initials = getAdminInitials();
$current_page = 'profile';

// Get admin details from session (or DB if needed)
$admin_email = $_SESSION['admin_email'] ?? 'admin@example.com';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Admin Dashboard</title>
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
        <?php 
        $header_title = "My Profile";
        include('include/header.php'); 
        ?>

        <!-- Profile Content -->
        <main class="p-6">
            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <!-- Profile Header -->
                    <div class="bg-[#022658] h-32 relative">
                        <div class="absolute -bottom-12 left-8">
                            <div class="w-24 h-24 bg-blue-600 rounded-2xl border-4 border-white flex items-center justify-center text-white text-3xl font-bold shadow-lg">
                                <?php echo $admin_initials; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="pt-16 pb-8 px-8">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($admin_name); ?></h2>
                                <p class="text-gray-500"><?php echo ucfirst($admin_role); ?></p>
                            </div>
                            <div class="mt-4 md:mt-0">
                                <button class="bg-[#022658] text-white px-6 py-2 rounded-lg hover:bg-blue-800 transition-colors font-medium">
                                    Edit Profile
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 border-t border-gray-100 pt-8">
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-400 uppercase mb-1">Full Name</label>
                                    <p class="text-gray-800 font-medium"><?php echo htmlspecialchars($admin_name); ?></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400 uppercase mb-1">Email Address</label>
                                    <p class="text-gray-800 font-medium"><?php echo htmlspecialchars($admin_email); ?></p>
                                </div>
                            </div>
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-400 uppercase mb-1">Account Role</label>
                                    <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
                                        <?php echo ucfirst($admin_role); ?>
                                    </span>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400 uppercase mb-1">Status</label>
                                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">
                                        Active
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity or Settings Shortcuts -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 text-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600 mx-auto mb-4">
                            <i class="fas fa-key text-xl"></i>
                        </div>
                        <h3 class="font-bold text-gray-800 mb-2">Password</h3>
                        <p class="text-sm text-gray-500 mb-4">Update your security credentials</p>
                        <a href="settings.php" class="text-blue-600 text-sm font-medium hover:underline">Change Password</a>
                    </div>
                    
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 text-center">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center text-purple-600 mx-auto mb-4">
                            <i class="fas fa-bell text-xl"></i>
                        </div>
                        <h3 class="font-bold text-gray-800 mb-2">Notifications</h3>
                        <p class="text-sm text-gray-500 mb-4">Manage your alert preferences</p>
                        <a href="settings.php" class="text-blue-600 text-sm font-medium hover:underline">Manage Alerts</a>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 text-center">
                        <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center text-orange-600 mx-auto mb-4">
                            <i class="fas fa-shield-alt text-xl"></i>
                        </div>
                        <h3 class="font-bold text-gray-800 mb-2">Security</h3>
                        <p class="text-sm text-gray-500 mb-4">Two-factor authentication settings</p>
                        <a href="settings.php" class="text-blue-600 text-sm font-medium hover:underline">Setup 2FA</a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Use the same sidebar scripts as others
        document.getElementById('sidebarToggle')?.addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('mainContent').classList.toggle('expanded');
        });
        document.getElementById('mobileSidebarToggle')?.addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('mobile-open');
        });
    </script>
</body>
</html>
