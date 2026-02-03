<?php
// admin/users.php
session_start();
require_once '../config.php';
require_once 'include/auth_check.php';

// Check if user is logged in
checkAdminAuth();

// Ideally, only 'admin' role should access this page.
// 'editor' or 'viewer' should probably be restricted.
if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] !== 'admin') {
    // Redirect or show access denied
    // For now, let's just let them view but hide actions via JS/PHP
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin User';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
$current_page = 'users';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Users | Admin Dashboard</title>
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
        $header_title = "Admin Users";
        $header_description = "Manage dashboard access and roles";
        include('include/header.php'); 
        ?>

        <main class="p-6">
            <!-- Action Bar -->
            <div class="flex justify-between items-center mb-6">
                <!-- Search (optional implementation) -->
                <div class="w-full max-w-sm">
                    <div class="relative">
                        <input type="text" id="userSearch" placeholder="Search users..." 
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>

                <?php if ($admin_role === 'admin'): ?>
                <button onclick="openModal()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition flex items-center">
                    <i class="fas fa-plus mr-2"></i> Add User
                </button>
                <?php endif; ?>
            </div>

            <!-- Users Table -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase">User</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase">Role</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase">Status</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase">Last Login</th>
                            <?php if ($admin_role === 'admin'): ?>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase text-right">Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody" class="divide-y divide-gray-100">
                        <!-- Populated via JS -->
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Add/Edit Modal -->
    <div id="userModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="userForm">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modalTitle">Add New User</h3>
                        <input type="hidden" name="id" id="userId">
                        
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                <input type="text" name="first_name" id="firstName" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                <input type="text" name="last_name" id="lastName" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                            <input type="text" name="username" id="userName" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" id="userEmail" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                                <select name="role" id="userRole" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    <option value="admin">Admin</option>
                                    <option value="editor">Editor</option>
                                    <option value="viewer">Viewer</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="status" id="userStatus" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1" id="passwordLabel">Password</label>
                            <input type="password" name="password" id="userPassword"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-xs text-gray-500 mt-1 hidden" id="passwordHint">Leave blank to keep current password</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Save User
                        </button>
                        <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast -->
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

        const currentAdminRole = '<?php echo $admin_role; ?>';
        const currentAdminId = <?php echo $_SESSION['admin_id']; ?>;

        function loadUsers() {
            fetch('../api/admin/get_admin_users.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderUsers(data.users);
                    }
                })
                .catch(err => console.error(err));
        }

        function renderUsers(users) {
            const tbody = document.getElementById('usersTableBody');
            tbody.innerHTML = '';
            
            users.forEach(user => {
                const statusClass = user.status === 'active' ? 'bg-[#022658]/10 text-[#022658]' : 'bg-gray-100 text-gray-800';
                
                let actions = '';
                if (currentAdminRole === 'admin') {
                    // Prevent deleting self, but allow editing
                    const deleteBtn = user.id == currentAdminId ? '' : 
                        `<button onclick="deleteUser(${user.id})" class="text-red-600 hover:bg-red-100 p-2 rounded"><i class="fas fa-trash"></i></button>`;
                    
                    actions = `
                        <div class="flex justify-end space-x-2">
                            <button onclick="editUser(${user.id}, '${user.first_name}', '${user.last_name}', '${user.username}', '${user.email}', '${user.role}', '${user.status}')" class="text-blue-600 hover:bg-blue-100 p-2 rounded">
                                <i class="fas fa-edit"></i>
                            </button>
                            ${deleteBtn}
                        </div>
                    `;
                }

                const html = `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold">
                                    ${user.first_name.charAt(0)}${user.last_name.charAt(0)}
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">${user.first_name} ${user.last_name}</div>
                                    <div class="text-xs text-gray-500">${user.email}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 capitalize">
                                ${user.role}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass} capitalize">
                                ${user.status}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            ${user.last_login ? new Date(user.last_login).toLocaleDateString() : 'Never'}
                        </td>
                        ${currentAdminRole === 'admin' ? `<td class="px-6 py-4 text-right">${actions}</td>` : ''}
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', html);
            });
        }

        // Modal Logic
        function openModal() {
            document.getElementById('userForm').reset();
            document.getElementById('userId').value = '';
            document.getElementById('modalTitle').textContent = 'Add New User';
            document.getElementById('userName').disabled = false; // Enable for new
            document.getElementById('passwordLabel').textContent = 'Password *';
            document.getElementById('userPassword').required = true;
            document.getElementById('passwordHint').classList.add('hidden');
            
            document.getElementById('userModal').classList.remove('hidden');
        }

        window.editUser = function(id, first, last, username, email, role, status) {
            document.getElementById('userId').value = id;
            document.getElementById('firstName').value = first;
            document.getElementById('lastName').value = last;
            document.getElementById('userName').value = username;
            document.getElementById('userName').disabled = true; // Identify immutable
            document.getElementById('userEmail').value = email;
            document.getElementById('userRole').value = role;
            document.getElementById('userStatus').value = status;
            
            document.getElementById('modalTitle').textContent = 'Edit User';
            document.getElementById('passwordLabel').textContent = 'Password (change)';
            document.getElementById('userPassword').required = false;
            document.getElementById('passwordHint').classList.remove('hidden');

            document.getElementById('userModal').classList.remove('hidden');
        };

        function closeModal() {
            document.getElementById('userModal').classList.add('hidden');
        }

        window.deleteUser = function(id) {
            if (!confirm('Are you sure you want to delete this user?')) return;

            const formData = new FormData();
            formData.append('id', id);

            fetch('../api/admin/delete_admin_user.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('User deleted successfully');
                    loadUsers();
                } else {
                    showToast(data.message, 'error');
                }
            });
        };

        // Form Submit
        document.getElementById('userForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const id = formData.get('id');
            const url = id ? '../api/admin/update_admin_user.php' : '../api/admin/create_admin_user.php';

            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message);
                    closeModal();
                    loadUsers();
                } else {
                    showToast(data.message, 'error');
                }
            });
        });

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
        loadUsers();
    </script>
</body>
</html>
