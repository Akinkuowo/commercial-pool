 <!-- Header -->
        <header class="bg-white border-b border-gray-200 sticky top-0 z-40">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <button id="mobileSidebarToggle" class="lg:hidden mr-4 text-gray-600">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800"><?php echo $header_title ?? 'Admin Panel'; ?></h1>
                            <?php if (isset($header_description)): ?>
                                <p class="text-gray-600 mt-1"><?php echo $header_description; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <button id="userMenuBtn" class="flex items-center space-x-3 p-2 hover:bg-gray-100 rounded-lg">
                                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                                    <?php echo getAdminInitials(); ?>
                                </div>
                                <div class="hidden md:block text-left">
                                    <div class="text-sm font-semibold text-gray-700"><?php echo explode(' ', getAdminName())[0]; ?></div>
                                    <div class="text-xs text-gray-500"><?php echo ucfirst(getAdminRole()); ?></div>
                                </div>
                            </button>
                            <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2">
                                <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user-circle mr-2"></i>Profile
                                </a>
                                <a href="settings.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 border-b border-gray-100">
                                    <i class="fas fa-cog mr-2"></i>Settings
                                </a>
                                <a href="../api/admin/logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>