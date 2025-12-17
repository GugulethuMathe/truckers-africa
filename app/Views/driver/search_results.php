<?= view('driver/templates/header', ['page_title' => $page_title]) ?>
<?php helper('currency'); ?>
<style>
/* Mobile button sizing tweaks */
@media (max-width: 480px) {
    button,
    .category-filter,
    #searchBtn,
    a[class*="rounded"][class*="px-"][class*="py-"] {
        padding: 6px 10px !important;
        font-size: 12px !important;
        line-height: 1.2 !important;
        border-radius: 6px;
    }
}
</style>

<!-- Search Bar (Fixed) -->
<div id="searchContainer" class="fixed top-16 left-4 right-4 z-40">
    <div class="bg-white rounded-lg shadow-lg p-3">
        <!-- Search Input -->
        <div class="flex items-center space-x-3 mb-3">
            <i class="fas fa-search text-gray-400"></i>
            <input type="text" id="searchInput" placeholder="Search for services, e.g., 'tyre repair'" 
                   value="<?= esc($search_term ?? '') ?>" class="flex-1 outline-none text-gray-700">
            <button id="searchBtn" class="bg-green-500 text-white px-4 py-2 rounded-lg text-sm font-medium">Search</button>
        </div>
        
        <!-- Category Filters -->
        <div class="border-t pt-3">
            <p class="text-xs text-gray-500 mb-2">Filter by category:</p>
            <div class="flex flex-wrap gap-2">
                <button class="category-filter <?= empty($category_id) || $category_id === 'all' ? 'active bg-green-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?> px-3 py-1 rounded-full text-xs font-medium" data-category="all">
                    All Services
                </button>
                <?php if (isset($categories) && !empty($categories)): ?>
                    <?php foreach ($categories as $category): ?>
                        <button class="category-filter <?= $category_id == $category['id'] ? 'active bg-green-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?> px-3 py-1 rounded-full text-xs font-medium" data-category="<?= $category['id'] ?>">
                            <?= esc($category['name']) ?>
                        </button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<main class="pt-40 pb-20 px-4">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="<?= base_url('dashboard/driver') ?>" class="inline-flex items-center text-gray-600 hover:text-gray-800">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Dashboard
        </a>
    </div>

    <!-- Search Results Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2"><?= esc($page_title) ?></h1>
        <p class="text-gray-600">
            <?= $total_results ?> result<?= $total_results !== 1 ? 's' : '' ?> found
            <?php if ($search_term): ?>
                for "<span class="font-semibold"><?= esc($search_term) ?></span>"
            <?php endif; ?>
            <?php if ($selected_category): ?>
                in <span class="font-semibold"><?= esc($selected_category['name']) ?></span>
            <?php endif; ?>
        </p>
    </div>

    <!-- Search Results -->
    <?php if (!empty($listings)): ?>
        <div class="grid gap-4">
            <?php foreach ($listings as $listing): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="flex">
                        <!-- Service Image -->
                        <div class="w-24 h-24 flex-shrink-0">
                            <?php if ($listing['main_image_path']): ?>
                                <img src="<?= get_listing_image_url($listing['main_image_path']) ?>"
                                     alt="<?= esc($listing['title']) ?>"
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-image text-gray-400 text-xl"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Service Details -->
                        <div class="flex-1 p-4">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-lg font-semibold text-gray-900 line-clamp-1">
                                    <?= esc($listing['title']) ?>
                                </h3>
                                <?php if (!empty($listing['price'])): ?>
                                    <span class="text-green-600 font-bold text-lg">
                                        <?= display_listing_price($listing) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-500 text-sm">Price on request</span>
                                <?php endif; ?>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-2 line-clamp-2">
                                <?= esc($listing['description']) ?>
                            </p>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-store mr-1"></i>
                                    <span><?= esc($listing['business_name']) ?></span>
                                    <?php if ($listing['category_name']): ?>
                                        <span class="mx-2">•</span>
                                        <span class="bg-gray-100 px-2 py-1 rounded-full text-xs">
                                            <?= esc($listing['category_name']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <a href="<?= base_url('driver/service/' . $listing['id']) ?>" 
                                   class="bg-green-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-600 transition-colors">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <!-- No Results -->
        <div class="text-center py-12">
            <div class="bg-white rounded-lg shadow-md p-8">
                <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Results Found</h3>
                <p class="text-gray-600 mb-6">
                    <?php if ($search_term && $selected_category): ?>
                        No services found for "<?= esc($search_term) ?>" in <?= esc($selected_category['name']) ?>.
                    <?php elseif ($search_term): ?>
                        No services found for "<?= esc($search_term) ?>".
                    <?php elseif ($selected_category): ?>
                        No services found in <?= esc($selected_category['name']) ?> category.
                    <?php else: ?>
                        No services available at the moment.
                    <?php endif; ?>
                </p>
                <div class="space-y-3">
                    <p class="text-sm text-gray-500">Try:</p>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Using different keywords</li>
                        <li>• Selecting a different category</li>
                        <li>• Removing some filters</li>
                    </ul>
                </div>
                <div class="mt-6">
                    <a href="<?= base_url('dashboard/driver') ?>" 
                       class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 transition-colors">
                        <i class="fas fa-home mr-2"></i>
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<script>
// Category filtering functionality (same as dashboard)
function initializeCategoryFilters() {
    const categoryFilters = document.querySelectorAll('.category-filter');
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    
    // Category filter click handlers
    categoryFilters.forEach(filter => {
        filter.addEventListener('click', function() {
            // Remove active class from all filters
            categoryFilters.forEach(f => {
                f.classList.remove('active', 'bg-green-500', 'text-white');
                f.classList.add('bg-gray-100', 'text-gray-700');
            });
            
            // Add active class to clicked filter
            this.classList.add('active', 'bg-green-500', 'text-white');
            this.classList.remove('bg-gray-100', 'text-gray-700');
            
            const categoryId = this.dataset.category;
            filterServices(categoryId, searchInput.value);
        });
    });
    
    // Search functionality
    function handleSearch() {
        const activeCategory = document.querySelector('.category-filter.active').dataset.category;
        const searchTerm = searchInput.value;
        filterServices(activeCategory, searchTerm);
    }
    
    searchBtn.addEventListener('click', handleSearch);
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            handleSearch();
        }
    });
}

// Filter services based on category and search term
function filterServices(categoryId, searchTerm) {
    const params = new URLSearchParams();
    
    if (categoryId && categoryId !== 'all') {
        params.append('category', categoryId);
    }
    
    if (searchTerm && searchTerm.trim() !== '') {
        params.append('search', searchTerm.trim());
    }
    
    // Redirect to search results page
         const url = params.toString() ? 
         `<?= base_url('driver/search') ?>?${params.toString()}` : 
         `<?= base_url('driver/search') ?>`;
    
    window.location.href = url;
}

// Initialize category filters when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeCategoryFilters();
});
</script>

<style>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<?= view('driver/templates/bottom_nav') ?>
