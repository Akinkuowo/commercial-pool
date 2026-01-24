<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders | Commercial Pool Equipment</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar { width: 260px; }
        .main-content { margin-left: 260px; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Sidebar (same as dashboard) -->
    <aside class="sidebar fixed left-0 top-0 h-screen bg-gray-900 text-white shadow-xl overflow-y-auto">
        <div class="p-4 border-b border-gray-700">
            <div class="flex items-center">
                <i class="fas fa-shield-halved text-2xl text-blue-400"></i>
                <span class="ml-3 font-bold text-lg">Jacksons Admin</span>
            </div>
        </div>
        <nav class="p-4">
            <a href="dashboard.php" class="flex items-center px-3 py-2.5 text-gray-300 hover:bg-gray-800 rounded-lg mb-1">
                <i class="fas fa-home w-5"></i>
                <span class="ml-3">Dashboard</span>
            </a>
            <a href="orders.php" class="flex items-center px-3 py-2.5 bg-blue-600 rounded-lg mb-1">
                <i class="fas fa-shopping-cart w-5"></i>
                <span class="ml-3">Orders</span>
            </a>
            <a href="products.php" class="flex items-center px-3 py-2.5 text-gray-300 hover:bg-gray-800 rounded-lg mb-1">
                <i class="fas fa-box w-5"></i>
                <span class="ml-3">Products</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="main-content min-h-screen">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200 sticky top-0 z-40">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-800">Orders</h1>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <button class="flex items-center space-x-3 p-2 hover:bg-gray-100 rounded-lg">
                                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">A</div>
                                <span class="text-sm font-semibold">Admin User</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="p-6">
            <!-- Stats Row -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Orders</p>
                            <h3 class="text-2xl font-bold text-gray-800">1,254</h3>
                        </div>
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-shopping-cart text-blue-600"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Pending</p>
                            <h3 class="text-2xl font-bold text-yellow-600">48</h3>
                        </div>
                        <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-yellow-600"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Processing</p>
                            <h3 class="text-2xl font-bold text-blue-600">156</h3>
                        </div>
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-sync text-blue-600"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Completed</p>
                            <h3 class="text-2xl font-bold text-green-600">1,050</h3>
                        </div>
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div class="md:col-span-2">
                        <input type="text" placeholder="Search orders by number, customer..." 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Payment Status</option>
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                    <div>
                        <input type="date" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-left">
                                    <input type="checkbox" class="rounded border-gray-300">
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Order</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Customer</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Total</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Payment</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <input type="checkbox" class="rounded border-gray-300">
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-blue-600 hover:underline cursor-pointer">#ORD-1234</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">Jan 24, 2026</td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900">John Smith</p>
                                    <p class="text-sm text-gray-500">john@example.com</p>
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-900">£234.50</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Paid</span>
                                </td>
                                <td class="px-6 py-4">
                                    <select class="px-3 py-1 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="pending">Pending</option>
                                        <option value="processing">Processing</option>
                                        <option value="completed" selected>Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="viewOrder(1234)" class="text-blue-600 hover:text-blue-800" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button onclick="printOrder(1234)" class="text-green-600 hover:text-green-800" title="Print">
                                            <i class="fas fa-print"></i>
                                        </button>
                                        <button onclick="deleteOrder(1234)" class="text-red-600 hover:text-red-800" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <input type="checkbox" class="rounded border-gray-300">
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-blue-600 hover:underline cursor-pointer">#ORD-1235</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">Jan 24, 2026</td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900">Jane Doe</p>
                                    <p class="text-sm text-gray-500">jane@example.com</p>
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-900">£456.00</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Pending</span>
                                </td>
                                <td class="px-6 py-4">
                                    <select class="px-3 py-1 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="pending">Pending</option>
                                        <option value="processing" selected>Processing</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="viewOrder(1235)" class="text-blue-600 hover:text-blue-800" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button onclick="printOrder(1235)" class="text-green-600 hover:text-green-800" title="Print">
                                            <i class="fas fa-print"></i>
                                        </button>
                                        <button onclick="deleteOrder(1235)" class="text-red-600 hover:text-red-800" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-600">Showing 1 to 20 of 1,254 orders</p>
                        <div class="flex space-x-2">
                            <button class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Previous</button>
                            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg">1</button>
                            <button class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">2</button>
                            <button class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">3</button>
                            <button class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Next</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function viewOrder(id) {
            window.location.href = `order_details.php?id=${id}`;
        }
        
        function printOrder(id) {
            window.open(`print_order.php?id=${id}`, '_blank');
        }
        
        function deleteOrder(id) {
            if (confirm('Are you sure you want to delete this order?')) {
                // Handle deletion
            }
        }
    </script>
</body>
</html>