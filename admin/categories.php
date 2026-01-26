<?php
// admin/categories.php
session_start();
require_once '../config.php';
require_once 'include/auth_check.php';

// Check if user is logged in
checkAdminAuth();

// Get database connection
$conn = getDbConnection();

$admin_name = $_SESSION['admin_name'] ?? 'Admin User';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
$current_page = 'categories';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories | Admin Dashboard</title>
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
                            <h1 class="text-2xl font-bold text-gray-800">Categories</h1>
                            <p class="text-gray-600 mt-1">Manage product categories</p>
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
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Add Category Form -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4" id="formTitle">Add New Category</h2>
                        <form id="categoryForm">
                            <input type="hidden" name="id" id="catId">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                <input type="text" name="name" id="catName" required 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Slug (Optional)</label>
                                <input type="text" name="slug" id="catSlug" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Leave empty to auto-generate</p>
                            </div>
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Parent Category</label>
                                <select name="parent_id" id="catParent" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">None</option>
                                    <!-- Populated via JS -->
                                </select>
                            </div>
                            <div class="flex space-x-2">
                                <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                                    <span id="submitBtnText">Add Category</span>
                                </button>
                                <button type="button" id="cancelEdit" class="hidden px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Categories List -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-800">All Categories</h3>
                            <button onclick="loadCategories()" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                        <div class="p-6">
                            <div id="categoryList" class="space-y-2">
                                <!-- Categories loaded via JS -->
                                <div class="animate-pulse space-y-3">
                                    <div class="h-10 bg-gray-100 rounded w-full"></div>
                                    <div class="h-10 bg-gray-100 rounded w-full"></div>
                                    <div class="h-10 bg-gray-100 rounded w-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>
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

        // Load Categories
        let allCategories = [];

        function loadCategories() {
            fetch('../api/categories.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        allCategories = data.categories;
                        renderCategories();
                        updateParentDropdown();
                    } else {
                        console.error('Failed to load categories');
                    }
                })
                .catch(err => console.error(err));
        }

        function renderCategories() {
            const container = document.getElementById('categoryList');
            container.innerHTML = '';

            if (allCategories.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-center py-4">No categories found.</p>';
                return;
            }

            // Recursive function to render hierarchy
            function renderItem(category, level = 0) {
                const padding = level * 20;
                const html = `
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100 hover:border-blue-200 transition group">
                        <div class="flex items-center" style="padding-left: ${padding}px">
                            ${level > 0 ? '<i class="fas fa-level-up-alt rotate-90 text-gray-400 mr-2 text-xs"></i>' : ''}
                            <span class="font-medium text-gray-800">${category.name}</span>
                            <span class="ml-2 text-xs text-gray-500 bg-gray-200 px-2 py-0.5 rounded">${category.slug}</span>
                        </div>
                        <div class="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="editCategory(${category.id}, '${category.name.replace(/'/g, "\\'")}', '${category.slug}', ${category.parent_id})" class="text-blue-600 hover:bg-blue-100 p-1.5 rounded" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteCategory(${category.id})" class="text-red-600 hover:bg-red-100 p-1.5 rounded" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', html);

                if (category.children && category.children.length > 0) {
                    category.children.forEach(child => renderItem(child, level + 1));
                }
            }

            allCategories.forEach(cat => renderItem(cat));
        }

        function updateParentDropdown() {
            const select = document.getElementById('catParent');
            const currentVal = select.value;
            select.innerHTML = '<option value="">None</option>';
            
            // Flatten list for dropdown (simple indentation)
            function addOption(category, level = 0) {
                const prefix = level > 0 ? '&nbsp;&nbsp;&nbsp;'.repeat(level) + '- ' : '';
                const option = document.createElement('option');
                option.value = category.id;
                option.innerHTML = prefix + category.name;
                select.appendChild(option);

                if (category.children && category.children.length > 0) {
                    category.children.forEach(child => addOption(child, level + 1));
                }
            }
            
            allCategories.forEach(cat => addOption(cat));
            select.value = currentVal;
        }

        // Add/Edit Form
        document.getElementById('categoryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const id = formData.get('id');
            const url = id ? '../api/admin/update_category.php' : '../api/admin/add_category.php';

            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message);
                    resetForm();
                    loadCategories();
                } else {
                    showToast(data.message, 'error');
                }
            });
        });

        // Edit Mode
        window.editCategory = function(id, name, slug, parentId) {
            document.getElementById('formTitle').textContent = 'Edit Category';
            document.getElementById('submitBtnText').textContent = 'Update Category';
            document.getElementById('catId').value = id;
            document.getElementById('catName').value = name;
            document.getElementById('catSlug').value = slug;
            document.getElementById('catParent').value = parentId || '';
            document.getElementById('cancelEdit').classList.remove('hidden');
        };

        // Cancel Edit
        document.getElementById('cancelEdit').addEventListener('click', resetForm);

        function resetForm() {
            document.getElementById('categoryForm').reset();
            document.getElementById('catId').value = '';
            document.getElementById('formTitle').textContent = 'Add New Category';
            document.getElementById('submitBtnText').textContent = 'Add Category';
            document.getElementById('cancelEdit').classList.add('hidden');
        }

        // Delete
        window.deleteCategory = function(id) {
            if (!confirm('Are you sure you want to delete this category?')) return;

            const formData = new FormData();
            formData.append('id', id);

            fetch('../api/admin/delete_category.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message);
                    loadCategories();
                } else {
                    showToast(data.message, 'error');
                }
            });
        };

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

        // Initial Load
        loadCategories();
    </script>
</body>
</html>
