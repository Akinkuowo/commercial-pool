<?php
// admin/media.php
session_start();
require_once '../config.php';
require_once 'include/auth_check.php';

// Check if user is logged in
checkAdminAuth();

$admin_name = $_SESSION['admin_name'] ?? 'Admin User';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
$current_page = 'media';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Library | Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar { width: 260px; transition: all 0.3s; }
        .main-content { margin-left: 260px; transition: all 0.3s; }
        .media-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem; }
        .media-item { aspect-ratio: 1; position: relative; overflow: hidden; }
        .media-item:hover .media-overlay { opacity: 1; }
        .media-overlay { transition: opacity 0.2s; }
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
                            <h1 class="text-2xl font-bold text-gray-800">Media Library</h1>
                            <p class="text-gray-600 mt-1">Manage product images</p>
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
            <!-- Upload Area -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div id="dropZone" class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-blue-500 transition-colors cursor-pointer">
                    <i class="fas fa-cloud-upload-alt text-5xl text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-700">Drop images here or click to upload</h3>
                    <p class="text-gray-500 mt-2 text-sm">Supports JPG, PNG, WEBP</p>
                    <input type="file" id="fileInput" class="hidden" multiple accept="image/*">
                </div>
                <!-- Progress Bar -->
                <div id="progressContainer" class="hidden mt-4">
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div id="progressBar" class="bg-blue-600 h-2.5 rounded-full" style="width: 0%"></div>
                    </div>
                    <p id="progressText" class="text-sm text-center mt-1 text-gray-600">Uploading...</p>
                </div>
            </div>

            <!-- Media Grid -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Files</h2>
                    <button onclick="loadMedia()" class="text-blue-600 hover:bg-blue-50 px-3 py-1 rounded transition">
                        <i class="fas fa-sync-alt mr-1"></i> Refresh
                    </button>
                </div>
                
                <div id="mediaGrid" class="media-grid">
                    <!-- Loaded via JS -->
                    <div class="animate-pulse bg-gray-100 rounded-lg aspect-square"></div>
                    <div class="animate-pulse bg-gray-100 rounded-lg aspect-square"></div>
                    <div class="animate-pulse bg-gray-100 rounded-lg aspect-square"></div>
                    <div class="animate-pulse bg-gray-100 rounded-lg aspect-square"></div>
                </div>
            </div>
        </main>
    </div>

    <!-- Preview Modal -->
    <div id="previewModal" class="hidden fixed inset-0 z-50 overflow-auto bg-black bg-opacity-90 flex items-center justify-center p-4">
        <button onclick="closePreview()" class="absolute top-4 right-4 text-white text-3xl hover:text-gray-300">&times;</button>
        <img id="previewImage" src="" alt="Preview" class="max-h-[90vh] max-w-[90vw] object-contain rounded-lg">
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-4 right-4 bg-gray-800 text-white px-6 py-3 rounded-lg shadow-lg transform translate-y-full opacity-0 transition-all duration-300 z-50">
        <div class="flex items-center space-x-3">
            <i id="toastIcon" class="fas fa-check-circle text-green-400"></i>
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

        // Load Media
        function loadMedia() {
            const grid = document.getElementById('mediaGrid');
            grid.innerHTML = '<div class="col-span-full text-center py-8 text-gray-500"><i class="fas fa-spinner fa-spin mr-2"></i>Loading...</div>';

            fetch('../api/admin/get_media.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderMedia(data.files);
                    } else {
                        grid.innerHTML = `<div class="col-span-full text-center py-8 text-red-500">Error: ${data.message}</div>`;
                    }
                })
                .catch(err => {
                    grid.innerHTML = '<div class="col-span-full text-center py-8 text-red-500">Failed to load media</div>';
                });
        }

        function renderMedia(files) {
            const grid = document.getElementById('mediaGrid');
            if (files.length === 0) {
                grid.innerHTML = '<div class="col-span-full text-center py-8 text-gray-500">No images found</div>';
                return;
            }

            grid.innerHTML = files.map(file => `
                <div class="media-item bg-gray-100 rounded-lg border border-gray-200 group">
                    <img src="../${file.path}" alt="${file.name}" class="w-full h-full object-cover">
                    <div class="media-overlay absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 flex items-center justify-center space-x-2 transition-opacity duration-200">
                        <button onclick="previewImage('../${file.path}')" class="bg-white text-gray-800 p-2 rounded-full hover:bg-gray-100 transition" title="Preview">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="copyLink('../${file.path}')" class="bg-blue-500 text-white p-2 rounded-full hover:bg-blue-600 transition" title="Copy Link">
                            <i class="fas fa-link"></i>
                        </button>
                        <button onclick="deleteImage('${file.name}')" class="bg-red-500 text-white p-2 rounded-full hover:bg-red-600 transition" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `).join('');
        }

        // Upload Logic
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');

        dropZone.addEventListener('click', () => fileInput.click());
        
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-blue-500', 'bg-blue-50');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
            handleFiles(e.dataTransfer.files);
        });

        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        function handleFiles(files) {
            if (files.length === 0) return;
            
            const progressContainer = document.getElementById('progressContainer');
            const progressBar = document.getElementById('progressBar');
            progressContainer.classList.remove('hidden');

            let completed = 0;
            let errors = 0;
            const total = files.length;

            Array.from(files).forEach(file => {
                const formData = new FormData();
                formData.append('file', file);

                fetch('../api/admin/upload_media.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        completed++;
                    } else {
                        errors++;
                        showToast(`Failed to upload ${file.name}: ${data.message}`, 'error');
                    }
                })
                .catch(() => {
                    errors++;
                })
                .finally(() => {
                    const percent = Math.round(((completed + errors) / total) * 100);
                    progressBar.style.width = `${percent}%`;
                    
                    if ((completed + errors) === total) {
                        setTimeout(() => {
                            progressContainer.classList.add('hidden');
                            progressBar.style.width = '0%';
                            loadMedia();
                            if (completed > 0) showToast(`${completed} files uploaded successfully`);
                        }, 1000);
                    }
                });
            });
        }

        // Actions
        function previewImage(src) {
            document.getElementById('previewImage').src = src;
            document.getElementById('previewModal').classList.remove('hidden');
        }

        function closePreview() {
            document.getElementById('previewModal').classList.add('hidden');
        }

        function copyLink(path) {
            // Absolute URL
            const url = window.location.origin + window.location.pathname.replace('/admin/media.php', '') + '/' + path.replace('../', '');
            navigator.clipboard.writeText(url).then(() => {
                showToast('Link copied to clipboard');
            });
        }

        function deleteImage(filename) {
            if (!confirm('Are you sure you want to delete this image? This cannot be undone.')) return;

            fetch('../api/admin/delete_media.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ filename: filename })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message);
                    loadMedia();
                } else {
                    showToast(data.message, 'error');
                }
            });
        }

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

        // Initial Load
        loadMedia();
    </script>
</body>
</html>
