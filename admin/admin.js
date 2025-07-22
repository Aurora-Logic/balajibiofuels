// Admin Dashboard JavaScript

// Tab Management
const sidebarLinks = document.querySelectorAll('.sidebar-link');
const tabContents = document.querySelectorAll('.tab-content');
const pageTitle = document.getElementById('page-title');

sidebarLinks.forEach(link => {
  link.addEventListener('click', (e) => {
    e.preventDefault();
    const targetTab = link.dataset.tab;

    // Remove active class from all links
    sidebarLinks.forEach(l => l.classList.remove('active'));
    link.classList.add('active');

    // Hide all tab contents
    tabContents.forEach(tab => tab.classList.add('hidden'));

    // Show target tab
    const targetTabElement = document.getElementById(`${targetTab}-tab`);
    if (targetTabElement) {
      targetTabElement.classList.remove('hidden');
    }

    // Update page title
    const titles = {
      'dashboard': 'Dashboard',
      'gallery': 'Gallery Management',
      'videos': 'Video Management',
      'categories': 'Categories',
      'upload': 'Upload Media',
      'analytics': 'Analytics',
      'settings': 'Settings'
    };
    pageTitle.textContent = titles[targetTab] || 'Dashboard';
  });
});

// File Upload Handling
const fileUpload = document.getElementById('file-upload');
const uploadArea = fileUpload.closest('.border-dashed');

uploadArea.addEventListener('dragover', (e) => {
  e.preventDefault();
  uploadArea.classList.add('border-primary', 'bg-primary', 'bg-opacity-5');
});

uploadArea.addEventListener('dragleave', (e) => {
  e.preventDefault();
  uploadArea.classList.remove('border-primary', 'bg-primary', 'bg-opacity-5');
});

uploadArea.addEventListener('drop', (e) => {
  e.preventDefault();
  uploadArea.classList.remove('border-primary', 'bg-primary', 'bg-opacity-5');

  const files = e.dataTransfer.files;
  if (files.length > 0) {
    fileUpload.files = files;
    console.log('Files dropped:', files);
  }
});

// Global variables
let currentGalleryPage = 1;
let currentVideoPage = 1;
let categories = [];

// Fetch categories
async function fetchCategories() {
  try {
    const response = await fetch('../dbs/categories.php');
    const result = await response.json();
    if (result.success) {
      categories = result.data;
      populateCategoryDropdowns();
    }
  } catch (error) {
    console.error('Error fetching categories:', error);
  }
}

// Populate category dropdowns
function populateCategoryDropdowns() {
  const dropdowns = document.querySelectorAll('.category-dropdown');
  dropdowns.forEach(dropdown => {
    // Check if this is a filter dropdown or a form dropdown
    const isFilter = dropdown.hasAttribute('onchange') && dropdown.getAttribute('onchange').includes('fetch');
    const defaultText = isFilter ? 'All Categories' : 'Select Category';

    dropdown.innerHTML = `<option value="">${defaultText}</option>` +
      categories.map(cat => `<option value="${cat.id}">${cat.name}</option>`).join('');
  });
}

// Show toast notification
function showToast(message, type = 'success') {
  const toast = document.createElement('div');
  toast.className = `fixed top-4 right-4 z-50 p-4 rounded-lg text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} transform transition-all duration-300 translate-x-full`;
  toast.textContent = message;
  document.body.appendChild(toast);

  // Animate in
  setTimeout(() => toast.classList.remove('translate-x-full'), 100);

  // Animate out and remove
  setTimeout(() => {
    toast.classList.add('translate-x-full');
    setTimeout(() => {
      if (document.body.contains(toast)) {
        document.body.removeChild(toast);
      }
    }, 300);
  }, 3000);
}

// Dashboard functions
async function fetchDashboardData() {
  try {
    const response = await fetch('../dbs/dashboard.php');
    const result = await response.json();
    if (result.success) {
      updateDashboard(result.data);
    }
  } catch (error) {
    console.error('Error fetching dashboard data:', error);
  }
}

function updateDashboard(data) {
  const statsCards = document.querySelectorAll('#dashboard-tab .bg-white');
  if (statsCards.length >= 3) {
    statsCards[0].querySelector('.p-5').innerHTML = `
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Images</dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white">${data.total_images}</dd>
                            </dl>
                        </div>
                    </div>
                `;

    statsCards[1].querySelector('.p-5').innerHTML = `
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Videos</dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white">${data.total_videos}</dd>
                            </dl>
                        </div>
                    </div>
                `;

    statsCards[2].querySelector('.p-5').innerHTML = `
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Categories</dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white">${data.total_categories}</dd>
                            </dl>
                        </div>
                    </div>
                `;
  }

  // Update recent activity
  const activityContainer = document.querySelector('#dashboard-tab .space-y-4');
  if (activityContainer && data.recent_activity) {
    activityContainer.innerHTML = data.recent_activity.map(activity => `
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-primary bg-opacity-10 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-primary" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 dark:text-white">${activity.description}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">${new Date(activity.created_at).toLocaleString()}</p>
                        </div>
                    </div>
                `).join('') || '<p class="text-gray-500 dark:text-gray-400 text-center">No recent activity</p>';
  }
}

