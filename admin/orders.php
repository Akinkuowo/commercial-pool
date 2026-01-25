<?php
// admin/orders.php
session_start();
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php?session=expired');
    exit;
}

// Get database connection
$conn = getDbConnection();

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
    COALESCE(SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END), 0) as total_revenue
FROM orders";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

$admin_name = $_SESSION['admin_name'] ?? 'Admin User';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders | Admin Dashboard</title>
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
                        <h1 class="text-2xl font-bold text-gray-800">Orders</h1>
                    </div>
                    <div class="flex items-center space-x-4">
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
            </div>
        </header>

        <main class="p-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Total Orders</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total_orders']); ?></h3>
                        </div>
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-shopping-cart text-blue-600"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Pending</p>
                            <h3 class="text-2xl font-bold text-yellow-600"><?php echo number_format($stats['pending']); ?></h3>
                        </div>
                        <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-yellow-600"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Processing</p>
                            <h3 class="text-2xl font-bold text-blue-600"><?php echo number_format($stats['processing']); ?></h3>
                        </div>
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-sync text-blue-600"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Completed</p>
                            <h3 class="text-2xl font-bold text-green-600"><?php echo number_format($stats['completed']); ?></h3>
                        </div>
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Revenue</p>
                            <h3 class="text-2xl font-bold text-gray-800">£<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                        </div>
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-pound-sign text-green-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                <form method="GET" action="orders.php" class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                        <div class="md:col-span-2">
                            <input type="text" 
                                   name="s" 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   placeholder="Search by order #, customer name or email..." 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Statuses</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="on_hold" <?php echo $status_filter === 'on_hold' ? 'selected' : ''; ?>>On Hold</option>
                                <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="refunded" <?php echo $status_filter === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                            </select>
                        </div>
                        <div>
                            <select name="payment_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Payment Status</option>
                                <option value="pending" <?php echo $payment_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="paid" <?php echo $payment_filter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                <option value="failed" <?php echo $payment_filter === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                <option value="refunded" <?php echo $payment_filter === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                            </select>
                        </div>
                        <div>
                            <input type="date" 
                                   name="date_from" 
                                   value="<?php echo htmlspecialchars($date_from); ?>"
                                   placeholder="From date"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <input type="date" 
                                   name="date_to" 
                                   value="<?php echo htmlspecialchars($date_to); ?>"
                                   placeholder="To date"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 mt-4">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                        <a href="orders.php" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition">
                            <i class="fas fa-times mr-2"></i>Clear
                        </a>
                        <button type="button" onclick="exportOrders()" class="ml-auto bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                            <i class="fas fa-file-export mr-2"></i>Export
                        </button>
                    </div>
                </form>
            </div>

            <!-- Orders Table -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input type="checkbox" id="selectAll" class="rounded border-gray-300">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Order</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Payment</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                        <i class="fas fa-inbox text-4xl mb-2"></i>
                                        <p>No orders found</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $order): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <input type="checkbox" class="order-checkbox rounded border-gray-300" value="<?php echo $order['id']; ?>">
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="order_details.php?id=<?php echo $order['id']; ?>" class="font-medium text-blue-600 hover:underline">
                                            <?php echo htmlspecialchars($order['id']); ?>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <?php echo date('M d, Y', strtotime($order['created_at'])); ?><br>
                                        <span class="text-xs text-gray-400"><?php echo date('H:i', strtotime($order['created_at'])); ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($order['customer_name']); ?></p>
                                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($order['customer_email']); ?></p>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-900">
                                        £<?php echo number_format($order['total_amount'], 2); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php
                                        $payment_colors = [
                                            'paid' => 'bg-green-100 text-green-800',
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'failed' => 'bg-red-100 text-red-800',
                                            'refunded' => 'bg-purple-100 text-purple-800'
                                        ];
                                        $payment_class = $payment_colors[$order['payment_status']] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                        <select class="payment-select px-2 py-1 text-xs font-medium border border-gray-200 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 <?php echo $payment_class; ?>" 
                                                data-order-id="<?php echo $order['id']; ?>">
                                            <option value="pending" <?php echo $order['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="paid" <?php echo $order['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                            <option value="failed" <?php echo $order['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                            <option value="refunded" <?php echo $order['payment_status'] === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                                        </select>
                                    </td>
                                    <td class="px-6 py-4">
                                        <select class="status-select px-3 py-1 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                                data-order-id="<?php echo $order['id']; ?>">
                                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="on_hold" <?php echo $order['status'] === 'on_hold' ? 'selected' : ''; ?>>On Hold</option>
                                            <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            <option value="refunded" <?php echo $order['status'] === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                                        </select>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-2">
                                            <a href="order_details.php?id=<?php echo $order['id']; ?>" 
                                               class="text-blue-600 hover:text-blue-800" 
                                               title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button onclick="printOrder(<?php echo $order['id']; ?>)" 
                                                    class="text-green-600 hover:text-green-800" 
                                                    title="Print">
                                                <i class="fas fa-print"></i>
                                            </button>
                                            <button onclick="sendEmail(<?php echo $order['id']; ?>)" 
                                                    class="text-purple-600 hover:text-purple-800" 
                                                    title="Email">
                                                <i class="fas fa-envelope"></i>
                                            </button>
                                            <?php if ($admin_role === 'admin'): ?>
                                            <button onclick="deleteOrder(<?php echo $order['id']; ?>)" 
                                                    class="text-red-600 hover:text-red-800" 
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php endif; ?>
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
                            Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $per_page, $total_orders); ?> of <?php echo $total_orders; ?> orders
                        </p>
                        <div class="flex space-x-2">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $status_filter; ?>&payment_status=<?php echo $payment_filter; ?>&s=<?php echo urlencode($search); ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>" 
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
                                <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&payment_status=<?php echo $payment_filter; ?>&s=<?php echo urlencode($search); ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>" 
                                   class="px-4 py-2 <?php echo $i === $page ? 'bg-blue-600 text-white' : 'border border-gray-300 hover:bg-gray-50'; ?> rounded-lg">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $status_filter; ?>&payment_status=<?php echo $payment_filter; ?>&s=<?php echo urlencode($search); ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>" 
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
    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-4 right-4 bg-gray-800 text-white px-6 py-3 rounded-lg shadow-lg transform translate-y-full opacity-0 transition-all duration-300 z-50">
        <div class="flex items-center space-x-3">
            <i id="toastIcon" class="fas fa-check-circle text-green-400"></i>
            <span id="toastMessage">Action successful</span>
        </div>
    </div>
    
    </div>

    <script>
        // Mobile sidebar toggle
        document.getElementById('mobileSidebarToggle')?.addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('mobile-open');
        });

        // User menu toggle
        document.getElementById('userMenuBtn')?.addEventListener('click', () => {
            document.getElementById('userMenu').classList.toggle('hidden');
        });

        // Select all checkboxes
        document.getElementById('selectAll')?.addEventListener('change', function() {
            document.querySelectorAll('.order-checkbox').forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Show Toast Function
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const icon = document.getElementById('toastIcon');
            const msg = document.getElementById('toastMessage');

            msg.textContent = message;
            if (type === 'success') {
                icon.className = 'fas fa-check-circle text-green-400';
            } else {
                icon.className = 'fas fa-exclamation-circle text-red-400';
            }

            toast.classList.remove('translate-y-full', 'opacity-0');
            setTimeout(() => {
                toast.classList.add('translate-y-full', 'opacity-0');
            }, 3000);
        }

        // Status update
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', async function() {
                const orderId = this.dataset.orderId;
                const newStatus = this.value;
                
                // Optional: remove confirm or keep it. Let's keep it but maybe stylize later. For now standard confirm is fine for safety.
                // Or better, just do it with toast confirmation? No, status change is significant.
                
                try {
                    const response = await fetch('../api/admin/update_order_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            order_id: orderId,
                            status: newStatus
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        showToast('Order status updated successfully');
                        // No reload needed if just status changed, but visual feedback is good.
                        // Ideally we change color class too but for now this is fine.
                    } else {
                        showToast('Error: ' + data.message, 'error');
                        // Revert?
                        location.reload(); 
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('Failed to update order status', 'error');
                }
            });
        });

        // Payment Status update
        document.querySelectorAll('.payment-select').forEach(select => {
            select.addEventListener('change', async function() {
                const orderId = this.dataset.orderId;
                const newStatus = this.value;
                
                try {
                    const response = await fetch('../api/admin/update_payment_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            order_id: orderId,
                            status: newStatus
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        showToast('Payment status updated successfully');
                        // Optional: update class for color change
                        // For now, reload to reflect correct color class
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast('Error: ' + data.message, 'error');
                        location.reload(); 
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('Failed to update payment status', 'error');
                }
            });
        });

        // Print order function
        async function printOrder(orderId) {
            try {
                const response = await fetch(`../api/admin/get_order.php?id=${orderId}`);
                const data = await response.json();
                
                if (data.success) {
                    const printWindow = window.open('', '_blank');
                    printWindow.document.write(`
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <title>Order ${data.order.id}</title>
                            <style>
                                body { font-family: Arial, sans-serif; padding: 20px; }
                                .header { text-align: center; margin-bottom: 30px; }
                                .order-info { margin-bottom: 20px; }
                                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                                th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
                                th { background-color: #f3f4f6; }
                                .total { font-weight: bold; font-size: 18px; }
                            </style>
                        </head>
                        <body>
                            <div class="header">
                                <h1>Order Invoice</h1>
                                <p>Order #${data.order.id}</p>
                            </div>
                            <div class="order-info">
                                <p><strong>Date:</strong> ${new Date(data.order.created_at).toLocaleDateString()}</p>
                                <p><strong>Customer:</strong> ${data.order.customer_name}</p>
                                <p><strong>Email:</strong> ${data.order.customer_email}</p>
                                <p><strong>Phone:</strong> ${data.order.customer_phone || 'N/A'}</p>
                            </div>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.items.map(item => `
                                        <tr>
                                            <td>${item.product_name}</td>
                                            <td>${item.quantity}</td>
                                            <td>£${parseFloat(item.price).toFixed(2)}</td>
                                            <td>£${(item.quantity * parseFloat(item.price)).toFixed(2)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                                <tfoot>
                                    <tr class="total">
                                        <td colspan="3">Total</td>
                                        <td>£${parseFloat(data.order.total).toFixed(2)}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </body>
                        </html>
                    `);
                    printWindow.document.close();
                    printWindow.print();
                } else {
                    alert('Error loading order details');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to print order');
            }
        }

        // Send email function
        // Send email function
        async function sendEmail(orderId) {
            if (!confirm('Send order confirmation email to customer?')) return;
            
            try {
                const response = await fetch('../api/admin/send_order_email.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ order_id: orderId })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('Email sent successfully');
                } else {
                    showToast('Error: ' + data.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Failed to send email', 'error');
            }
        }

        // Delete order function
        // Delete order function
        async function deleteOrder(orderId) {
            if (!confirm('Are you sure you want to delete this order? This action cannot be undone.')) return;
            
            try {
                const response = await fetch('../api/admin/delete_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ order_id: orderId })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('Order deleted successfully');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Error: ' + data.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Failed to delete order', 'error');
            }
        }

        // Export orders function
        function exportOrders() {
            const urlParams = new URLSearchParams(window.location.search);
            const exportUrl = '../api/admin/export_orders.php?' + urlParams.toString();
            window.location.href = exportUrl;
        }

        // Close user menu when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.getElementById('userMenu');
            const userMenuBtn = document.getElementById('userMenuBtn');
            
            if (userMenu && !userMenu.contains(event.target) && !userMenuBtn.contains(event.target)) {
                userMenu.classList.add('hidden');
            }
        });

        // Close mobile sidebar when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('mobileSidebarToggle');
            
            if (window.innerWidth < 1024 && sidebar.classList.contains('mobile-open')) {
                if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
                    sidebar.classList.remove('mobile-open');
                }
            }
        });
    </script>
</body>
</html>