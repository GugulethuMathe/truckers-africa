<?= $this->include('merchant/templates/header') ?>

<div class="p-4 lg:p-6">
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Help & Support</h1>
        <p class="text-gray-600 mt-2">Get in touch with our support team</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Contact Information Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center mb-6">
                <div class="bg-blue-100 rounded-full p-3 mr-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Contact Information</h2>
                    <p class="text-gray-600 text-sm">We're here to help you</p>
                </div>
            </div>

            <!-- Email -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-600 mr-3 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <div>
                        <h3 class="text-sm font-medium text-gray-900 mb-1">Email Address</h3>
                        <a href="mailto:support@truckersafrica.com" class="text-blue-600 hover:text-blue-800 font-medium">
                            support@truckersafrica.com
                        </a>
                    </div>
                </div>
            </div>

            <!-- Phone / WhatsApp -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-green-600 mr-3 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                    <div>
                        <h3 class="text-sm font-medium text-gray-900 mb-1">Phone / WhatsApp</h3>
                        <a href="tel:+27687781223" class="text-green-600 hover:text-green-800 font-medium">
                            +27 68 778 1223
                        </a>
                        <p class="text-xs text-gray-500 mt-1">Available via phone call or WhatsApp</p>
                    </div>
                </div>
            </div>

            <!-- Business Hours -->
            <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-600 mr-3 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h3 class="text-sm font-medium text-gray-900 mb-2">Business Hours</h3>
                        <p class="text-sm text-gray-700">Monday - Friday: 8:00 AM - 5:00 PM (SAST)</p>
                        <p class="text-sm text-gray-700">Saturday: 9:00 AM - 1:00 PM (SAST)</p>
                        <p class="text-sm text-gray-700">Sunday: Closed</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Help Topics Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center mb-6">
                <div class="bg-purple-100 rounded-full p-3 mr-4">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Common Questions</h2>
                    <p class="text-gray-600 text-sm">Quick answers to help you</p>
                </div>
            </div>

            <div class="space-y-4">
                <!-- FAQ Item 1 -->
                <div class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">How do I add a new branch location?</h3>
                    <p class="text-sm text-gray-600">Go to Branches in the sidebar, then click "Add New Location" to set up a new branch.</p>
                </div>

                <!-- FAQ Item 2 -->
                <div class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">How do I upgrade my subscription plan?</h3>
                    <p class="text-sm text-gray-600">Visit the Subscription page and click "Change Plan" to view available plans and upgrade.</p>
                </div>

                <!-- FAQ Item 3 -->
                <div class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">How do I manage incoming orders?</h3>
                    <p class="text-sm text-gray-600">Navigate to Manage Orders to view all orders. You can accept or reject orders from there.</p>
                </div>

                <!-- FAQ Item 4 -->
                <div class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Can't downgrade my plan?</h3>
                    <p class="text-sm text-gray-600">If you have more branches than the new plan allows, contact support for assistance with downgrades.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Support Information -->
    <div class="mt-6 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-6 border border-blue-200">
        <div class="flex items-start">
            <svg class="w-6 h-6 text-blue-600 mr-3 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Need Immediate Assistance?</h3>
                <p class="text-gray-700 mb-3">
                    For urgent matters, we recommend contacting us via WhatsApp for the fastest response.
                    Our support team is committed to helping you resolve any issues quickly and efficiently.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="https://wa.me/27687781223" target="_blank" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                        Chat on WhatsApp
                    </a>
                    <a href="mailto:support@truckersafrica.com" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Send Email
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->include('merchant/templates/footer') ?>