// Dynamic Gallery Fetch and Render
async function fetchAndRenderGallery(page = 1, categoryId = '', search = '') {
  const grid = document.getElementById('gallery-images-grid');
  if (!grid) return;

  grid.innerHTML = '<div class="col-span-full text-center text-gray-400">Loading...</div>';
  currentGalleryPage = page;

  try {
    let url = `../dbs/gallery.php?page=${page}&limit=12`;
    if (categoryId) url += `&category_id=${categoryId}`;
    if (search) url += `&search=${encodeURIComponent(search)}`;

    const res = await fetch(url);
    const result = await res.json();

    if (!result.success) throw new Error(result.error || 'Failed to load images');

    const images = result.data;
    if (!images.length) {
      grid.innerHTML = '<div class="col-span-full text-center text-gray-400">No images found.</div>';
      return;
    }

    grid.innerHTML = images.map(img => `
                    <div class="relative group bg-white rounded-lg overflow-hidden shadow-sm border border-gray-200">
                        <div class="aspect-w-1 aspect-h-1">
                            <img src="../${img.image_path}" alt="${img.title}" class="w-full h-48 object-cover group-hover:opacity-75 transition-opacity" onerror="this.src='https://via.placeholder.com/300x200?text=Image+Not+Found'">
                        </div>
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-200 flex items-center justify-center opacity-0 group-hover:opacity-100">
                            <div class="flex space-x-2">
                                <button onclick="editImage(${img.id})" class="bg-white text-gray-900 px-3 py-1 rounded-md text-sm font-medium hover:bg-gray-100">Edit</button>
                                <button onclick="deleteImage(${img.id})" class="bg-red-500 text-white px-3 py-1 rounded-md text-sm font-medium hover:bg-red-600">Delete</button>
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="text-sm font-medium text-gray-900 truncate">${img.title}</h3>
                            <p class="text-xs text-gray-500 mt-1">${img.category_name || 'No Category'}</p>
                            <p class="text-xs text-gray-400 mt-1">${new Date(img.uploaded_at).toLocaleDateString()}</p>
                        </div>
                    </div>
                `).join('');

    // Update pagination
    updatePagination('gallery', result.pagination);

  } catch (err) {
    grid.innerHTML = `<div class='col-span-full text-center text-red-500'>${err.message}</div>`;
  }
}

// Dynamic Videos Fetch and Render
async function fetchAndRenderVideos(page = 1, categoryId = '', search = '') {
  const container = document.querySelector('#videos-tab .overflow-x-auto');
  if (!container) return;

  container.innerHTML = '<div class="text-center text-gray-400 p-8">Loading...</div>';
  currentVideoPage = page;

  try {
    let url = `../dbs/videos.php?page=${page}&limit=10`;
    if (categoryId) url += `&category_id=${categoryId}`;
    if (search) url += `&search=${encodeURIComponent(search)}`;

    const res = await fetch(url);
    const result = await res.json();

    if (!result.success) throw new Error(result.error || 'Failed to load videos');

    const videos = result.data;
    if (!videos.length) {
      container.innerHTML = '<div class="text-center text-gray-400 p-8">No videos found.</div>';
      return;
    }

    container.innerHTML = `
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Video</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            ${videos.map(video => `
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-16 w-24">
                                                <video class="h-16 w-24 rounded-lg object-cover" preload="metadata">
                                                    <source src="../${video.video_path}" type="video/mp4">
                                                    Video not supported
                                                </video>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">${video.title}</div>
                                                <div class="text-sm text-gray-500">${video.description ? video.description.substring(0, 50) + '...' : 'No description'}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${video.category_name || 'No Category'}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${video.duration || 'Unknown'}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${new Date(video.uploaded_at).toLocaleDateString()}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="editVideo(${video.id})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                        <button onclick="deleteVideo(${video.id})" class="text-red-600 hover:text-red-900">Delete</button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `;

    // Update pagination
    updatePagination('videos', result.pagination);

  } catch (err) {
    container.innerHTML = `<div class='text-center text-red-500 p-8'>${err.message}</div>`;
  }
}

// Fetch and render categories
async function fetchAndRenderCategories() {
  const container = document.querySelector('#categories-tab .grid');
  if (!container) return;

  container.innerHTML = '<div class="col-span-full text-center text-gray-400">Loading...</div>';

  try {
    const res = await fetch('../dbs/categories.php');
    const result = await res.json();

    if (!result.success) throw new Error(result.error || 'Failed to load categories');

    const categories = result.data;
    if (!categories.length) {
      container.innerHTML = '<div class="col-span-full text-center text-gray-400">No categories found.</div>';
      return;
    }

    container.innerHTML = categories.map(category => `
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">${category.name}</h3>
                            <div class="flex space-x-2">
                                <button onclick="editCategory(${category.id})" class="text-indigo-600 hover:text-indigo-900 text-sm">Edit</button>
                                <button onclick="deleteCategory(${category.id})" class="text-red-600 hover:text-red-900 text-sm">Delete</button>
                            </div>
                        </div>
                        <p class="text-gray-600 text-sm mb-4">${category.description || 'No description'}</p>
                        <div class="flex items-center justify-between text-sm text-gray-500">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                                </svg>
                                ${category.image_count} images
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"></path>
                                </svg>
                                ${category.video_count} videos
                            </div>
                        </div>
                    </div>
                `).join('');

  } catch (err) {
    container.innerHTML = `<div class='col-span-full text-center text-red-500'>${err.message}</div>`;
  }
}

