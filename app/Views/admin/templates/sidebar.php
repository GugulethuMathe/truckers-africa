<aside class="w-64 flex-shrink-0 bg-white border-r border-gray-200 flex flex-col">
    <!-- Logo -->
    <div class="h-16 flex items-center justify-center border-b border-gray-200">
        <a href="<?= site_url('admin/dashboard') ?>" class="flex items-center space-x-2">
            <img src="<?= base_url('assets/images/logo-icon-black.png') ?>" alt="Truckers Africa Logo" class="h-10 w-10">
            <span class="text-lg font-bold text-gray-900">Admin Panel</span>
        </a>
    </div>
    <!-- Nav Links -->
    <nav class="flex-1 px-4 py-6 space-y-2" x-data="{ merchantsOpen: false, driversOpen: false, listingsOpen: false, servicesOpen: false, subscriptionsOpen: false, emailMarketingOpen: false }">
        <a href="<?= site_url('admin/dashboard') ?>" class="flex items-center px-4 py-2 text-gray-700 bg-gray-200 rounded-md">
            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h12M3.75 3h16.5M3.75 3v16.5M16.5 3c0 1.623-1.377 2.946-2.946 2.946l-1.054.012a2.946 2.946 0 01-2.946-2.946M16.5 3v16.5" /></svg>
            Dashboard
        </a>
        <div>
            <button @click="merchantsOpen = !merchantsOpen" class="w-full flex justify-between items-center px-4 py-2 mt-2 text-gray-600 hover:bg-gray-200 rounded-md">
                <span class="flex items-center">
                    <i class="ri-store-2-line mr-3"></i>
                    Merchants
                </span>
                <i class="ri-arrow-down-s-line transform transition-transform" :class="{ 'rotate-180': merchantsOpen }"></i>
            </button>
            <div x-show="merchantsOpen" x-cloak class="pl-8 mt-2 space-y-2">
                <a href="<?= site_url('admin/merchants/pending') ?>" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-md">Pending Queue</a>
                <a href="<?= site_url('admin/merchants/all') ?>" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-md">All Merchants</a>
            </div>
        </div>
        <div>
            <button @click="driversOpen = !driversOpen" class="w-full flex justify-between items-center px-4 py-2 mt-2 text-gray-600 hover:bg-gray-200 rounded-md">
                <span class="flex items-center">
                    <i class="ri-steering-2-line mr-3"></i>
                    Drivers
                </span>
                <i class="ri-arrow-down-s-line transform transition-transform" :class="{ 'rotate-180': driversOpen }"></i>
            </button>
            <div x-show="driversOpen" x-cloak class="pl-8 mt-2 space-y-2">
                <a href="<?= site_url('admin/drivers/all') ?>" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-md">All Drivers</a>
            </div>
        </div>
        <div>
            <button @click="listingsOpen = !listingsOpen" class="w-full flex justify-between items-center px-4 py-2 mt-2 text-gray-600 hover:bg-gray-200 rounded-md">
                <span class="flex items-center">
                    <i class="ri-list-check-2 mr-3"></i>
                    Listings
                </span>
                <i class="ri-arrow-down-s-line transform transition-transform" :class="{ 'rotate-180': listingsOpen }"></i>
            </button>
            <div x-show="listingsOpen" x-cloak class="pl-8 mt-2 space-y-2">
                <a href="<?= site_url('admin/listings/pending') ?>" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-md">Pending Listings</a>
                <a href="<?= site_url('admin/listings/approved') ?>" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-md">Approved Listings</a>
                <a href="<?= site_url('admin/listings/all') ?>" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-md">All Listings</a>
            </div>
        </div>
        <a href="<?= site_url('admin/services/categories') ?>" class="flex items-center px-4 py-2 mt-2 text-gray-600 hover:bg-gray-200 rounded-md">
            <i class="ri-briefcase-4-line mr-3"></i>
            Categories
        </a>
        <div>
            <button @click="subscriptionsOpen = !subscriptionsOpen" class="w-full flex justify-between items-center px-4 py-2 mt-2 text-gray-600 hover:bg-gray-200 rounded-md">
                <span class="flex items-center">
                    <i class="ri-bank-card-line mr-3"></i>
                    Subscriptions
                </span>
                <i class="ri-arrow-down-s-line transform transition-transform" :class="{ 'rotate-180': subscriptionsOpen }"></i>
            </button>
            <div x-show="subscriptionsOpen" x-cloak class="pl-8 mt-2 space-y-2">
                <a href="<?= site_url('admin/subscriptions') ?>" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-md">All Subscriptions</a>
                <a href="<?= site_url('admin/plans') ?>" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-md">Manage Plans</a>
                <a href="<?= site_url('admin/features') ?>" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-md">Manage Features</a>
            </div>
        </div>

        <div>
            <button @click="emailMarketingOpen = !emailMarketingOpen" class="w-full flex justify-between items-center px-4 py-2 mt-2 text-gray-600 hover:bg-gray-200 rounded-md">
                <span class="flex items-center">
                    <i class="ri-mail-send-line mr-3"></i>
                    Email Marketing
                </span>
                <i class="ri-arrow-down-s-line transform transition-transform" :class="{ 'rotate-180': emailMarketingOpen }"></i>
            </button>
            <div x-show="emailMarketingOpen" x-cloak class="pl-8 mt-2 space-y-2">
                <a href="<?= site_url('admin/email-marketing/leads') ?>" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-md">All Leads</a>
                <a href="<?= site_url('admin/email-marketing/leads/add') ?>" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-md">Add Lead</a>
                <a href="<?= site_url('admin/email-marketing/campaigns') ?>" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-md">Campaigns</a>
                <a href="<?= site_url('admin/email-marketing/campaigns/sent') ?>" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-md">Sent Campaigns</a>
            </div>
        </div>

        <a href="<?= site_url('admin/settings') ?>" class="flex items-center px-4 py-2 mt-2 text-gray-600 hover:bg-gray-200 rounded-md">
                <i class="ri-settings-3-line mr-3"></i>
                Settings
            </a>
        </nav>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- Logout -->
    <div class="px-4 py-4 border-t border-gray-200">
        <a href="<?= site_url('admin/logout') ?>" class="flex items-center px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-md">
            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" /></svg>
            Logout
        </a>
    </div>
</aside>
