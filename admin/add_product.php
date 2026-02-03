<?php
// admin/add_product.php
session_start();
require_once '../config.php';
require_once 'include/auth_check.php';

// Check if user is logged in
checkAdminAuth();

$admin_name = $_SESSION['admin_name'] ?? 'Admin User';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
$current_page = 'products';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product | Admin Dashboard</title>
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
        $header_title = "Add Product";
        include('include/header.php'); 
        ?>

        <main class="p-6">
            <div class="max-w-4xl mx-auto">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Add New Product</h1>
                    <a href="products.php" class="text-gray-600 hover:text-gray-900">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Products
                    </a>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <form id="addProductForm" class="p-6 space-y-6">
                        <!-- Basic Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                                <input type="text" name="product_name" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="e.g. Luxury 4-Person Tent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">SKU Number *</label>
                                <input type="text" name="sku_number" required
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
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="e.g. Vango">
                            </div>
                        </div>

                        <!-- Pricing & Stock -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Price (£) *</label>
                                <input type="number" step="0.01" name="price" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="0.00">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity *</label>
                                <input type="number" name="stock" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="0">
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="product_description" rows="4"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Enter product description..."></textarea>
                        </div>

                        <!-- Image -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Product Image</label>
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
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                </select>
                            </div>
                            
                            <div class="flex space-x-6">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="is_new_product" value="1" class="rounded text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Mark as New Arrival</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="is_popular_product" value="1" class="rounded text-blue-600 focus:ring-blue-500">
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
                                <i class="fas fa-save mr-2"></i>Save Product
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
                            // Use category name as value since products table stores category name currently
                            // If it stored ID, we would use category.id
                            option.value = category.name; 
                            option.innerHTML = prefix + category.name;
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
        document.getElementById('addProductForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';

            const formData = new FormData(this);

            fetch('../api/admin/add_product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Product added successfully!');
                    setTimeout(() => {
                        window.location.href = 'products.php';
                    }, 1000);
                } else {
                    showToast(data.message || 'Error creating product', 'error');
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