// Update pagination (dynamic implementation)
function updatePagination(type, pagination) {
  console.log(`Updating pagination for ${type}:`, pagination);

  const paginationContainer = document.querySelector(`#${type}-tab .flex.items-center.justify-between.mt-6`);
  if (!paginationContainer) {
    console.error(`Pagination container not found for ${type}`);
    return;
  }

  // If no pagination data, hide the pagination
  if (!pagination) {
    paginationContainer.style.display = 'none';
    return;
  } else {
    paginationContainer.style.display = 'flex';
  }

  const currentPage = pagination.current_page || pagination.currentPage || 1;
  const totalPages = pagination.total_pages || pagination.totalPages || 1;
  const totalCount = pagination.total_items || pagination.totalCount || 0;
  const itemsPerPage = pagination.items_per_page || pagination.itemsPerPage || 12;

  // Calculate start and end indices
  const startIndex = Math.min((currentPage - 1) * itemsPerPage + 1, totalCount);
  const endIndex = Math.min(currentPage * itemsPerPage, totalCount);

  // Update the results text
  const resultsText = paginationContainer.querySelector('.text-sm');
  if (resultsText) {
    if (totalCount === 0) {
      resultsText.textContent = 'No results found';
    } else {
      resultsText.textContent = `Showing ${startIndex} to ${endIndex} of ${totalCount} results`;
    }
  }

  // Generate pagination buttons
  const nav = paginationContainer.querySelector('nav');
  if (!nav) return;

  // Don't show pagination if there's only one page or no results
  if (totalPages <= 1) {
    nav.innerHTML = '';
    return;
  }

  let paginationHtml = '';

  // Previous button
  if (currentPage > 1) {
    paginationHtml += `<button onclick="handlePaginationClick('${type}', ${currentPage - 1})" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">Previous</button>`;
  } else {
    paginationHtml += `<button disabled class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:text-gray-500">Previous</button>`;
  }

  // Page numbers
  const startPage = Math.max(1, currentPage - 2);
  const endPage = Math.min(totalPages, currentPage + 2);

  // First page and ellipsis
  if (startPage > 1) {
    paginationHtml += `<button onclick="handlePaginationClick('${type}', 1)" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">1</button>`;
    if (startPage > 2) {
      paginationHtml += `<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">...</span>`;
    }
  }

  // Current page range
  for (let i = startPage; i <= endPage; i++) {
    if (i === currentPage) {
      paginationHtml += `<button class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-primary text-sm font-medium text-white">${i}</button>`;
    } else {
      paginationHtml += `<button onclick="handlePaginationClick('${type}', ${i})" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">${i}</button>`;
    }
  }

  // Last page and ellipsis
  if (endPage < totalPages) {
    if (endPage < totalPages - 1) {
      paginationHtml += `<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">...</span>`;
    }
    paginationHtml += `<button onclick="handlePaginationClick('${type}', ${totalPages})" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">${totalPages}</button>`;
  }

  // Next button
  if (currentPage < totalPages) {
    paginationHtml += `<button onclick="handlePaginationClick('${type}', ${currentPage + 1})" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">Next</button>`;
  } else {
    paginationHtml += `<button disabled class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:text-gray-500">Next</button>`;
  }

  nav.innerHTML = paginationHtml;
}

// Handle pagination clicks
function handlePaginationClick(type, page) {
  const currentCategoryFilter = document.querySelector(`#${type}-tab .category-dropdown`)?.value || '';
  const currentSearch = document.querySelector(`#${type}-tab input[type="text"]`)?.value || '';

  if (type === 'gallery') {
    fetchAndRenderGallery(page, currentCategoryFilter, currentSearch);
  } else if (type === 'videos') {
    fetchAndRenderVideos(page, currentCategoryFilter, currentSearch);
  }
}

// CRUD Operations
async function deleteImage(id) {
  if (!confirm('Are you sure you want to delete this image?')) return;

  try {
    const response = await fetch('../dbs/gallery.php', {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ id })
    });

    const result = await response.json();
    if (result.success) {
      showToast('Image deleted successfully');
      fetchAndRenderGallery(currentGalleryPage);
      fetchDashboardData(); // Refresh dashboard stats
    } else {
      throw new Error(result.error);
    }
  } catch (error) {
    showToast('Error deleting image: ' + error.message, 'error');
  }
}

async function deleteVideo(id) {
  if (!confirm('Are you sure you want to delete this video?')) return;

  try {
    const response = await fetch('../dbs/videos.php', {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ id })
    });

    const result = await response.json();
    if (result.success) {
      showToast('Video deleted successfully');
      fetchAndRenderVideos(currentVideoPage);
      fetchDashboardData(); // Refresh dashboard stats
    } else {
      throw new Error(result.error);
    }
  } catch (error) {
    showToast('Error deleting video: ' + error.message, 'error');
  }
}

async function deleteCategory(id) {
  if (!confirm('Are you sure you want to delete this category?')) return;

  try {
    const response = await fetch('../dbs/categories.php', {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ id })
    });

    const result = await response.json();
    if (result.success) {
      showToast('Category deleted successfully');
      fetchAndRenderCategories();
      fetchCategories(); // Refresh category dropdowns
      fetchDashboardData(); // Refresh dashboard stats
    } else {
      throw new Error(result.error);
    }
  } catch (error) {
    showToast('Error deleting category: ' + error.message, 'error');
  }
}

function editImage(id) {
  // Fetch image details first
  fetchImageDetails(id).then(image => {
    if (image) {
      // Populate the modal with existing data
      document.getElementById('modal-image-title').value = image.title || '';
      document.getElementById('modal-image-category').value = image.category_id || '';
      document.getElementById('modal-image-description').value = image.description || '';

      // Change modal title and button text
      document.querySelector('#add-image-modal h3').textContent = 'Edit Image';
      document.getElementById('submit-image-btn').textContent = 'Update Image';

      // Update file input for edit mode
      const fileInput = document.getElementById('modal-image-file');
      const fileLabel = document.getElementById('image-file-label');
      const fileHelp = document.getElementById('image-file-help');

      fileInput.removeAttribute('required');
      fileLabel.textContent = 'Replace Image (Optional)';
      fileHelp.classList.remove('hidden');

      // Store the image ID for update
      document.getElementById('add-image-form').setAttribute('data-edit-id', id);

      // Open modal
      const modal = document.getElementById('add-image-modal');
      modal.classList.remove('hidden');
      modal.setAttribute('aria-hidden', 'false');
    }
  });
}

async function fetchImageDetails(id) {
  try {
    const response = await fetch(`../dbs/gallery.php?id=${id}`);
    const result = await response.json();
    return result.success ? result.data : null;
  } catch (error) {
    console.error('Error fetching image details:', error);
    return null;
  }
}

