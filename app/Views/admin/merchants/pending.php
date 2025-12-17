<?= view('admin/templates/header', ['page_title' => 'Pending Merchant Approvals']) ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Pending Merchant Applications</h1>
        <div class="flex space-x-2">
            <a href="#" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Export CSV</a>
            <a href="<?= site_url('admin/merchants/add') ?>" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">Add Merchant</a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md" role="alert">
            <p class="font-bold">Success</p>
            <p><?= session()->getFlashdata('success') ?></p>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md" role="alert">
            <p class="font-bold">Error</p>
            <p><?= session()->getFlashdata('error') ?></p>
        </div>
    <?php endif; ?>

    <!-- Search and Filter Section -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <form method="GET" action="<?= site_url('admin/merchants/pending') ?>" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-64">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Merchants</label>
                <input type="text"
                       id="search"
                       name="search"
                       value="<?= esc($search) ?>"
                       placeholder="Search by business name, owner, email, phone, or address..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="min-w-48">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Verification Status</label>
                <select id="status"
                        name="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Status</option>
                    <option value="pending" selected>Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    Search
                </button>
                <a href="<?= site_url('admin/merchants/pending') ?>" class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Results Summary -->
    <div class="mb-4">
       <p class="text-gray-600">
    Showing <?= count($merchants) ?> of <?= $totalMerchants ?> merchants
    <?php if (!empty($search)): ?>
        for "<strong><?= esc($search) ?></strong>"
    <?php endif; ?>
    with status "<strong>Pending</strong>" (newest first)
