<?php
// admin/edit_product.php
session_start();
require_once '../config.php';
require_once 'include/auth_check.php';

// Check if user is logged in
checkAdminAuth();

$admin_name = $_SESSION['admin_name'] ?? 'Admin User';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
$current_page = 'products';

// Get product ID
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    header('Location: products.php');
    exit;
}

// Fetch product details
$conn = getDbConnection();
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    header('Location: products.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product | Admin Dashboard</title>
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
                        <div class="flex items-center text-sm text-gray-600">
                            <a href="products.php" class="hover:text-blue-600">Products</a>
                            <i class="fas fa-chevron-right mx-2 text-xs"></i>
                            <span class="font-semibold text-gray-800">Edit Product</span>
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
            <div class="max-w-4xl mx-auto">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Edit Product</h1>
                    <a href="products.php" class="text-gray-600 hover:text-gray-900">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Products
                    </a>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <form id="editProductForm" class="p-6 space-y-6">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        
                        <!-- Basic Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                                <input type="text" name="product_name" required
                                    value="<?php echo htmlspecialchars($product['product_name']); ?>"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="e.g. Luxury 4-Person Tent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">SKU Number *</label>
                                <input type="text" name="sku_number" required
                                    value="<?php echo htmlspecialchars($product['sku_number']); ?>"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="e.g. TN-4P-LUX">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                <select name="category" id="productCategory"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Category</option>
                                    <!-- Populated via JS -->
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Brand Name</label>
                                <input type="text" name="brand_name"
                                    value="<?php echo htmlspecialchars($product['brand_name'] ?? ''); ?>"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="e.g. Vango">
                            </div>
                        </div>

                        <!-- Pricing & Stock -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Price (£) *</label>
                                <input type="number" step="0.01" name="price" required
                                    value="<?php echo $product['price']; ?>"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="0.00">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity *</label>
                                <input type="number" name="stock" required
                                    value="<?php echo $product['quantity'] ?? 0; ?>"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="0">
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="product_description" rows="4"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Enter product description..."><?php echo htmlspecialchars($product['product_description'] ?? ''); ?></textarea>
                        </div>

                        <!-- Current Image -->
                        <?php if (!empty($product['image'])): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Image</label>
                            <div class="flex items-center space-x-4">
                                <img src="../<?php echo htmlspecialchars($product['image']); ?>" 
                                     alt="Product Image" 
                                     class="w-32 h-32 object-cover rounded-lg border border-gray-200">
                                <button type="button" onclick="removeCurrentImage()" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash mr-1"></i>Remove Image
                                </button>
                            </div>
                            <input type="hidden" id="currentImage" name="current_image" value="<?php echo htmlspecialchars($product['image']); ?>">
                            <input type="hidden" id="removeImage" name="remove_image" value="0">
                        </div>
                        <?php endif; ?>

                        <!-- New Image Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Upload New Image</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-500 transition-colors">
                                <div class="space-y-1 text-center">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="image-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                            <span>Upload a file</span>
                                            <input id="image-upload" name="image" type="file" class="sr-only" accept="image/*">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 5MB</p>
                                </div>
                            </div>
                            <div id="image-preview" class="mt-4 hidden p-2 border rounded-lg bg-gray-50"></div>
                        </div>

                        <!-- Status & Flags -->
                        <div class="p-4 bg-gray-50 rounded-lg space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="draft" <?php echo $product['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?php echo $product['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                                </select>
                            </div>
                            
                            <div class="flex space-x-6">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="is_new_product" value="1" 
                                        <?php echo $product['is_new_product'] ? 'checked' : ''; ?>
                                        class="rounded text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Mark as New Arrival</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="is_popular_product" value="1"
                                        <?php echo $product['is_popular_product'] ? 'checked' : ''; ?>
                                        class="rounded text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Mark as Popular</span>
                                </label>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-4 pt-4 border-t border-gray-100">
                            <a href="products.php" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center">
                                <i class="fas fa-save mr-2"></i>Update Product
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-4 right-4 bg-gray-800 text-white px-6 py-3 rounded-lg shadow-lg transform translate-y-full opacity-0 transition-all duration-300 z-50">
        <div class="flex items-center space-x-3">
            <i id="toastIcon" class="fas fa-check-circle text-[#022658]"></i>
            <span id="toastMessage">Action successful</span>
        </div>
    </div>

    <script>
        const currentCategory = "<?php echo htmlspecialchars($product['category'] ?? ''); ?>";

        // User menu toggle
        document.getElementById('userMenuBtn').addEventListener('click', function() {
            document.getElementById('userMenu').classList.toggle('hidden');
        });

        // Mobile sidebar toggle
        document.getElementById('mobileSidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('mobile-open');
        });

        // Image Preview
        document.getElementById('image-upload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('image-preview');
                    preview.innerHTML = `<div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <img src="${e.target.result}" class="w-16 h-16 object-cover rounded" />
                            <span class="text-sm text-gray-600">${file.name}</span>
                        </div>
                        <button type="button" onclick="clearImage()" class="text-red-500 hover:text-red-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>`;
                    preview.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        });

        function clearImage() {
            document.getElementById('image-upload').value = '';
            document.getElementById('image-preview').innerHTML = '';
            document.getElementById('image-preview').classList.add('hidden');
        }

        function removeCurrentImage() {
            if (confirm('Are you sure you want to remove the current image?')) {
                document.getElementById('removeImage').value = '1';
                document.querySelector('img[alt="Product Image"]').parentElement.parentElement.style.display = 'none';
            }
        }

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

        // Load Categories
        function loadCategories() {
            fetch('../api/categories.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const select = document.getElementById('productCategory');
                        
                        function addOption(category, level = 0) {
                            const prefix = level > 0 ? '&nbsp;&nbsp;&nbsp;'.repeat(level) + '- ' : '';
                            const option = document.createElement('option');
                            option.value = category.name;
                            option.innerHTML = prefix + category.name;
                            
                            // Select current category
                            if (category.name === currentCategory) {
                                option.selected = true;
                            }
                            
                            select.appendChild(option);

                            if (category.children && category.children.length > 0) {
                                category.children.forEach(child => addOption(child, level + 1));
                            }
                        }

                        data.categories.forEach(cat => addOption(cat));
                    }
                })
                .catch(error => console.error('Error loading categories:', error));
        }

        // Initial Load
        loadCategories();

        // Form Submission
        document.getElementById('editProductForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';

            const formData = new FormData(this);

            fetch('../api/admin/update_product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Product updated successfully!');
                    setTimeout(() => {
                        window.location.href = 'products.php';
                    }, 1000);
                } else {
                    showToast(data.message || 'Error updating product', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An unexpected error occurred', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    </script>
</body>
</html>