function editVideo(id) {
  // Fetch video details first
  fetchVideoDetails(id).then(video => {
    if (video) {
      // Populate the modal with existing data
      document.getElementById('modal-video-title').value = video.title || '';
      document.getElementById('modal-video-category').value = video.category_id || '';
      document.getElementById('modal-video-description').value = video.description || '';

      // Change modal title and button text
      document.querySelector('#add-video-modal h3').textContent = 'Edit Video';
      document.getElementById('submit-video-btn').textContent = 'Update Video';

      // Update file input for edit mode
      const fileInput = document.getElementById('modal-video-file');
      const fileLabel = document.getElementById('video-file-label');
      const fileHelp = document.getElementById('video-file-help');

      fileInput.removeAttribute('required');
      fileLabel.textContent = 'Replace Video (Optional)';
      fileHelp.classList.remove('hidden');

      // Store the video ID for update
      document.getElementById('add-video-form').setAttribute('data-edit-id', id);

      // Open modal
      const modal = document.getElementById('add-video-modal');
      modal.classList.remove('hidden');
      modal.setAttribute('aria-hidden', 'false');
    }
  });
}

async function fetchVideoDetails(id) {
  try {
    const response = await fetch(`../dbs/videos.php?id=${id}`);
    const result = await response.json();
    return result.success ? result.data : null;
  } catch (error) {
    console.error('Error fetching video details:', error);
    return null;
  }
}

function editCategory(id) {
  // Fetch category details first
  fetchCategoryDetails(id).then(category => {
    if (category) {
      showEditCategoryModal(category);
    }
  });
}

async function fetchCategoryDetails(id) {
  try {
    const response = await fetch(`../dbs/categories.php?id=${id}`);
    const result = await response.json();
    return result.success ? result.data : null;
  } catch (error) {
    console.error('Error fetching category details:', error);
    return null;
  }
}

