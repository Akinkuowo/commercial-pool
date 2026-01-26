<?php
// admin/products.php
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
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$stock_filter = isset($_GET['stock']) ? $_GET['stock'] : '';
$search = isset($_GET['s']) ? trim($_GET['s']) : '';

// Build query with correct column names
$where_conditions = [];
$params = [];
$param_types = '';

if ($category_filter) {
    $where_conditions[] = "category LIKE ?";
    $params[] = "%$category_filter%";
    $param_types .= 's';
}

if ($status_filter) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
    $param_types .= 's';
}

if ($search) {
    $where_conditions[] = "(product_name LIKE ? OR sku_number LIKE ? OR product_description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= 'sss';
}

if ($stock_filter) {
    switch ($stock_filter) {
        case 'in_stock':
            $where_conditions[] = "quantity > 10";
            break;
        case 'low_stock':
            $where_conditions[] = "quantity <= 10 AND quantity > 0";
            break;
        case 'out_of_stock':
            $where_conditions[] = "quantity = 0";
            break;
    }
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_query = "SELECT COUNT(*) as total FROM products $where_clause";
if (!empty($params)) {
    $count_stmt = $conn->prepare($count_query);
    if (!empty($param_types)) {
        $count_stmt->bind_param($param_types, ...$params);
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_products = $count_result->fetch_assoc()['total'];
    $count_stmt->close();
} else {
    $count_result = $conn->query($count_query);
    $total_products = $count_result->fetch_assoc()['total'];
}

$total_pages = ceil($total_products / $per_page);

// Get products with correct column names
$query = "SELECT * FROM products $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$param_types .= 'ii';

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Initialize default stats FIRST
$product_stats = [
    'total_products' => 0,
    'published' => 0,
    'draft' => 0,
    'low_stock' => 0,
    'out_of_stock' => 0,
    'total_stock' => 0
];

// Get product stats with correct column names
$stats_query = "SELECT 
    COALESCE(COUNT(*), 0) as total_products,
    COALESCE(SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END), 0) as published,
    COALESCE(SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END), 0) as draft,
    COALESCE(SUM(CASE WHEN quantity <= 10 AND quantity > 0 THEN 1 ELSE 0 END), 0) as low_stock,
    COALESCE(SUM(CASE WHEN quantity = 0 THEN 1 ELSE 0 END), 0) as out_of_stock,
    COALESCE(SUM(CASE WHEN quantity IS NOT NULL THEN quantity ELSE 0 END), 0) as total_stock
FROM products";

$stats_result = $conn->query($stats_query);

// ONLY overwrite if we got valid data
if ($stats_result && $stats_result->num_rows > 0) {
    $fetched_stats = $stats_result->fetch_assoc();
    if ($fetched_stats && is_array($fetched_stats)) {
        // Explicitly map keys to ensure we don't lose the default structure or introduce case sensitivity issues
        foreach ($product_stats as $key => $default_val) {
            if (isset($fetched_stats[$key])) {
                $product_stats[$key] = $fetched_stats[$key];
            }
        }
    }
}

