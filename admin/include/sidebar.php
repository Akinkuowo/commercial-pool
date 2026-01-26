<?php
// admin/include/sidebar.php

// Ensure database connection
if (!isset($conn)) {
    // If conn not set, try to get it, assuming config is accessible relative to this file's inclusion context
    // Usually config is included in parent, but let's be safe
    if (file_exists('../config.php')) {
        require_once '../config.php';
        $conn = getDbConnection();
    } elseif (file_exists('../../config.php')) {
        require_once '../../config.php';
        $conn = getDbConnection();
    }
}

// Get pending orders count if connection is valid
$pending_count = 0;
if (isset($conn) && $conn instanceof mysqli) {
    $pending_query = "SELECT COUNT(*) as pending_count FROM orders WHERE status = 'Pending'";
    $pending_result = $conn->query($pending_query);
    if ($pending_result) {
        $pending_count = $pending_result->fetch_assoc()['pending_count'];
    }
}

// Helper for active class
function getActiveClass($page, $current) {
    return $page === $current ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800';
}

$c = $current_page ?? ''; // Prevent notice if not set
?>

<aside id="sidebar" class="sidebar fixed left-0 top-0 h-screen bg-gray-900 text-white shadow-xl overflow-y-auto z-50">
    <div class="p-4 border-b border-gray-700">
        <div class="flex items-center justify-between">
            <div class="flex items-center overflow-hidden">
                <i class="fas fa-shield-halved text-2xl text-blue-400 min-w-[24px]"></i>
                <span class="logo-text ml-3 font-bold text-lg whitespace-nowrap transition-opacity duration-300">Admin Panel</span>
            </div>
            <button id="sidebarToggle" class="lg:block hidden text-gray-400 hover:text-white focus:outline-none">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>

    <nav class="p-4">
        <div class="mb-6">
            <div class="text-xs text-gray-500 uppercase mb-2 px-3 sidebar-text truncate">Main</div>
            
            <a href="admin-dashboard.php" class="flex items-center px-3 py-2.5 rounded-lg mb-1 group <?php echo getActiveClass('dashboard', $c); ?>">
                <i class="fas fa-home w-5 text-center"></i>
                <span class="sidebar-text ml-3 whitespace-nowrap group-hover:block">Dashboard</span>
            </a>
            
            <a href="orders.php" class="flex items-center px-3 py-2.5 rounded-lg mb-1 group <?php echo getActiveClass('orders', $c); ?>">
                <i class="fas fa-shopping-cart w-5 text-center"></i>
                <span class="sidebar-text ml-3 whitespace-nowrap">Orders</span>
                <?php if ($pending_count > 0): ?>
                    <span class="sidebar-text ml-auto bg-red-500 text-white text-xs px-2 py-0.5 rounded-full"><?php echo $pending_count; ?></span>
                <?php endif; ?>
            </a>
            
            <a href="products.php" class="flex items-center px-3 py-2.5 rounded-lg mb-1 group <?php echo getActiveClass('products', $c); ?>">
                <i class="fas fa-box w-5 text-center"></i>
                <span class="sidebar-text ml-3 whitespace-nowrap">Products</span>
            </a>
            
            <a href="customers.php" class="flex items-center px-3 py-2.5 rounded-lg mb-1 group <?php echo getActiveClass('customers', $c); ?>">
                <i class="fas fa-users w-5 text-center"></i>
                <span class="sidebar-text ml-3 whitespace-nowrap">Customers</span>
            </a>
        </div>

        <div class="mb-6">
            <div class="text-xs text-gray-500 uppercase mb-2 px-3 sidebar-text truncate">Content</div>
            
            <a href="categories.php" class="flex items-center px-3 py-2.5 rounded-lg mb-1 group <?php echo getActiveClass('categories', $c); ?>">
                <i class="fas fa-folder w-5 text-center"></i>
                <span class="sidebar-text ml-3 whitespace-nowrap">Categories</span>
            </a>
            
            <a href="media.php" class="flex items-center px-3 py-2.5 rounded-lg mb-1 group <?php echo getActiveClass('media', $c); ?>">
                <i class="fas fa-images w-5 text-center"></i>
                <span class="sidebar-text ml-3 whitespace-nowrap">Media Library</span>
            </a>
        </div>

        <div class="mb-6">
            <div class="text-xs text-gray-500 uppercase mb-2 px-3 sidebar-text truncate">Analytics</div>
            
            <a href="reports.php" class="flex items-center px-3 py-2.5 rounded-lg mb-1 group <?php echo getActiveClass('reports', $c); ?>">
                <i class="fas fa-chart-bar w-5 text-center"></i>
                <span class="sidebar-text ml-3 whitespace-nowrap">Reports</span>
            </a>
            
            <a href="analytics.php" class="flex items-center px-3 py-2.5 rounded-lg mb-1 group <?php echo getActiveClass('analytics', $c); ?>">
                <i class="fas fa-chart-line w-5 text-center"></i>
                <span class="sidebar-text ml-3 whitespace-nowrap">Analytics</span>
            </a>
        </div>

        <div class="mb-6" id="adminOnlySection">
            <div class="text-xs text-gray-500 uppercase mb-2 px-3 sidebar-text truncate">Administration</div>
            
            <a href="users.php" class="flex items-center px-3 py-2.5 rounded-lg mb-1 group <?php echo getActiveClass('users', $c); ?>">
                <i class="fas fa-user-shield w-5 text-center"></i>
                <span class="sidebar-text ml-3 whitespace-nowrap">Users</span>
            </a>
            
            <a href="settings.php" class="flex items-center px-3 py-2.5 rounded-lg mb-1 group <?php echo getActiveClass('settings', $c); ?>">
                <i class="fas fa-cog w-5 text-center"></i>
                <span class="sidebar-text ml-3 whitespace-nowrap">Settings</span>
            </a>
            
            <a href="activity.php" class="flex items-center px-3 py-2.5 rounded-lg mb-1 group <?php echo getActiveClass('activity', $c); ?>">
                <i class="fas fa-history w-5 text-center"></i>
                <span class="sidebar-text ml-3 whitespace-nowrap">Activity Log</span>
            </a>
        </div>
    </nav>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const sidebarTexts = document.querySelectorAll('.sidebar-text');
    const logoText = document.querySelector('.logo-text');
    
    // Check local storage
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isCollapsed) {
        toggleSidebar(false); // false = don't animate on initial load
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            toggleSidebar(true);
        });
    }

    function toggleSidebar(animate) {
        if (animate) {
            sidebar.classList.add('transition-all', 'duration-300');
            if (mainContent) mainContent.classList.add('transition-all', 'duration-300');
        }

        const collapsed = sidebar.style.width === '80px' || sidebar.classList.contains('w-[80px]'); // Check current state

        if (!collapsed) {
            // Collapse
            sidebar.style.width = '80px';
            if (mainContent) mainContent.style.marginLeft = '80px';
            
            sidebarTexts.forEach(el => el.classList.add('hidden'));
            if (logoText) logoText.classList.add('hidden');
            
            localStorage.setItem('sidebarCollapsed', 'true');
        } else {
            // Expand
            sidebar.style.width = '260px';
            if (mainContent) mainContent.style.marginLeft = '260px';
            
            sidebarTexts.forEach(el => el.classList.remove('hidden'));
            if (logoText) logoText.classList.remove('hidden');
            
            localStorage.setItem('sidebarCollapsed', 'false');
        }
    }
});
</script> 