function showEditCategoryModal(category) {
  // Create modal HTML if it doesn't exist
  let modal = document.getElementById('edit-category-modal');
  if (!modal) {
    const modalHTML = `
      <div id="edit-category-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
          <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
              <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Edit Category</h3>
              <button type="button" onclick="closeEditCategoryModal()" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
              </button>
            </div>
            <div class="p-4 md:p-5">
              <form id="edit-category-form" class="space-y-4">
                <input type="hidden" id="edit-category-id">
                <div>
                  <label for="edit-category-name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Category Name</label>
                  <input type="text" id="edit-category-name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required>
                </div>
                <div>
                  <label for="edit-category-description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Description</label>
                  <textarea id="edit-category-description" rows="3" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"></textarea>
                </div>
                <div class="flex justify-end space-x-2">
                  <button type="button" onclick="closeEditCategoryModal()" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">Cancel</button>
                  <button type="submit" class="text-white bg-primary hover:bg-accent focus:ring-4 focus:outline-none focus:ring-primary font-medium rounded-lg text-sm px-5 py-2.5 text-center">Update Category</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    modal = document.getElementById('edit-category-modal');

    // Add form submit handler
    document.getElementById('edit-category-form').addEventListener('submit', handleEditCategorySubmit);
  }

  // Populate form with category data
  document.getElementById('edit-category-id').value = category.id;
  document.getElementById('edit-category-name').value = category.name;
  document.getElementById('edit-category-description').value = category.description || '';

  // Show modal
  modal.classList.remove('hidden');
  modal.setAttribute('aria-hidden', 'false');
  document.body.classList.add('overflow-hidden');
}

function closeEditCategoryModal() {
  const modal = document.getElementById('edit-category-modal');
  if (modal) {
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('overflow-hidden');
  }
}

async function handleEditCategorySubmit(e) {
  e.preventDefault();

  const submitBtn = e.target.querySelector('button[type="submit"]');
  const originalText = submitBtn.textContent;
  submitBtn.textContent = 'Updating...';
  submitBtn.disabled = true;

  const id = document.getElementById('edit-category-id').value;
  const name = document.getElementById('edit-category-name').value.trim();
  const description = document.getElementById('edit-category-description').value.trim();

  try {
    const response = await fetch('../dbs/categories.php', {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        id: parseInt(id),
        name: name,
        description: description
      })
    });

    const result = await response.json();

    if (result.success) {
      showToast('Category updated successfully');
      closeEditCategoryModal();
      fetchAndRenderCategories();
      fetchCategories(); // Refresh category dropdowns
      fetchDashboardData(); // Refresh dashboard stats
    } else {
      throw new Error(result.error || 'Failed to update category');
    }
  } catch (error) {
    showToast('Error updating category: ' + error.message, 'error');
  } finally {
    submitBtn.textContent = originalText;
    submitBtn.disabled = false;
  }
}

// Initialize on tab switch
function initializeTab(tabName) {
  switch (tabName) {
    case 'dashboard':
      fetchDashboardData();
      break;
    case 'gallery':
      fetchAndRenderGallery();
      break;
    case 'videos':
      fetchAndRenderVideos();
      break;
    case 'categories':
      fetchAndRenderCategories();
      break;
    case 'upload':
      setupUploadForm();
      break;
    case 'settings':
      loadSettings();
      break;
  }
}

// Setup upload form functionality
function setupUploadForm() {
  const uploadArea = document.querySelector('#upload-tab .border-dashed');
  const fileInput = document.createElement('input');
  fileInput.type = 'file';
  fileInput.multiple = true;
  fileInput.accept = 'image/*,video/*';
  fileInput.style.display = 'none';
  document.body.appendChild(fileInput);

  if (uploadArea && !uploadArea.hasAttribute('data-initialized')) {
    uploadArea.setAttribute('data-initialized', 'true');

    // Add upload content if not exists
    if (!uploadArea.innerHTML.trim()) {
      uploadArea.innerHTML = `
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <div class="mt-4">
                            <p class="text-lg font-medium text-gray-900">Drop files here or click to upload</p>
                            <p class="text-sm text-gray-500 mt-2">Support for images (JPG, PNG, GIF, WebP) and videos (MP4, AVI, MOV)</p>
                        </div>
                    `;
    }

    uploadArea.addEventListener('click', () => fileInput.click());

    uploadArea.addEventListener('dragover', (e) => {
      e.preventDefault();
      uploadArea.classList.add('border-primary', 'bg-primary', 'bg-opacity-5');
    });

    uploadArea.addEventListener('dragleave', (e) => {
      e.preventDefault();
      uploadArea.classList.remove('border-primary', 'bg-primary', 'bg-opacity-5');
    });

    uploadArea.addEventListener('drop', (e) => {
      e.preventDefault();
      uploadArea.classList.remove('border-primary', 'bg-primary', 'bg-opacity-5');

      const files = e.dataTransfer.files;
      if (files.length > 0) {
        handleFileUpload(files);
      }
    });

    fileInput.addEventListener('change', (e) => {
      if (e.target.files.length > 0) {
        handleFileUpload(e.target.files);
      }
    });
  }
}

// Handle file upload
async function handleFileUpload(files) {
  const formData = new FormData();

  for (let file of files) {
    if (file.type.startsWith('image/')) {
      formData.append('image', file);
      formData.set('upload_type', 'image');
    } else if (file.type.startsWith('video/')) {
      formData.append('video', file);
      formData.set('upload_type', 'video');
    }
  }

  // Add default title and description
  formData.set('title', files[0].name.split('.')[0]);
  formData.set('description', 'Uploaded via admin panel');

  try {
    showToast('Uploading files...', 'info');

    const response = await fetch('../dbs/upload.php', {
      method: 'POST',
      body: formData
    });

    const result = await response.json();
    if (result.success) {
      showToast(`${result.data.length} file(s) uploaded successfully!`);
      fetchDashboardData(); // Refresh dashboard stats
    } else {
      throw new Error(result.error);
    }
  } catch (error) {
    showToast('Error uploading files: ' + error.message, 'error');
  }
}

// Initialize everything on page load
document.addEventListener('DOMContentLoaded', function () {
  fetchCategories();
  fetchDashboardData();

  // Initialize search functionality
  setTimeout(() => {
    const imageSearch = document.getElementById('image-search');
    if (imageSearch) {
      imageSearch.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        const currentCategory = document.querySelector('#gallery-tab .category-dropdown')?.value || '';
        fetchAndRenderGallery(1, currentCategory, searchTerm);
      });
    }
  }, 100);

  // Update sidebar link event listeners to initialize tabs
  sidebarLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      const targetTab = link.dataset.tab;

      // Remove active class from all links
      sidebarLinks.forEach(l => l.classList.remove('active'));
      link.classList.add('active');

      // Hide all tab contents
      tabContents.forEach(tab => tab.classList.add('hidden'));

      // Show target tab
      const targetTabElement = document.getElementById(`${targetTab}-tab`);
      if (targetTabElement) {
        targetTabElement.classList.remove('hidden');
      }

      // Initialize tab content
      initializeTab(targetTab);
    });
  });

  // Initialize the default active tab (dashboard)
  initializeTab('dashboard');

  // Add event listener for modal image submission
  const submitImageBtn = document.getElementById('submit-image-btn');
  console.log('Submit button found:', !!submitImageBtn);
  if (submitImageBtn) {
    submitImageBtn.addEventListener('click', (e) => {
      e.preventDefault();
      console.log('Submit button clicked!');
      handleModalImageSubmission();
    });
  } else {
    console.error('Submit button not found!');
  }

  // Add form submission handler as backup
  const addImageForm = document.getElementById('add-image-form');
  console.log('Add image form found:', !!addImageForm);
  if (addImageForm) {
    addImageForm.addEventListener('submit', (e) => {
      e.preventDefault();
      console.log('Form submitted!');
      handleModalImageSubmission();
    });
  } else {
    console.error('Add image form not found!');
  }

  // Add event listener for modal video submission
  const submitVideoBtn = document.getElementById('submit-video-btn');
  console.log('Submit video button found:', !!submitVideoBtn);
  if (submitVideoBtn) {
    submitVideoBtn.addEventListener('click', (e) => {
      e.preventDefault();
      console.log('Submit video button clicked!');
      handleModalVideoSubmission();
    });
  } else {
    console.error('Submit video button not found!');
  }

  // Add form submission handler for video as backup
  const addVideoForm = document.getElementById('add-video-form');
  console.log('Add video form found:', !!addVideoForm);
  if (addVideoForm) {
    addVideoForm.addEventListener('submit', (e) => {
      e.preventDefault();
      console.log('Video form submitted!');
      handleModalVideoSubmission();
    });
  } else {
    console.error('Add video form not found!');
  }

  // Add event listeners for modal reset when opened for new items
  document.querySelectorAll('[data-modal-target="add-image-modal"]').forEach(btn => {
    btn.addEventListener('click', resetImageModal);
  });

  document.querySelectorAll('[data-modal-target="add-video-modal"]').forEach(btn => {
    btn.addEventListener('click', resetVideoModal);
  });

  // Debug modal elements
  setTimeout(() => {
    const fileInput = document.getElementById('modal-image-file');
    const titleInput = document.getElementById('modal-image-title');
    const categoryInput = document.getElementById('modal-image-category');
    const descriptionInput = document.getElementById('modal-image-description');

    const videoFileInput = document.getElementById('modal-video-file');
    const videoTitleInput = document.getElementById('modal-video-title');
    const videoCategoryInput = document.getElementById('modal-video-category');
    const videoDescriptionInput = document.getElementById('modal-video-description');

    console.log('Modal elements found:');
    console.log('- Image File input:', !!fileInput);
    console.log('- Image Title input:', !!titleInput);
    console.log('- Image Category input:', !!categoryInput);
    console.log('- Image Description input:', !!descriptionInput);
    console.log('- Video File input:', !!videoFileInput);
    console.log('- Video Title input:', !!videoTitleInput);
    console.log('- Video Category input:', !!videoCategoryInput);
    console.log('- Video Description input:', !!videoDescriptionInput);
  }, 1000);
});

// Sidebar styles
const style = document.createElement('style');
style.textContent = `
            .sidebar-link {
                display: flex;
                align-items: center;
                padding: 0.75rem;
                text-decoration: none;
                color: #6B7280;
                border-radius: 0.5rem;
                transition: all 0.2s;
            }
            .sidebar-link:hover {
                background-color: #F3F4F6;
                color: #10b981;
            }
            .sidebar-link.active {
                background-color: #10b981;
                color: white;
            }
            .sidebar-link.active:hover {
                background-color: #059669;
            }
        `;
document.head.appendChild(style);

// Handle form upload
async function handleFormUpload(event) {
  event.preventDefault();

  const title = document.getElementById('media-title').value;
  const description = document.getElementById('media-description').value;
  const categoryId = document.getElementById('media-category').value;

  if (!title.trim()) {
    showToast('Please enter a title', 'error');
    return;
  }

  // Since files are uploaded via drag&drop or file selection, this form is for metadata only
  // Show message to user
  showToast('Please use the drag & drop area to upload files', 'error');
}

// Show add category modal
function showAddCategoryModal() {
  // Create modal HTML if it doesn't exist
  let modal = document.getElementById('add-category-modal');
  if (!modal) {
    const modalHTML = `
      <div id="add-category-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
          <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
              <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Add New Category</h3>
              <button type="button" onclick="closeAddCategoryModal()" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
              </button>
            </div>
            <div class="p-4 md:p-5">
              <form id="add-category-form" class="space-y-4">
                <div>
                  <label for="add-category-name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Category Name</label>
                  <input type="text" id="add-category-name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required>
                </div>
                <div>
                  <label for="add-category-description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Description</label>
                  <textarea id="add-category-description" rows="3" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"></textarea>
                </div>
                <div class="flex justify-end space-x-2">
                  <button type="button" onclick="closeAddCategoryModal()" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">Cancel</button>
                  <button type="submit" class="text-white bg-primary hover:bg-accent focus:ring-4 focus:outline-none focus:ring-primary font-medium rounded-lg text-sm px-5 py-2.5 text-center">Add Category</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    modal = document.getElementById('add-category-modal');

    // Add form submit handler
    document.getElementById('add-category-form').addEventListener('submit', handleAddCategorySubmit);
  }

  // Reset form
  document.getElementById('add-category-name').value = '';
  document.getElementById('add-category-description').value = '';

  // Show modal
  modal.classList.remove('hidden');
  modal.setAttribute('aria-hidden', 'false');
  document.body.classList.add('overflow-hidden');
}

