<?php
    // admin/index.php
    // Main admin panel with authentication

    require_once '../dbs/auth/auth.php';

    // Check if authentication object exists
    if (! isset($auth)) {
        header('Location: login.php?error=auth_error');
        exit;
    }

    // Require login to access admin panel
    $auth->requireLogin();

    // Double check if user is actually logged in
    if (! $auth->isLoggedIn()) {
        header('Location: login.php?error=session_expired');
        exit;
    }

    // Get current admin info
    $currentAdmin = $auth->getCurrentAdmin();

    // If we can't get current admin info, something is wrong with the session
    if (! $currentAdmin) {
        session_destroy();
        header('Location: login.php?error=invalid_session');
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Shree Balalji BioFuels</title>
    <link rel="icon" type="image/png" href="../logo.png">
    <link rel="shortcut icon" href="../logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'jakarta': ['Plus Jakarta Sans', 'sans-serif']
                    },
                    colors: {
                        primary: '#10b981',
                        accent: '#059669'
                    }
                }
            }
        }
    </script>
</head>
<body class="font-jakarta antialiased bg-gray-50 dark:bg-gray-900">

    <!-- Sidebar -->
    <aside id="logo-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0" aria-label="Sidebar">
        <div class="h-full px-3 py-4 overflow-y-auto bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700">
            <!-- Logo -->
            <div class="flex items-center space-x-3 mb-8 px-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center">
                    <img src="../logo.png" alt="">
                </div>
                <div>
                    <div class="font-semibold text-gray-900 dark:text-white">Admin Panel</div>
                    <div class="text-xs text-gray-500">Shree Balalji BioFuels</div>
                </div>
            </div>

            <!-- Navigation -->
            <ul class="space-y-2 font-medium">
                <li>
                    <a href="#" class="sidebar-link active" data-tab="dashboard">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
                            <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>
                        </svg>
                        <span class="ml-3">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="sidebar-link" data-tab="gallery">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-3">Gallery Management</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="sidebar-link" data-tab="videos">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"></path>
                        </svg>
                        <span class="ml-3">Video Management</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="sidebar-link" data-tab="categories">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"></path>
                        </svg>
                        <span class="ml-3">Categories</span>
                    </a>
                </li>
                <!-- <li>
                    <a href="#" class="sidebar-link" data-tab="upload">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-3">Upload Media</span>
                    </a>
                </li> -->
                <!-- <li>
                    <a href="#" class="sidebar-link" data-tab="analytics">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
                        </svg>
                        <span class="ml-3">Analytics</span>
                    </a>
                </li> -->
                <li>
                    <a href="#" class="sidebar-link" data-tab="settings">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-3">Settings</span>
                    </a>
                </li>
            </ul>

            <!-- User Profile -->
            <div class="absolute bottom-4 left-3 right-3">
                <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white font-semibold">
                        <?php echo strtoupper(substr($currentAdmin['name'] ?? 'A', 0, 1)); ?>
                    </div>
                    <div class="ml-3 flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate"><?php echo htmlspecialchars($currentAdmin['name'] ?? 'Admin'); ?></p>
                        <p class="text-xs text-gray-500 truncate"><?php echo htmlspecialchars($currentAdmin['phone'] ?? ''); ?></p>
                    </div>
                    <button onclick="logout()" class="ml-2 text-gray-400 hover:text-red-600" title="Logout">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="p-4 sm:ml-64 relative z-30">
        <!-- Top Navigation -->
        <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 rounded-lg mb-6">
            <div class="px-6 py-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                        <h1 class="text-xl font-semibold text-gray-900 dark:text-white ml-2" id="page-title">Dashboard</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button type="button" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2L3 7v11a1 1 0 001 1h3v-6h6v6h3a1 1 0 001-1V7l-7-5z"></path>
                            </svg>
                        </button>
                        <button type="button" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Dashboard Tab -->
        <div id="dashboard-tab" class="tab-content relative z-10">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Images</dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-white">245</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Videos</dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-white">12</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Categories</dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-white">6</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Views</dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-white">45.2K</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div> -->
            </div>

            <!-- Recent Activity -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Recent Activity</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <p class="text-gray-500 text-center">Loading recent activity...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gallery Management Tab -->
        <div id="gallery-tab" class="tab-content hidden relative z-10">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Gallery Images</h3>
                        <button type="button" class="text-white bg-primary hover:bg-accent focus:ring-4 focus:ring-primary font-medium rounded-lg text-sm px-5 py-2.5" data-modal-target="add-image-modal" data-modal-toggle="add-image-modal">
                            Add New Image
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <!-- Filter and Search -->
                    <div class="flex flex-col sm:flex-row gap-4 mb-6">
                        <div class="flex-1">
                            <label for="image-search" class="sr-only">Search images</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <input type="text" id="image-search" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary focus:border-primary sm:text-sm" placeholder="Search images...">
                            </div>
                        </div>
                        <select class="category-dropdown block w-48 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md" onchange="fetchAndRenderGallery(1, this.value)">
                            <option value="">All Categories</option>
                        </select>
                    </div>

                    <!-- Images Grid -->
                    <div id="gallery-images-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <!-- Images will be dynamically loaded here -->
                    </div>

                    <!-- Pagination -->
                    <div class="flex items-center justify-between mt-6">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            <!-- Pagination info will be dynamically loaded here -->
                        </div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <!-- Pagination buttons will be dynamically loaded here -->
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Video Management Tab -->
        <div id="videos-tab" class="tab-content hidden relative z-10">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Video Library</h3>
                        <button type="button" class="text-white bg-primary hover:bg-accent focus:ring-4 focus:ring-primary font-medium rounded-lg text-sm px-5 py-2.5" data-modal-target="add-video-modal" data-modal-toggle="add-video-modal">
                            Add New Video
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <!-- Videos Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Video</th>
                                    <th scope="col" class="px-6 py-3">Title</th>
                                    <th scope="col" class="px-6 py-3">Category</th>
                                    <th scope="col" class="px-6 py-3">Duration</th>
                                    <th scope="col" class="px-6 py-3">Date</th>
                                    <th scope="col" class="px-6 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-6 py-4">
                                        <div class="w-20 h-12 bg-gray-200 rounded overflow-hidden">
                                            <img src="https://images.unsplash.com/photo-1497486751825-1233686d5d80?w=100&h=60&fit=crop" alt="Video thumbnail" class="w-full h-full object-cover">
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                        Facility Tour
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">Facility</span>
                                    </td>
                                    <td class="px-6 py-4">5:30</td>
                                    <td class="px-6 py-4">2024-01-15</td>
                                    <td class="px-6 py-4">
                                        <div class="flex space-x-2">
                                            <button class="text-primary hover:text-accent">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                                </svg>
                                            </button>
                                            <button class="text-red-600 hover:text-red-700">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- More video rows -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination for Videos -->
                    <div class="flex items-center justify-between mt-6">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            <!-- Pagination info will be dynamically loaded here -->
                        </div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <!-- Pagination buttons will be dynamically loaded here -->
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories Tab -->
        <div id="categories-tab" class="tab-content hidden relative z-10">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Gallery Categories</h3>
                        <button type="button" class="text-white bg-primary hover:bg-accent focus:ring-4 focus:ring-primary font-medium rounded-lg text-sm px-5 py-2.5" onclick="showAddCategoryModal()">
                            Add Category
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <p class="col-span-full text-gray-500 text-center">Loading categories...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Tab -->
        <div id="upload-tab" class="tab-content hidden relative z-10">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Upload Media</h3>
                </div>
                <div class="p-6">
                    <!-- Upload Area -->
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-12 text-center hover:border-primary transition-colors">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-600">
                            <label for="file-upload" class="font-medium text-primary hover:text-accent cursor-pointer">
                                Upload files
                            </label>
                            or drag and drop
                        </p>
                        <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                        <input id="file-upload" name="file-upload" type="file" class="sr-only" multiple accept="image/*,video/*">
                    </div>

                    <!-- Upload Form -->
                    <form class="mt-6 space-y-6" onsubmit="handleFormUpload(event)">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="media-title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
                                <input type="text" id="media-title" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm" required>
                            </div>
                            <div>
                                <label for="media-category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                                <select id="media-category" class="category-dropdown mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                                    <option value="">Select Category</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label for="media-description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <textarea id="media-description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="bg-primary text-white px-6 py-2 rounded-md hover:bg-accent focus:outline-none focus:ring-2 focus:ring-primary">
                                Upload Media
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Analytics Tab -->
        <div id="analytics-tab" class="tab-content hidden relative z-10">
            <div class="space-y-6">
                <!-- Chart Cards -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Gallery Views</h3>
                        <div class="h-64 bg-gray-100 rounded-lg flex items-center justify-center">
                            <p class="text-gray-500">Chart: Gallery Views Over Time</p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Popular Categories</h3>
                        <div class="h-64 bg-gray-100 rounded-lg flex items-center justify-center">
                            <p class="text-gray-500">Chart: Category Performance</p>
                        </div>
                    </div>
                </div>

                <!-- Popular Content -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Most Viewed Content</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <img src="https://images.unsplash.com/photo-1497486751825-1233686d5d80?w=60&h=40&fit=crop" alt="Content" class="w-15 h-10 rounded object-cover">
                                    <div>
                                        <p class="font-medium text-gray-900">Facility Tour Video</p>
                                        <p class="text-sm text-gray-500">Facility • Video</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium text-gray-900">12.5K views</p>
                                    <p class="text-sm text-green-600">↑ 15%</p>
                                </div>
                            </div>
                            <!-- More popular content items -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Tab -->
        <div id="settings-tab" class="tab-content hidden relative z-10">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Gallery Settings</h3>
                </div>
                <div class="p-6 space-y-6">
                    <!-- Settings Form -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Image Size (MB)</label>
                            <input type="number" value="10" min="1" max="50" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm" id="max-image-size">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Video Size (MB)</label>
                            <input type="number" value="100" min="1" max="1000" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm" id="max-video-size">
                        </div>
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-gray-300 text-primary shadow-sm focus:border-primary focus:ring-primary" id="auto-optimize" checked>
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enable automatic image optimization</span>
                        </label>
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-gray-300 text-primary shadow-sm focus:border-primary focus:ring-primary" id="require-approval">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Require approval for new uploads</span>
                        </label>
                    </div>

                    <div class="flex justify-end">
                        <button type="button" onclick="saveSettings()" class="bg-primary text-white px-6 py-2 rounded-md hover:bg-accent focus:outline-none focus:ring-2 focus:ring-primary">
                            Save Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Image Modal -->
    <div id="add-image-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-2xl max-h-full">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Add New Image</h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="add-image-modal">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                    </button>
                </div>
                <div class="p-4 md:p-5 space-y-4">
                    <form id="add-image-form" class="space-y-4">
                        <div>
                            <label id="image-file-label" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Upload Image</label>
                            <input type="file" id="modal-image-file" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-accent" required>
                            <p id="image-file-help" class="mt-1 text-xs text-gray-500 hidden">Leave empty to keep current image</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
                            <input type="text" id="modal-image-title" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm" placeholder="Enter image title" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                            <select id="modal-image-category" class="category-dropdown mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                                <option value="">Select Category</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <textarea id="modal-image-description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm" placeholder="Enter image description"></textarea>
                        </div>
                    </form>
                </div>
                <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                    <button type="button" id="submit-image-btn" class="text-white bg-primary hover:bg-accent focus:ring-4 focus:outline-none focus:ring-primary font-medium rounded-lg text-sm px-5 py-2.5 text-center">Add Image</button>
                    <button type="button" class="ms-3 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600" data-modal-hide="add-image-modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Video Modal -->
    <div id="add-video-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-2xl max-h-full">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Add New Video</h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="add-video-modal">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                    </button>
                </div>
                <div class="p-4 md:p-5 space-y-4">
                    <form id="add-video-form" class="space-y-4">
                        <div>
                            <label id="video-file-label" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Upload Video</label>
                            <input type="file" id="modal-video-file" accept="video/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-accent">
                            <p id="video-file-help" class="mt-1 text-xs text-gray-500 hidden">Leave empty to keep current video</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
                            <input type="text" id="modal-video-title" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm" placeholder="Enter video title">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                            <select id="modal-video-category" class="category-dropdown mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                                <option value="">Select Category</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <textarea id="modal-video-description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm" placeholder="Enter video description"></textarea>
                        </div>
                    </form>
                </div>
                <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                    <button type="button" id="submit-video-btn" class="text-white bg-primary hover:bg-accent focus:ring-4 focus:outline-none focus:ring-primary font-medium rounded-lg text-sm px-5 py-2.5 text-center">Add Video</button>
                    <button type="button" class="ms-3 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600" data-modal-hide="add-video-modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>
    <script src="./admin.js"></script>
    <script>
    // Session timeout check - check every 5 minutes
    setInterval(function() {
        fetch('../dbs/auth/auth_check.php')
            .then(response => response.json())
            .then(data => {
                if (!data.logged_in) {
                    alert('Your session has expired. Please log in again.');
                    window.location.href = 'login.php?error=session_expired';
                }
            })
            .catch(error => {
                console.error('Session check failed:', error);
            });
    }, 300000); // 5 minutes
    </script>
</body>
</html>