// Ensure all stats are integers
foreach ($product_stats as $key => $value) {
    $product_stats[$key] = (int)$value;
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin User';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
$current_page = 'products';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products | Admin Dashboard</title>
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
                            <h1 class="text-2xl font-bold text-gray-800">Products</h1>
                            <p class="text-gray-600 mt-1">Manage your product inventory</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button onclick="openAddProductModal()" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition flex items-center">
                            <i class="fas fa-plus mr-2"></i>
                            Add New Product
                        </button>
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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Total Products</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($product_stats['total_products']); ?></h3>
                        </div>
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-box text-blue-600"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Published</p>
                            <h3 class="text-2xl font-bold text-[#022658]"><?php echo number_format($product_stats['published']); ?></h3>
                        </div>
                        <div class="w-10 h-10 bg-[#022658]/10 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-circle text-[#022658]"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Draft</p>
                            <h3 class="text-2xl font-bold text-yellow-600"><?php echo number_format($product_stats['draft']); ?></h3>
                        </div>
                        <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-file-alt text-yellow-600"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Low Stock</p>
                            <h3 class="text-2xl font-bold text-orange-600"><?php echo number_format($product_stats['low_stock']); ?></h3>
                        </div>
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-orange-600"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Out of Stock</p>
                            <h3 class="text-2xl font-bold text-red-600"><?php echo number_format($product_stats['out_of_stock']); ?></h3>
                        </div>
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-times-circle text-red-600"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Total Stock</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($product_stats['total_stock']); ?></h3>
                        </div>
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-layer-group text-purple-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                <form method="GET" action="products.php" class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <input type="text" 
                                   name="s" 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   placeholder="Search products..." 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <select name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Categories</option>
                                <option value="Awnings" <?php echo $category_filter === 'Awnings' ? 'selected' : ''; ?>>Awnings</option>
                                <option value="Camping" <?php echo $category_filter === 'Camping' ? 'selected' : ''; ?>>Camping</option>
                                <option value="Caravan" <?php echo $category_filter === 'Caravan' ? 'selected' : ''; ?>>Caravan</option>
                                <option value="Motorhome" <?php echo $category_filter === 'Motorhome' ? 'selected' : ''; ?>>Motorhome</option>
                            </select>
                        </div>
                        <div>
                            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Status</option>
                                <option value="published" <?php echo $status_filter === 'published' ? 'selected' : ''; ?>>Published</option>
                                <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            </select>
                        </div>
                        <div>
                            <select name="stock" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Stock</option>
                                <option value="in_stock" <?php echo $stock_filter === 'in_stock' ? 'selected' : ''; ?>>In Stock</option>
                                <option value="low_stock" <?php echo $stock_filter === 'low_stock' ? 'selected' : ''; ?>>Low Stock</option>
                                <option value="out_of_stock" <?php echo $stock_filter === 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 mt-4">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                        <a href="products.php" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition">
                            <i class="fas fa-times mr-2"></i>Clear
                        </a>
                        <button type="button" onclick="exportProducts()" class="ml-auto bg-[#022658] text-white px-6 py-2 rounded-lg hover:bg-[#022658]/90 transition">
                            <i class="fas fa-file-export mr-2"></i>Export
                        </button>
                    </div>
                </form>
            </div>

            <!-- Products Table -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-left">
                                    <input type="checkbox" id="selectAll" class="rounded border-gray-300">
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Product</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">SKU</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Stock</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Price</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Brand</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                        <i class="fas fa-box-open text-4xl mb-2"></i>
                                        <p>No products found</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($products as $product): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <input type="checkbox" class="product-checkbox rounded border-gray-300" value="<?php echo $product['id']; ?>">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="w-12 h-12 bg-gray-200 rounded flex-shrink-0 overflow-hidden">
                                                <?php if (!empty($product['image'])): ?>
                                                    <img src="../<?php echo htmlspecialchars($product['image']); ?>" alt="" class="w-full h-full object-cover">
                                                <?php endif; ?>
                                            </div>
                                            <div class="ml-4">
                                                <p class="font-medium text-gray-900"><?php echo htmlspecialchars($product['product_name']); ?></p>
                                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars(substr($product['product_description'] ?? '', 0, 50)); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($product['sku_number']); ?></td>
                                    <td class="px-6 py-4">
                                        <?php
                                        $stock = $product['quantity'] ?? 0;
                                        $stock_class = $stock > 10 ? 'text-[#022658]' : ($stock > 0 ? 'text-orange-600' : 'text-red-600');
                                        ?>
                                        <span class="text-sm font-medium <?php echo $stock_class; ?>"><?php echo $stock; ?></span>
                                        <span class="text-xs text-gray-500 ml-1">units</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-gray-900">£<?php echo number_format($product['price'], 2); ?></p>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600"><?php echo htmlspecialchars($product['brand_name'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4">
                                        <?php
                                        $status_colors = [
                                            'published' => 'bg-[#022658]/10 text-[#022658]',
                                            'draft' => 'bg-yellow-100 text-yellow-800'
                                        ];
                                        $status_class = $status_colors[$product['status']] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                        <span class="px-3 py-1 text-xs font-medium <?php echo $status_class; ?> rounded-full">
                                            <?php echo ucfirst($product['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-2">
                                            <button onclick="editProduct(<?php echo $product['id']; ?>)" class="text-blue-600 hover:text-blue-800" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="viewProduct(<?php echo $product['id']; ?>)" class="text-[#022658] hover:text-[#022658]/80" title="View">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($admin_role === 'admin'): ?>
                                            <button onclick="deleteProduct(<?php echo $product['id']; ?>)" class="text-red-600 hover:text-red-800" title="Delete">
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
                            Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $per_page, $total_products); ?> of <?php echo $total_products; ?> products
                        </p>
                        <div class="flex space-x-2">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&category=<?php echo $category_filter; ?>&status=<?php echo $status_filter; ?>&stock=<?php echo $stock_filter; ?>&s=<?php echo urlencode($search); ?>" 
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
                                <a href="?page=<?php echo $i; ?>&category=<?php echo $category_filter; ?>&status=<?php echo $status_filter; ?>&stock=<?php echo $stock_filter; ?>&s=<?php echo urlencode($search); ?>" 
                                   class="px-4 py-2 <?php echo $i === $page ? 'bg-blue-600 text-white' : 'border border-gray-300 hover:bg-gray-50'; ?> rounded-lg">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&category=<?php echo $category_filter; ?>&status=<?php echo $status_filter; ?>&stock=<?php echo $stock_filter; ?>&s=<?php echo urlencode($search); ?>" 
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

        function openAddProductModal() {
            // Redirect to add product page
            window.location.href = 'add_product.php';
        }
        
        function editProduct(id) {
            window.location.href = `edit_product.php?id=${id}`;
        }
        
        function viewProduct(id) {
            window.open(`../product.php?id=${id}`, '_blank');
        }
        
        function deleteProduct(id) {
            if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
                fetch(`../api/admin/delete_product.php?id=${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Error deleting product: ' + data.message);
                    }
                });
            }
        }

        function exportProducts() {
            window.location.href = '../api/admin/export_products.php';
        }
    </script>
</body>
</html>