function closeAddCategoryModal() {
  const modal = document.getElementById('add-category-modal');
  if (modal) {
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('overflow-hidden');
  }
}

async function handleAddCategorySubmit(e) {
  e.preventDefault();

  const submitBtn = e.target.querySelector('button[type="submit"]');
  const originalText = submitBtn.textContent;
  submitBtn.textContent = 'Adding...';
  submitBtn.disabled = true;

  const name = document.getElementById('add-category-name').value.trim();
  const description = document.getElementById('add-category-description').value.trim();

  try {
    const response = await fetch('../dbs/categories.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ name, description })
    });

    const result = await response.json();

    if (result.success) {
      showToast('Category created successfully');
      closeAddCategoryModal();
      fetchAndRenderCategories();
      fetchCategories(); // Refresh category dropdowns
      fetchDashboardData(); // Refresh dashboard stats
    } else {
      throw new Error(result.error || 'Failed to create category');
    }
  } catch (error) {
    showToast('Error creating category: ' + error.message, 'error');
  } finally {
    submitBtn.textContent = originalText;
    submitBtn.disabled = false;
  }
}

// Handle modal video submission
async function handleModalVideoSubmission() {
  console.log('Starting video submission...');

  const fileInput = document.getElementById('modal-video-file');
  const titleInput = document.getElementById('modal-video-title');
  const categoryInput = document.getElementById('modal-video-category');
  const descriptionInput = document.getElementById('modal-video-description');
  const form = document.getElementById('add-video-form');
  const editId = form.getAttribute('data-edit-id');

  const isEditing = !!editId;

  // Validate required fields - file is only required for new uploads
  if (!isEditing && !fileInput.files[0]) {
    console.error('No file selected for new video');
    showToast('Please select a video file', 'error');
    return;
  }

  if (!titleInput.value.trim()) {
    console.error('No title provided');
    showToast('Please enter a title', 'error');
    titleInput.focus();
    return;
  }

  // Log form data
  console.log('File:', fileInput.files[0]);
  console.log('Title:', titleInput.value);
  console.log('Category:', categoryInput.value);
  console.log('Description:', descriptionInput.value);
  console.log('Is Editing:', isEditing, editId);

  const formData = new FormData();

  // Only append video if we have one (for new uploads or when replacing existing)
  if (fileInput.files[0]) {
    formData.append('video', fileInput.files[0]);
    console.log('Video file added to FormData');

    // Get video duration automatically
    try {
      const duration = await getVideoDuration(fileInput.files[0]);
      formData.append('duration', duration);
      console.log('Video duration detected:', duration, 'seconds');
    } catch (error) {
      console.warn('Could not detect video duration:', error);
    }
  } else if (isEditing) {
    console.log('No new video file - updating metadata only');
  }

  formData.append('title', titleInput.value.trim());
  formData.append('description', descriptionInput.value.trim());
  formData.append('category_id', categoryInput.value);

  if (isEditing) {
    formData.append('id', editId);
    formData.append('action', 'update'); // Use 'action' instead of '_method'
    console.log('Edit mode: ID =', editId);
  } else {
    formData.append('action', 'create');
    console.log('Create mode');
  }

  try {
    // Show loading state
    const submitBtn = document.getElementById('submit-video-btn');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = isEditing ? 'Updating...' : 'Uploading...';
    submitBtn.disabled = true;

    console.log('Sending request to videos.php...');
    const response = await fetch('../dbs/videos.php', {
      method: 'POST',
      body: formData
    });

    console.log('Response status:', response.status);
    const responseText = await response.text();
    console.log('Response text:', responseText);

    let result;
    try {
      result = JSON.parse(responseText);
    } catch (parseError) {
      console.error('JSON parse error:', parseError);
      console.error('Response was:', responseText);
      throw new Error('Server returned invalid JSON. Response: ' + responseText.substring(0, 200));
    }

    console.log('Parsed result:', result);

    if (result.success) {
      showToast(isEditing ? 'Video updated successfully!' : 'Video uploaded successfully!');

      // Reset modal to add mode
      resetVideoModal();

      // Close modal
      const modal = document.getElementById('add-video-modal');
      modal.classList.add('hidden');
      modal.setAttribute('aria-hidden', 'true');

      // Remove backdrop
      document.body.style.overflow = '';
      const backdrop = document.querySelector('[modal-backdrop]');
      if (backdrop) {
        backdrop.remove();
      }

      // Refresh videos if we're on the videos tab
      const videosTab = document.getElementById('videos-tab');
      if (!videosTab.classList.contains('hidden')) {
        fetchAndRenderVideos(1);
      }

      // Refresh dashboard stats
      fetchDashboardData();

    } else {
      throw new Error(result.error || (isEditing ? 'Update failed' : 'Upload failed'));
    }
  } catch (error) {
    console.error('Upload error:', error);
    showToast('Error ' + (isEditing ? 'updating' : 'uploading') + ' video: ' + error.message, 'error');
  } finally {
    // Restore button state
    const submitBtn = document.getElementById('submit-video-btn');
    if (submitBtn) {
      submitBtn.textContent = isEditing ? 'Update Video' : 'Add Video';
      submitBtn.disabled = false;
    }
  }
}

