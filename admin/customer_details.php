<?php
// admin/customer_details.php
session_start();
require_once '../config.php';
require_once 'include/auth_check.php';

// Check if user is logged in
checkAdminAuth();

// Get database connection
$conn = getDbConnection();

$customer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($customer_id <= 0) {
    header('Location: customers.php');
    exit;
}

// Fetch customer details
$customer_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($customer_query);
$stmt->bind_param('i', $customer_id);
$stmt->execute();
$customer_result = $stmt->get_result();
$customer = $customer_result->fetch_assoc();
$stmt->close();

if (!$customer) {
    header('Location: customers.php');
    exit;
}

// Fetch order history
$orders_query = "SELECT * FROM orders WHERE customer_email = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($orders_query);
$stmt->bind_param('s', $customer['email']);
$stmt->execute();
$orders_result = $stmt->get_result();
$orders = $orders_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate totals
$total_spent = 0;
foreach ($orders as $order) {
    if ($order['payment_status'] === 'paid') {
        $total_spent += $order['total_amount'];
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
    <title>Customer Details | Admin Dashboard</title>
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
        $header_title = "Customer Details";
        $header_description = "View profile and order history for " . htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']);
        include('include/header.php'); 
        ?>

        <main class="p-6">
            <div class="max-w-6xl mx-auto">
                <div class="mb-6">
                    <a href="customers.php" class="text-gray-600 hover:text-gray-900 flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Customers
                    </a>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Profile Info -->
                    <div class="lg:col-span-1 space-y-6">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <div class="flex flex-col items-center text-center mb-6">
                                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center text-gray-400 text-4xl font-bold mb-4">
                                    <?php echo strtoupper(substr($customer['first_name'], 0, 1) . substr($customer['last_name'], 0, 1)); ?>
                                </div>
                                <h2 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></h2>
                                <p class="text-gray-500"><?php echo htmlspecialchars($customer['business_name'] ?? 'Individual Customer'); ?></p>
                                <div class="mt-4 flex space-x-2">
                                    <?php if ($customer['is_active']): ?>
                                        <span class="px-3 py-1 text-xs font-medium bg-[#022658]/10 text-[#022658] rounded-full">Active</span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Inactive</span>
                                    <?php endif; ?>
                                    <?php if ($customer['is_trade_customer']): ?>
                                        <span class="px-3 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full">Trade</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="space-y-4 pt-6 border-t border-gray-100">
                                <div>
                                    <label class="text-xs font-semibold text-gray-400 uppercase">Email Address</label>
                                    <p class="text-sm text-gray-900 mt-1"><?php echo htmlspecialchars($customer['email']); ?></p>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-400 uppercase">Phone Number</label>
                                    <p class="text-sm text-gray-900 mt-1"><?php echo htmlspecialchars($customer['phone_number'] ?? 'N/A'); ?></p>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-400 uppercase">Joined Date</label>
                                    <p class="text-sm text-gray-900 mt-1"><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></p>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-400 uppercase">Total Lifetime Spend</label>
                                    <p class="text-lg font-bold text-blue-600 mt-1">£<?php echo number_format($total_spent, 2); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order History -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                                <h2 class="text-lg font-semibold text-gray-800">Order History</h2>
                                <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs font-medium">
                                    <?php echo count($orders); ?> Orders
                                </span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left">
                                    <thead class="bg-gray-50 border-b border-gray-200">
                                        <tr>
                                            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Order ID</th>
                                            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Date</th>
                                            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Status</th>
                                            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Payment</th>
                                            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Total</th>
                                            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <?php if (empty($orders)): ?>
                                            <tr>
                                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                                    No orders found for this customer.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($orders as $order): ?>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4">
                                                        <span class="font-medium text-gray-900">#<?php echo $order['id']; ?></span>
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-600">
                                                        <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <?php
                                                        $status_colors = [
                                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                                            'processing' => 'bg-blue-100 text-blue-800',
                                                            'completed' => 'bg-[#022658]/10 text-[#022658]',
                                                            'cancelled' => 'bg-red-100 text-red-800',
                                                            'refunded' => 'bg-purple-100 text-purple-800'
                                                        ];
                                                        $status_class = $status_colors[$order['status']] ?? 'bg-gray-100 text-gray-800';
                                                        ?>
                                                        <span class="px-2 py-0.5 rounded text-xs font-medium <?php echo $status_class; ?>">
                                                            <?php echo ucfirst($order['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <?php
                                                        $pay_status_colors = [
                                                            'paid' => 'bg-[#022658]/10 text-[#022658]',
                                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                                            'failed' => 'bg-red-100 text-red-800'
                                                        ];
                                                        $pay_status_class = $pay_status_colors[$order['payment_status']] ?? 'bg-gray-100 text-gray-800';
                                                        ?>
                                                        <span class="px-2 py-0.5 rounded text-xs font-medium <?php echo $pay_status_class; ?>">
                                                            <?php echo ucfirst($order['payment_status']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 font-medium text-gray-900">
                                                        £<?php echo number_format($order['total_amount'], 2); ?>
                                                    </td>
                                                    <td class="px-6 py-4 text-right">
                                                        <a href="order_details.php?id=<?php echo $order['id']; ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                            View Order
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
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
    </script>
</body>
</html>
