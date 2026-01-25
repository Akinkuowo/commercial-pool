<?php
// admin/orders.php
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

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$payment_filter = isset($_GET['payment_status']) ? $_GET['payment_status'] : '';
$search = isset($_GET['s']) ? trim($_GET['s']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build query
$where_conditions = [];
$params = [];
$param_types = '';

if ($status_filter) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
    $param_types .= 's';
}

if ($payment_filter) {
    $where_conditions[] = "payment_status = ?";
    $params[] = $payment_filter;
    $param_types .= 's';
}

if ($search) {
    $where_conditions[] = "(id LIKE ? OR customer_name LIKE ? OR customer_email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= 'sss';
}

if ($date_from) {
    $where_conditions[] = "DATE(created_at) >= ?";
    $params[] = $date_from;
    $param_types .= 's';
}

if ($date_to) {
    $where_conditions[] = "DATE(created_at) <= ?";
    $params[] = $date_to;
    $param_types .= 's';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_query = "SELECT COUNT(*) as total FROM orders $where_clause";
if (!empty($params)) {
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->bind_param($param_types, ...$params);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_orders = $count_result->fetch_assoc()['total'];
    $count_stmt->close();
} else {
    $count_result = $conn->query($count_query);
    $total_orders = $count_result->fetch_assoc()['total'];
}

$total_pages = ceil($total_orders / $per_page);

// Get orders
$query = "SELECT * FROM orders $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$param_types .= 'ii';

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get order stats
$stats_query = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
    SUM(total_amount) as total_revenue
FROM orders";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

$admin_name = $_SESSION['admin_name'] ?? 'Admin User';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
?>

<!-- Sidebar -->
    <aside id="sidebar" class="sidebar fixed left-0 top-0 h-screen bg-gray-900 text-white shadow-xl overflow-y-auto">
        <div class="p-4 border-b border-gray-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-shield-halved text-2xl text-blue-400"></i>
                    <span class="logo-text ml-3 font-bold text-lg">Admin Dashboard</span>
                </div>
                <button id="sidebarToggle" class="lg:block hidden text-gray-400 hover:text-white">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>

        <nav class="p-4">
            <div class="mb-6">
                <div class="text-xs text-gray-500 uppercase mb-2 px-3 sidebar-text">Main</div>
                <a href="admin-dashboard.php" class="flex items-center px-3 py-2.5 bg-blue-600 rounded-lg mb-1">
                    <i class="fas fa-home w-5"></i>
                    <span class="sidebar-text ml-3">Dashboard</span>
                </a>
                <a href="orders.php" class="flex items-center px-3 py-2.5 text-gray-300 hover:bg-gray-800 rounded-lg mb-1">
                    <i class="fas fa-shopping-cart w-5"></i>
                    <span class="sidebar-text ml-3">Orders</span>
                    <?php if ($pending_count > 0): ?>
                        <span class="sidebar-text ml-auto bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">
                            <?php echo $pending_count; ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="products.php" class="flex items-center px-3 py-2.5 text-gray-300 hover:bg-gray-800 rounded-lg mb-1">
                    <i class="fas fa-box w-5"></i>
                    <span class="sidebar-text ml-3">Products</span>
                </a>
                <a href="customers.php" class="flex items-center px-3 py-2.5 text-gray-300 hover:bg-gray-800 rounded-lg mb-1">
                    <i class="fas fa-users w-5"></i>
                    <span class="sidebar-text ml-3">Customers</span>
                </a>
            </div>

            <div class="mb-6">
                <div class="text-xs text-gray-500 uppercase mb-2 px-3 sidebar-text">Content</div>
                <a href="categories.php" class="flex items-center px-3 py-2.5 text-gray-300 hover:bg-gray-800 rounded-lg mb-1">
                    <i class="fas fa-folder w-5"></i>
                    <span class="sidebar-text ml-3">Categories</span>
                </a>
                <a href="media.php" class="flex items-center px-3 py-2.5 text-gray-300 hover:bg-gray-800 rounded-lg mb-1">
                    <i class="fas fa-images w-5"></i>
                    <span class="sidebar-text ml-3">Media Library</span>
                </a>
            </div>

            <div class="mb-6">
                <div class="text-xs text-gray-500 uppercase mb-2 px-3 sidebar-text">Analytics</div>
                <a href="reports.php" class="flex items-center px-3 py-2.5 text-gray-300 hover:bg-gray-800 rounded-lg mb-1">
                    <i class="fas fa-chart-bar w-5"></i>
                    <span class="sidebar-text ml-3">Reports</span>
                </a>
                <a href="analytics.php" class="flex items-center px-3 py-2.5 text-gray-300 hover:bg-gray-800 rounded-lg mb-1">
                    <i class="fas fa-chart-line w-5"></i>
                    <span class="sidebar-text ml-3">Analytics</span>
                </a>
            </div>

            <div class="mb-6" id="adminOnlySection">
                <div class="text-xs text-gray-500 uppercase mb-2 px-3 sidebar-text">Administration</div>
                <a href="users.php" class="flex items-center px-3 py-2.5 text-gray-300 hover:bg-gray-800 rounded-lg mb-1">
                    <i class="fas fa-user-shield w-5"></i>
                    <span class="sidebar-text ml-3">Users</span>
                </a>
                <a href="settings.php" class="flex items-center px-3 py-2.5 text-gray-300 hover:bg-gray-800 rounded-lg mb-1">
                    <i class="fas fa-cog w-5"></i>
                    <span class="sidebar-text ml-3">Settings</span>
                </a>
                <a href="activity.php" class="flex items-center px-3 py-2.5 text-gray-300 hover:bg-gray-800 rounded-lg mb-1">
                    <i class="fas fa-history w-5"></i>
                    <span class="sidebar-text ml-3">Activity Log</span>
                </a>
            </div>
        </nav>
    </aside>