// Reset video modal to add mode
function resetVideoModal() {
  const form = document.getElementById('add-video-form');
  form.reset();
  form.removeAttribute('data-edit-id');

  // Reset modal title and button
  document.querySelector('#add-video-modal h3').textContent = 'Add New Video';
  document.getElementById('submit-video-btn').textContent = 'Add Video';

  // Reset file input labels
  const fileInput = document.getElementById('modal-video-file');
  const fileLabel = document.getElementById('video-file-label');
  const fileHelp = document.getElementById('video-file-help');

  fileLabel.textContent = 'Upload Video';
  fileHelp.classList.add('hidden');

  // Note: Video file input is never required since it's optional in both modes
}

// Get video duration automatically
function getVideoDuration(file) {
  return new Promise((resolve, reject) => {
    const video = document.createElement('video');
    video.preload = 'metadata';

    video.onloadedmetadata = function () {
      window.URL.revokeObjectURL(video.src);
      const duration = Math.round(video.duration);
      resolve(duration);
    };

    video.onerror = function () {
      reject(new Error('Error loading video metadata'));
    };

    video.src = URL.createObjectURL(file);
  });
}

// Handle modal image submission
async function handleModalImageSubmission() {
  console.log('Starting image submission...');

  const fileInput = document.getElementById('modal-image-file');
  const titleInput = document.getElementById('modal-image-title');
  const categoryInput = document.getElementById('modal-image-category');
  const descriptionInput = document.getElementById('modal-image-description');
  const form = document.getElementById('add-image-form');
  const editId = form.getAttribute('data-edit-id');

  const isEditing = !!editId;

  // Validate required fields - file is only required for new uploads
  if (!isEditing && !fileInput.files[0]) {
    console.error('No file selected for new image');
    showToast('Please select an image file', 'error');
    return;
  }

  if (!titleInput.value.trim()) {
    console.error('No title provided');
    showToast('Please enter a title', 'error');
    titleInput.focus();
    return;
  }

  // Log form data
  console.log('File:', fileInput.files[0]);
  console.log('Title:', titleInput.value);
  console.log('Category:', categoryInput.value);
  console.log('Description:', descriptionInput.value);
  console.log('Is Editing:', isEditing, editId);

  const formData = new FormData();

  // Only append image if we have one (for new uploads or when replacing existing)
  if (fileInput.files[0]) {
    formData.append('image', fileInput.files[0]);
    console.log('Image file added to FormData');
  } else if (isEditing) {
    console.log('No new image file - updating metadata only');
  }

  formData.append('title', titleInput.value.trim());
  formData.append('description', descriptionInput.value.trim());
  formData.append('category_id', categoryInput.value);

  if (isEditing) {
    formData.append('id', editId);
    formData.append('action', 'update'); // Use 'action' instead of '_method'
    console.log('Edit mode: ID =', editId);
  } else {
    formData.append('action', 'create');
    console.log('Create mode');
  }

  try {
    // Show loading state
    const submitBtn = document.getElementById('submit-image-btn');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = isEditing ? 'Updating...' : 'Uploading...';
    submitBtn.disabled = true;

    console.log('Sending request to gallery.php...');
    const response = await fetch('../dbs/gallery.php', {
      method: 'POST',
      body: formData
    });

    console.log('Response status:', response.status);
    const responseText = await response.text();
    console.log('Response text:', responseText);

    let result;
    try {
      result = JSON.parse(responseText);
    } catch (parseError) {
      console.error('JSON parse error:', parseError);
      console.error('Response was:', responseText);
      throw new Error('Server returned invalid JSON. Response: ' + responseText.substring(0, 200));
    }

    console.log('Parsed result:', result);

    if (result.success) {
      showToast(isEditing ? 'Image updated successfully!' : 'Image uploaded successfully!');

      // Reset modal to add mode
      resetImageModal();

      // Close modal
      const modal = document.getElementById('add-image-modal');
      modal.classList.add('hidden');
      modal.setAttribute('aria-hidden', 'true');

      // Remove backdrop
      document.body.style.overflow = '';
      const backdrop = document.querySelector('[modal-backdrop]');
      if (backdrop) {
        backdrop.remove();
      }

      // Refresh gallery if we're on the gallery tab
      const galleryTab = document.getElementById('gallery-tab');
      if (!galleryTab.classList.contains('hidden')) {
        fetchAndRenderGallery(1);
      }

      // Refresh dashboard stats
      fetchDashboardData();

    } else {
      throw new Error(result.error || (isEditing ? 'Update failed' : 'Upload failed'));
    }
  } catch (error) {
    console.error('Upload error:', error);
    showToast('Error ' + (isEditing ? 'updating' : 'uploading') + ' image: ' + error.message, 'error');
  } finally {
    // Restore button state
    const submitBtn = document.getElementById('submit-image-btn');
    if (submitBtn) {
      submitBtn.textContent = isEditing ? 'Update Image' : 'Add Image';
      submitBtn.disabled = false;
    }
  }
}