</p>
    </div>

    <!-- Merchants Grid/List -->
    <div class="space-y-6">
        <?php if (!empty($merchants)): ?>
            <?php foreach ($merchants as $merchant): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6">
                        <!-- Header Section -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center space-x-4">
                                <?php if (!empty($merchant['business_image_url'])): ?>
                                    <img src="<?= esc($merchant['business_image_url']) ?>" alt="Business" class="w-16 h-16 rounded-lg object-cover">
                                <?php else: ?>
                                    <div class="w-16 h-16 bg-gray-300 rounded-lg flex items-center justify-center">
                                        <span class="text-xl font-bold text-gray-600"><?= strtoupper(substr($merchant['business_name'], 0, 2)) ?></span>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900"><?= esc($merchant['business_name']) ?></h3>
                                    <p class="text-gray-600">ID: <?= esc($merchant['id']) ?></p>
                                    <div class="flex items-center space-x-2 mt-1">
                                        <?php
                                        $status = esc($merchant['verification_status']);
                                        $badgeClass = '';
                                        switch ($status) {
                                            case 'approved':
                                                $badgeClass = 'bg-green-100 text-green-800';
                                                break;
                                            case 'pending':
                                                $badgeClass = 'bg-yellow-100 text-yellow-800';
                                                break;
                                            case 'rejected':
                                                $badgeClass = 'bg-red-100 text-red-800';
                                                break;
                                            case 'suspended':
                                                $badgeClass = 'bg-orange-100 text-orange-800';
                                                break;
                                            default:
                                                $badgeClass = 'bg-gray-100 text-gray-800';
                                        }
                                        ?>
                                        <span class="<?= $badgeClass ?> py-1 px-3 rounded-full text-xs font-medium"><?= ucfirst($status) ?></span>
                                        <?php if (!empty($merchant['subscription_status'])): ?>
                                            <span class="bg-blue-100 text-blue-800 py-1 px-3 rounded-full text-xs font-medium">
                                                <?= esc(ucfirst($merchant['subscription_status'])) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($merchant['is_visible']): ?>
                                            <span class="bg-green-100 text-green-800 py-1 px-3 rounded-full text-xs font-medium">Visible</span>
                                        <?php else: ?>
                                            <span class="bg-gray-100 text-gray-800 py-1 px-3 rounded-full text-xs font-medium">Hidden</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a href="<?= site_url('admin/merchants/view/' . $merchant['id']) ?>" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">View</a>
                                <a href="<?= site_url('admin/merchants/edit/' . $merchant['id']) ?>" class="text-blue-600 hover:text-blue-900 text-sm font-medium">Edit</a>

                                <?php if ($merchant['verification_status'] === 'pending'): ?>
                                    <a href="<?= site_url('admin/merchants/approve/' . $merchant['id']) ?>"
                                       class="text-green-600 hover:text-green-900 text-sm font-medium"
                                       onclick="return confirm('Are you sure you want to approve this merchant?')">Approve</a>
                                    <a href="<?= site_url('admin/merchants/reject/' . $merchant['id']) ?>"
                                       class="text-red-600 hover:text-red-900 text-sm font-medium"
                                       onclick="return confirm('Are you sure you want to reject this merchant?')">Reject</a>
                                <?php elseif ($merchant['verification_status'] === 'approved'): ?>
                                    <a href="<?= site_url('admin/merchants/suspend/' . $merchant['id']) ?>"
                                       class="text-orange-600 hover:text-orange-900 text-sm font-medium"
                                       onclick="return confirm('Are you sure you want to suspend this merchant?')">Suspend</a>
                                <?php elseif ($merchant['verification_status'] === 'suspended'): ?>
                                    <a href="<?= site_url('admin/merchants/suspend/' . $merchant['id']) ?>"
                                       class="text-green-600 hover:text-green-900 text-sm font-medium"
                                       onclick="return confirm('Are you sure you want to reactivate this merchant?')">Reactivate</a>
                                <?php elseif ($merchant['verification_status'] === 'rejected'): ?>
                                    <a href="<?= site_url('admin/merchants/approve/' . $merchant['id']) ?>"
                                       class="text-green-600 hover:text-green-900 text-sm font-medium"
                                       onclick="return confirm('Are you sure you want to approve this merchant?')">Approve</a>
                                <?php endif; ?>

                                <a href="<?= site_url('admin/merchants/delete/' . $merchant['id']) ?>"
                                   class="text-red-600 hover:text-red-900 text-sm font-medium"
                                   onclick="return confirm('Are you sure you want to permanently delete this merchant? This action cannot be undone.')">Delete</a>
                            </div>
                        </div>

                        <!-- Main Information Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <!-- Owner Information -->
                            <div class="space-y-3">
                                <h4 class="font-semibold text-gray-900 border-b border-gray-200 pb-1">Owner Information</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex items-center">
                                        <?php if (!empty($merchant['profile_image_url'])): ?>
                                            <img src="<?= esc($merchant['profile_image_url']) ?>" alt="Profile" class="w-8 h-8 rounded-full mr-2">
                                        <?php else: ?>
                                            <div class="w-8 h-8 bg-gray-300 rounded-full mr-2 flex items-center justify-center">
                                                <span class="text-xs font-semibold text-gray-600"><?= strtoupper(substr($merchant['owner_name'], 0, 1)) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <span class="font-medium"><?= esc($merchant['owner_name']) ?></span>
                                    </div>
                                    <div class="flex items-center text-gray-600">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        <?= esc($merchant['email']) ?>
                                    </div>
                                    <?php if (!empty($merchant['google_id'])): ?>
                                        <div class="flex items-center text-blue-600">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                            </svg>
                                            Google Account
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="space-y-3">
                                <h4 class="font-semibold text-gray-900 border-b border-gray-200 pb-1">Contact Information</h4>
                                <div class="space-y-2 text-sm">
                                    <?php if (!empty($merchant['business_contact_number'])): ?>
                                        <div class="flex items-center text-gray-600">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                            <?= esc($merchant['business_contact_number']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($merchant['business_whatsapp_number']) && $merchant['business_whatsapp_number'] != $merchant['business_contact_number']): ?>
                                        <div class="flex items-center text-green-600">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.106"/>
                                            </svg>
                                            WhatsApp: <?= esc($merchant['business_whatsapp_number']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($merchant['physical_address'])): ?>
                                        <div class="flex items-start text-gray-600">
                                            <svg class="w-4 h-4 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <span class="flex-1"><?= esc($merchant['physical_address']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($merchant['latitude']) && !empty($merchant['longitude'])): ?>
                                        <div class="flex items-center text-gray-500 text-xs">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                                            </svg>
                                            <?= esc($merchant['latitude']) ?>, <?= esc($merchant['longitude']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Business Information -->
                            <div class="space-y-3">
                                <h4 class="font-semibold text-gray-900 border-b border-gray-200 pb-1">Business Details</h4>
                                <div class="space-y-2 text-sm">
                                    <?php if (!empty($merchant['main_service'])): ?>
                                        <div class="flex items-center text-gray-600">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H8a2 2 0 01-2-2V8a2 2 0 012-2V6"></path>
                                            </svg>
                                            Main Service: <?= esc($merchant['main_service']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($merchant['services'])): ?>
                                        <div class="flex items-start text-gray-600">
                                            <svg class="w-4 h-4 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                            </svg>
                                            <div class="flex-1">
                                                <span class="block">Services:</span>
                                                <div class="flex flex-wrap gap-1 mt-1">
                                                    <?php foreach ($merchant['services'] as $service): ?>
                                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs"><?= esc($service['name']) ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($merchant['plan_name'])): ?>
                                        <div class="flex items-center text-gray-600">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                            </svg>
                                            Plan: <?= esc($merchant['plan_name']) ?> (<?= esc($planCurrencySymbol) ?><?= number_format($merchant['plan_price'], 2) ?>)
                                        </div>
                                    <?php endif; ?>
                                    <div class="flex items-center text-gray-500 text-xs">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Joined: <?= date('M j, Y', strtotime($merchant['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Descriptions -->
                        <?php if (!empty($merchant['profile_description']) || !empty($merchant['business_description'])): ?>
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <?php if (!empty($merchant['profile_description'])): ?>
                                        <div>
                                            <h4 class="font-semibold text-gray-900 mb-2">Profile Description</h4>
                                            <p class="text-sm text-gray-600 leading-relaxed"><?= esc($merchant['profile_description']) ?></p>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($merchant['business_description'])): ?>
                                        <div>
                                            <h4 class="font-semibold text-gray-900 mb-2">Business Description</h4>
                                            <p class="text-sm text-gray-600 leading-relaxed"><?= esc($merchant['business_description']) ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>


                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <p class="text-lg font-medium text-gray-900">No merchants found</p>
                <?php if (!empty($search)): ?>
                    <p class="text-sm text-gray-500 mt-1">Try adjusting your search criteria</p>
                <?php else: ?>
                    <p class="text-sm text-gray-500 mt-1">No merchants have registered yet</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="mt-8 flex items-center justify-between border-t border-gray-200 pt-6">
            <div class="flex-1 flex justify-between sm:hidden">
                <!-- Mobile pagination -->
                <?php if ($currentPage > 1): ?>
                    <a href="<?= site_url('admin/merchants/pending') ?>?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])) ?>"
                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Previous
                    </a>
                <?php endif; ?>
                <?php if ($currentPage < $totalPages): ?>
                    <a href="<?= site_url('admin/merchants/pending') ?>?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>"
                       class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Next
                    </a>
                <?php endif; ?>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing page <span class="font-medium"><?= $currentPage ?></span> of <span class="font-medium"><?= $totalPages ?></span>
                        (<span class="font-medium"><?= $totalMerchants ?></span> pending applications)
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <!-- Previous Page Link -->
                        <?php if ($currentPage > 1): ?>
                            <a href="<?= site_url('admin/merchants/pending') ?>?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])) ?>"
                               class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Previous</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <?php
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $currentPage + 2);

                        if ($startPage > 1): ?>
                            <a href="<?= site_url('admin/merchants/pending') ?>?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>"
                               class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">1</a>
                            <?php if ($startPage > 2): ?>
                                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <?php if ($i == $currentPage): ?>
                                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-indigo-50 text-sm font-medium text-indigo-600"><?= $i ?></span>
                            <?php else: ?>
                                <a href="<?= site_url('admin/merchants/pending') ?>?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                                   class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($endPage < $totalPages): ?>
                            <?php if ($endPage < $totalPages - 1): ?>
                                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>
                            <?php endif; ?>
                            <a href="<?= site_url('admin/merchants/pending') ?>?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>"
                               class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"><?= $totalPages ?></a>
                        <?php endif; ?>

                        <!-- Next Page Link -->
                        <?php if ($currentPage < $totalPages): ?>
                            <a href="<?= site_url('admin/merchants/pending') ?>?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>"
                               class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Next</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Auto-submit form when status filter changes
document.getElementById('status').addEventListener('change', function() {
    this.form.submit();
});

// Add keyboard shortcut for search (Ctrl+K or Cmd+K)
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        document.getElementById('search').focus();
    }
});

// Clear search when Escape is pressed in search field
document.getElementById('search').addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        this.value = '';
        this.form.submit();
    }
});

// Add loading state to search button
document.querySelector('form').addEventListener('submit', function() {
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Searching...';
    submitBtn.disabled = true;

    // Re-enable after a short delay in case of quick response
    setTimeout(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }, 2000);
});
</script>

<?= view('admin/templates/footer') ?>
