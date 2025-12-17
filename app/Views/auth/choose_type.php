<?= view('templates/home-header', ['page_title' => 'Choose Account Type', 'page_class' => 'bg-gray-100']) ?>

<!-- Main container for the centered content -->
<div class="flex flex-col items-center justify-center min-h-screen py-12 px-4">

    <!-- Back Arrow -->
    <div class="w-full max-w-md mb-4">
        <a href="<?= site_url('/') ?>" class="inline-flex items-center text-gray-600 hover:text-gray-900 underline">
            <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Back to Home
        </a>
    </div>

    <!-- Main Content Box -->
    <div class="w-full max-w-md text-center">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Choose Your Account Type</h1>
        <p class="text-gray-600 mb-8">Select your account type to continue.</p>

        <div class="space-y-4">
            <!-- Service Provider Option - MOVED TO TOP -->
            <a href="<?= site_url('signup/merchant') ?>" class="w-full flex items-center text-left p-6 bg-white rounded-lg shadow-md border-2 border-transparent hover:border-brand-blue hover:shadow-lg transition-all underline">
                <div class="flex-shrink-0 p-4 rounded-full mr-5" style="background-color: #e8ecf1; color: #0e2140;">
                    <svg class="w-8 h-8" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.87868 7.12106C4.05025 8.29263 5.94975 8.29263 7.12132 7.12106C7.26529 6.97709 7.39156 6.82213 7.50015 6.65889C8.03763 7.46711 8.95661 7.99977 10 7.99977C11.0435 7.99977 11.9626 7.46697 12.5001 6.65856C12.6087 6.82194 12.7351 6.97702 12.8791 7.12109C14.0507 8.29267 15.9502 8.29267 17.1218 7.12109C18.2933 5.94952 18.2933 4.05003 17.1218 2.87845L16.8291 2.58579C16.454 2.21071 15.9453 2 15.4149 2H4.58552C4.05509 2 3.54638 2.21071 3.17131 2.58579L2.87868 2.87842C1.70711 4.04999 1.70711 5.94949 2.87868 7.12106Z" fill="currentColor" />
                        <path d="M3 9.03223C4.42799 9.74067 6.15393 9.64395 7.50057 8.74205C8.21499 9.22007 9.07471 9.49977 10 9.49977C10.9254 9.49977 11.7852 9.22002 12.4996 8.74191C13.8462 9.64388 15.572 9.74073 17 9.03249V16.5H17.25C17.6642 16.5 18 16.8358 18 17.25C18 17.6642 17.6642 18 17.25 18H12.75C12.3358 18 12 17.6642 12 17.25V13.75C12 13.3358 11.6642 13 11.25 13H8.75C8.33579 13 8 13.3358 8 13.75V17.25C8 17.6642 7.66421 18 7.25 18H2.75C2.33579 18 2 17.6642 2 17.25C2 16.8358 2.33579 16.5 2.75 16.5H3V9.03223Z" fill="currentColor" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-semibold text-lg text-gray-900">Service Provider</h2>
                    <p class="text-gray-600">Offer your services to thousands of truck drivers.</p>
                </div>
            </a>

            <!-- Truck Driver Option -->
            <a href="<?= site_url('signup/driver') ?>" class="w-full flex items-center text-left p-6 bg-white rounded-lg shadow-md border-2 border-transparent hover:border-brand-blue hover:shadow-lg transition-all underline">
                <div class="flex-shrink-0 p-4 rounded-full mr-5" style="background-color: #e8ecf1; color: #0e2140;">
                    <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8.25 18.75C8.25 19.5784 7.57843 20.25 6.75 20.25C5.92157 20.25 5.25 19.5784 5.25 18.75M8.25 18.75C8.25 17.9216 7.57843 17.25 6.75 17.25C5.92157 17.25 5.25 17.9216 5.25 18.75M8.25 18.75H14.25M5.25 18.75H3.375C2.75368 18.75 2.25 18.2463 2.25 17.625V14.2504M19.5 18.75C19.5 19.5784 18.8284 20.25 18 20.25C17.1716 20.25 16.5 19.5784 16.5 18.75M19.5 18.75C19.5 17.9216 18.8284 17.25 18 17.25C17.1716 17.25 16.5 17.9216 16.5 18.75M19.5 18.75L20.625 18.75C21.2463 18.75 21.7537 18.2457 21.7154 17.6256C21.5054 14.218 20.3473 11.0669 18.5016 8.43284C18.1394 7.91592 17.5529 7.60774 16.9227 7.57315H14.25M16.5 18.75H14.25M14.25 7.57315V6.61479C14.25 6.0473 13.8275 5.56721 13.263 5.50863C11.6153 5.33764 9.94291 5.25 8.25 5.25C6.55709 5.25 4.88466 5.33764 3.23698 5.50863C2.67252 5.56721 2.25 6.0473 2.25 6.61479V14.2504M14.25 7.57315V14.2504M14.25 18.75V14.2504M14.25 14.2504H2.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-semibold text-lg text-gray-900">Truck Driver</h2>
                    <p class="text-gray-600">Find services and special offers along your route.</p>
                </div>
            </a>

            <!-- Trucking Company Option -->
            <a href="<?= site_url('signup/driver') ?>" class="w-full flex items-center text-left p-6 bg-white rounded-lg shadow-md border-2 border-transparent hover:border-brand-blue hover:shadow-lg transition-all underline">
                <div class="flex-shrink-0 p-4 rounded-full mr-5" style="background-color: #e8ecf1; color: #0e2140;">
                    <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8.25 18.75C8.25 19.5784 7.57843 20.25 6.75 20.25C5.92157 20.25 5.25 19.5784 5.25 18.75M8.25 18.75C8.25 17.9216 7.57843 17.25 6.75 17.25C5.92157 17.25 5.25 17.9216 5.25 18.75M8.25 18.75H14.25M5.25 18.75H3.375C2.75368 18.75 2.25 18.2463 2.25 17.625V14.2504M19.5 18.75C19.5 19.5784 18.8284 20.25 18 20.25C17.1716 20.25 16.5 19.5784 16.5 18.75M19.5 18.75C19.5 17.9216 18.8284 17.25 18 17.25C17.1716 17.25 16.5 17.9216 16.5 18.75M19.5 18.75L20.625 18.75C21.2463 18.75 21.7537 18.2457 21.7154 17.6256C21.5054 14.218 20.3473 11.0669 18.5016 8.43284C18.1394 7.91592 17.5529 7.60774 16.9227 7.57315H14.25M16.5 18.75H14.25M14.25 7.57315V6.61479C14.25 6.0473 13.8275 5.56721 13.263 5.50863C11.6153 5.33764 9.94291 5.25 8.25 5.25C6.55709 5.25 4.88466 5.33764 3.23698 5.50863C2.67252 5.56721 2.25 6.0473 2.25 6.61479V14.2504M14.25 7.57315V14.2504M14.25 18.75V14.2504M14.25 14.2504H2.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-semibold text-lg text-gray-900">Trucking Company</h2>
                    <p class="text-gray-600">Find services and special offers on our platform</p>
                </div>
            </a>

        </div>
    </div>

    <div class="mt-8 text-sm text-gray-600">
        Already have an account? <a href="<?= site_url('login') ?>" class="font-medium text-brand-blue underline">Login here</a>
    </div>
</div>

<?= view('templates/home-footer') ?>