// Reset image modal to add mode
function resetImageModal() {
  const form = document.getElementById('add-image-form');
  form.reset();
  form.removeAttribute('data-edit-id');

  // Reset modal title and button
  document.querySelector('#add-image-modal h3').textContent = 'Add New Image';
  document.getElementById('submit-image-btn').textContent = 'Add Image';

  // Reset file input to required and original labels
  const fileInput = document.getElementById('modal-image-file');
  const fileLabel = document.getElementById('image-file-label');
  const fileHelp = document.getElementById('image-file-help');

  fileInput.setAttribute('required', '');
  fileLabel.textContent = 'Upload Image';
  fileHelp.classList.add('hidden');
}

// Settings Management Functions
async function loadSettings() {
  try {
    const response = await fetch('../dbs/settings.php');
    const result = await response.json();

    if (result.success) {
      const settings = result.data;

      // Populate form fields with current settings
      const maxImageSize = document.getElementById('max-image-size');
      const maxVideoSize = document.getElementById('max-video-size');
      const autoOptimize = document.getElementById('auto-optimize');
      const requireApproval = document.getElementById('require-approval');

      if (maxImageSize) maxImageSize.value = settings.max_image_size || 10;
      if (maxVideoSize) maxVideoSize.value = settings.max_video_size || 100;
      if (autoOptimize) autoOptimize.checked = settings.auto_optimize === '1' || settings.auto_optimize === true;
      if (requireApproval) requireApproval.checked = settings.require_approval === '1' || settings.require_approval === true;

      console.log('Settings loaded:', settings);
    }
  } catch (error) {
    console.error('Error loading settings:', error);
    showToast('Failed to load settings', 'error');
  }
}

async function saveSettings() {
  try {
    const maxImageSize = document.getElementById('max-image-size');
    const maxVideoSize = document.getElementById('max-video-size');
    const autoOptimize = document.getElementById('auto-optimize');
    const requireApproval = document.getElementById('require-approval');

    const settingsData = {
      settings: {
        max_image_size: maxImageSize ? maxImageSize.value : 10,
        max_video_size: maxVideoSize ? maxVideoSize.value : 100,
        auto_optimize: autoOptimize ? (autoOptimize.checked ? '1' : '0') : '0',
        require_approval: requireApproval ? (requireApproval.checked ? '1' : '0') : '0'
      }
    };

    console.log('Saving settings:', settingsData);

    const response = await fetch('../dbs/settings.php', {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(settingsData)
    });

    const result = await response.json();

    if (result.success) {
      showToast('Settings saved successfully!', 'success');
    } else {
      throw new Error(result.error || 'Failed to save settings');
    }
  } catch (error) {
    console.error('Error saving settings:', error);
    showToast('Failed to save settings: ' + error.message, 'error');
  }
}

// Initialize tooltips and modals
if (typeof Flowbite !== 'undefined') {
  // Flowbite initialization if needed
}

// Add modal backdrop event listeners
document.addEventListener('click', function (event) {
  // Close add category modal when clicking backdrop
  const addCategoryModal = document.getElementById('add-category-modal');
  if (addCategoryModal && event.target === addCategoryModal) {
    closeAddCategoryModal();
  }

  // Close edit category modal when clicking backdrop
  const editCategoryModal = document.getElementById('edit-category-modal');
  if (editCategoryModal && event.target === editCategoryModal) {
    closeEditCategoryModal();
  }
});

// Add keyboard event listeners for modal closing
document.addEventListener('keydown', function (event) {
  if (event.key === 'Escape') {
    // Close add category modal on Escape
    const addCategoryModal = document.getElementById('add-category-modal');
    if (addCategoryModal && !addCategoryModal.classList.contains('hidden')) {
      closeAddCategoryModal();
    }

    // Close edit category modal on Escape
    const editCategoryModal = document.getElementById('edit-category-modal');
    if (editCategoryModal && !editCategoryModal.classList.contains('hidden')) {
      closeEditCategoryModal();
    }
  }
});

// Logout function
function logout() {
  if (confirm('Are you sure you want to logout?')) {
    window.location.href = '../dbs/auth/logout.php';
  }
}