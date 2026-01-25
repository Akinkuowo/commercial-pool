<?php
// admin/customers.php
session_start();
require_once '../config.php';
require_once 'include/auth_check.php';

// Check if user is logged in
checkAdminAuth();

// Get database connection
$conn = getDbConnection();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Filters
$search = isset($_GET['s']) ? trim($_GET['s']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build query
$where_conditions = [];
$params = [];
$param_types = '';

// Default filter: only show regular users/customers (not admins)
// Assuming admins are in a separate table 'admin_users', so 'users' table is just customers.
// But just in case, we can filter if needed. For now, we assume all in 'users' are customers.

if ($search) {
    $where_conditions[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR business_name LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= 'ssss';
}

if ($status_filter !== '') {
    if ($status_filter === 'active') {
        $where_conditions[] = "is_active = 1";
    } elseif ($status_filter === 'inactive') {
        $where_conditions[] = "is_active = 0";
    }
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_query = "SELECT COUNT(*) as total FROM users $where_clause";
if (!empty($params)) {
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->bind_param($param_types, ...$params);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_customers = $count_result->fetch_assoc()['total'];
    $count_stmt->close();
} else {
    $count_result = $conn->query($count_query);
    $total_customers = $count_result->fetch_assoc()['total'];
}

$total_pages = ceil($total_customers / $per_page);

// Get customers
$query = "SELECT * FROM users $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$param_types .= 'ii';

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$customers = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get customer stats (avoiding $stats variable name collision)
$customer_stats = [
    'total_customers' => 0,
    'active' => 0,
    'inactive' => 0,
    'trade' => 0,
    'new_this_month' => 0
];

$stats_query = "SELECT 
    COALESCE(COUNT(*), 0) as total_customers,
    COALESCE(SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END), 0) as active,
    COALESCE(SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END), 0) as inactive,
    COALESCE(SUM(CASE WHEN is_trade_customer = 1 THEN 1 ELSE 0 END), 0) as trade,
    COALESCE(SUM(CASE WHEN created_at >= DATE_FORMAT(NOW(), '%Y-%m-01') THEN 1 ELSE 0 END), 0) as new_this_month
FROM users";

$stats_result = $conn->query($stats_query);
if ($stats_result && $stats_result->num_rows > 0) {
    $fetched_stats = $stats_result->fetch_assoc();
    if ($fetched_stats) {
        $customer_stats = array_merge($customer_stats, $fetched_stats);
    }
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin User';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
$current_page = 'customers';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers | Admin Dashboard</title>
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
                            <h1 class="text-2xl font-bold text-gray-800">Customers</h1>
                            <p class="text-gray-600 mt-1">Manage your registered customers</p>
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
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Total Customers</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($customer_stats['total_customers']); ?></h3>
                        </div>
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-users text-blue-600"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Active Accounts</p>
                            <h3 class="text-2xl font-bold text-green-600"><?php echo number_format($customer_stats['active']); ?></h3>
                        </div>
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-check text-green-600"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Trade Accounts</p>
                            <h3 class="text-2xl font-bold text-purple-600"><?php echo number_format($customer_stats['trade']); ?></h3>
                        </div>
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-briefcase text-purple-600"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">New This Month</p>
                            <h3 class="text-2xl font-bold text-orange-600"><?php echo number_format($customer_stats['new_this_month']); ?></h3>
                        </div>
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar-plus text-orange-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                <form method="GET" action="customers.php" class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="md:col-span-2">
                            <input type="text" 
                                   name="s" 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   placeholder="Search by name, email, or business..." 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Status</option>
                                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition w-full md:w-auto">
                                <i class="fas fa-filter mr-2"></i>Filter
                            </button>
                            <a href="customers.php" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition w-full md:w-auto text-center">
                                Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Customers Table -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Customer</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Contact Info</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Business Info</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Joined</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (empty($customers)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                        <i class="fas fa-users-slash text-4xl mb-2"></i>
                                        <p>No customers found</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($customers as $customer): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-600 font-bold">
                                                <?php echo strtoupper(substr($customer['first_name'], 0, 1) . substr($customer['last_name'], 0, 1)); ?>
                                            </div>
                                            <div class="ml-4">
                                                <p class="font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>
                                                </p>
                                                <?php if ($customer['is_trade_customer']): ?>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                                        Trade
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-gray-900">
                                            <i class="fas fa-envelope text-gray-400 mr-2 w-4"></i>
                                            <?php echo htmlspecialchars($customer['email']); ?>
                                        </p>
                                        <p class="text-sm text-gray-600 mt-1">
                                            <i class="fas fa-phone text-gray-400 mr-2 w-4"></i>
                                            <?php echo htmlspecialchars($customer['phone_number']); ?>
                                        </p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-gray-900 font-medium"><?php echo htmlspecialchars($customer['company_name'] ?? 'N/A'); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($customer['business_type'] ?? ''); ?></p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($customer['is_active']): ?>
                                            <span class="px-3 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                                Active
                                            </span>
                                        <?php else: ?>
                                            <span class="px-3 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                                Inactive
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <?php echo date('M d, Y', strtotime($customer['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-2">
                                            <a href="#" class="text-blue-600 hover:text-blue-800" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="px-6 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-600">
                            Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $per_page, $total_customers); ?> of <?php echo $total_customers; ?> customers
                        </p>
                        <div class="flex space-x-2">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $status_filter; ?>&s=<?php echo urlencode($search); ?>" 
                                   class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                    Previous
                                </a>
                            <?php else: ?>
                                <button disabled class="px-4 py-2 border border-gray-300 rounded-lg opacity-50 cursor-not-allowed">
                                    Previous
                                </button>
                            <?php endif; ?>

                            <?php
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);
                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                                <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&s=<?php echo urlencode($search); ?>" 
                                   class="px-4 py-2 <?php echo $i === $page ? 'bg-blue-600 text-white' : 'border border-gray-300 hover:bg-gray-50'; ?> rounded-lg">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $status_filter; ?>&s=<?php echo urlencode($search); ?>" 
                                   class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                    Next
                                </a>
                            <?php else: ?>
                                <button disabled class="px-4 py-2 border border-gray-300 rounded-lg opacity-50 cursor-not-allowed">
                                    Next
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
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
    </script>
</body>
</html>
