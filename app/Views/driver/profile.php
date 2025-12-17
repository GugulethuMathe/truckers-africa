<?= view('driver/templates/header', ['page_title' => $page_title]) ?>
<style>
    input {
    color: black;
}
select {
    color: black;
}
</style>
<?php
    // Derive first and last names if surname is empty but name includes both
    $firstName = $driver['name'] ?? '';
    $lastName = $driver['surname'] ?? '';
    if (empty($lastName) && !empty($firstName) && preg_match('/\s+/', $firstName)) {
        $parts = preg_split('/\s+/', trim($firstName), 2);
        $firstName = $parts[0] ?? $firstName;
        $lastName = $parts[1] ?? '';
    }
    // Default WhatsApp number to phone if missing
    $contactNumber = $driver['contact_number'] ?? '';
    $whatsappNumber = !empty($driver['whatsapp_number']) ? $driver['whatsapp_number'] : $contactNumber;
    $avatarName = trim(($firstName ?: '') . ' ' . ($lastName ?: ''));
    // Get stored image path - should be in format: uploads/driver_profiles/filename.ext
    $storedImagePath = $driver['profile_image_url'] ?? '';
?>

<style>
    .profile-image-preview {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 50%;
    }
</style>

<!-- Main Content -->
<main class="pb-20">
        <div class="max-w-2xl mx-auto p-4 space-y-6">
            
            <!-- Success/Error Messages -->
            <?php if (session()->getFlashdata('message')): ?>
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= session()->getFlashdata('message') ?>
                </div>
            <?php endif; ?>
            
            <?php if (session()->getFlashdata('error')): ?>
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('errors')): ?>
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <ul class="list-disc list-inside">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Profile Form -->
            <form action="<?= base_url('profile/driver/update') ?>" method="post" enctype="multipart/form-data" class="space-y-6">
                <?= csrf_field() ?>
                
                <!-- Profile Picture Section -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Profile Picture</h2>
                    <div class="flex items-center space-x-6">
                        <div class="relative">
                            <?php
                                // Generate the correct image URL
                                if (!empty($storedImagePath)) {
                                    // Remove 'public/' prefix if it exists (legacy data)
                                    $cleanPath = preg_replace('#^public/#', '', $storedImagePath);
                                    $imageUrl = base_url($cleanPath);
                                } else {
                                    // Use default avatar
                                    $imageUrl = 'https://ui-avatars.com/api/?name=' . urlencode($avatarName ?: 'Driver') . '&background=10b981&color=fff&size=120';
                                }
                            ?>
                            <img id="profilePreview"
                                 src="<?= esc($imageUrl) ?>"
                                 alt="Profile Picture"
                                 class="profile-image-preview border-4 border-gray-200"
                                 onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=<?= urlencode($avatarName ?: 'Driver') ?>&background=10b981&color=fff&size=120';">
                            <button type="button" onclick="document.getElementById('profileImageInput').click()" 
                                    class="absolute bottom-0 right-0 bg-green-500 text-white rounded-full p-2 hover:bg-green-600 transition-colors">
                                <i class="fas fa-camera text-sm"></i>
                            </button>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900"><?= esc(trim($firstName . ' ' . $lastName)) ?></h3>
                            <p class="text-sm text-gray-500 mb-2">Driver since <?= date('F Y', strtotime($driver['created_at'])) ?></p>
                            <input type="file" id="profileImageInput" name="profile_image" accept="image/*" class="hidden" onchange="previewImage(this)">
                            <p class="text-xs text-gray-400">Click camera icon to change photo</p>
                        </div>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                            <input type="text" id="name" name="name" value="<?= esc($firstName) ?>" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div>
                            <label for="surname" class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                            <input type="text" id="surname" name="surname" value="<?= esc($lastName) ?>" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div class="md:col-span-2">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                            <input type="email" id="email" name="email" value="<?= esc($driver['email']) ?>" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="contact_number" class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                            <input type="tel" id="contact_number" name="contact_number" value="<?= esc($contactNumber) ?>" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div>
                            <label for="whatsapp_number" class="block text-sm font-medium text-gray-700 mb-2">WhatsApp Number</label>
                            <input type="tel" id="whatsapp_number" name="whatsapp_number" value="<?= esc($whatsappNumber) ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                    </div>
                </div>

                <!-- Location & Preferences -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Location & Preferences</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="country_of_residence" class="block text-sm font-medium text-gray-700 mb-2">Country of Residence *</label>
                            <select id="country_of_residence" name="country_of_residence" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">Select Country</option>
                                <option value="South Africa" <?= $driver['country_of_residence'] === 'South Africa' ? 'selected' : '' ?>>South Africa</option>
                                <option value="Nigeria" <?= $driver['country_of_residence'] === 'Nigeria' ? 'selected' : '' ?>>Nigeria</option>
                                <option value="Kenya" <?= $driver['country_of_residence'] === 'Kenya' ? 'selected' : '' ?>>Kenya</option>
                                <option value="Ghana" <?= $driver['country_of_residence'] === 'Ghana' ? 'selected' : '' ?>>Ghana</option>
                                <option value="Tanzania" <?= $driver['country_of_residence'] === 'Tanzania' ? 'selected' : '' ?>>Tanzania</option>
                                <option value="Uganda" <?= $driver['country_of_residence'] === 'Uganda' ? 'selected' : '' ?>>Uganda</option>
                                <option value="Zimbabwe" <?= $driver['country_of_residence'] === 'Zimbabwe' ? 'selected' : '' ?>>Zimbabwe</option>
                                <option value="Botswana" <?= $driver['country_of_residence'] === 'Botswana' ? 'selected' : '' ?>>Botswana</option>
                                <option value="Zambia" <?= $driver['country_of_residence'] === 'Zambia' ? 'selected' : '' ?>>Zambia</option>
                                <option value="Mozambique" <?= $driver['country_of_residence'] === 'Mozambique' ? 'selected' : '' ?>>Mozambique</option>
                            </select>
                        </div>
                        <div>
                            <label for="preferred_search_radius_km" class="block text-sm font-medium text-gray-700 mb-2">Search Radius (km)</label>
                            <select id="preferred_search_radius_km" name="preferred_search_radius_km"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="10" <?= $driver['preferred_search_radius_km'] == 10 ? 'selected' : '' ?>>10 km</option>
                                <option value="25" <?= $driver['preferred_search_radius_km'] == 25 ? 'selected' : '' ?>>25 km</option>
                                <option value="50" <?= $driver['preferred_search_radius_km'] == 50 || !$driver['preferred_search_radius_km'] ? 'selected' : '' ?>>50 km (Default)</option>
                                <option value="100" <?= $driver['preferred_search_radius_km'] == 100 ? 'selected' : '' ?>>100 km</option>
                                <option value="200" <?= $driver['preferred_search_radius_km'] == 200 ? 'selected' : '' ?>>200 km</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">How far to search for services</p>
                        </div>
                    </div>
                </div>

                <!-- Vehicle Information -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Vehicle Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="vehicle_type" class="block text-sm font-medium text-gray-700 mb-2">Vehicle Type & Description</label>
                            <input type="text" id="vehicle_type" name="vehicle_type" 
                                   value="<?= esc($driver['vehicle_type'] ?? '') ?>"
                                   placeholder="e.g., White Volvo Truck, Red Mercedes Semi-Trailer"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <p class="text-xs text-gray-500 mt-1">Describe your vehicle (color, make, model, type)</p>
                        </div>
                        <div>
                            <label for="vehicle_registration" class="block text-sm font-medium text-gray-700 mb-2">Vehicle Registration</label>
                            <input type="text" id="vehicle_registration" name="vehicle_registration" 
                                   value="<?= esc($driver['vehicle_registration'] ?? '') ?>"
                                   placeholder="e.g., ABC123GP"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div class="md:col-span-2">
                            <label for="license_number" class="block text-sm font-medium text-gray-700 mb-2">Driver's License Number</label>
                            <input type="text" id="license_number" name="license_number" 
                                   value="<?= esc($driver['license_number'] ?? '') ?>"
                                   placeholder="Enter your driver's license number"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                    </div>
                </div>

                <!-- Account Statistics -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Account Statistics</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <i class="fas fa-calendar-alt text-2xl text-green-600 mb-2"></i>
                            <div class="text-sm text-gray-600">Member Since</div>
                            <div class="font-semibold text-gray-900"><?= date('M Y', strtotime($driver['created_at'])) ?></div>
                        </div>
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <i class="fas fa-map-marker-alt text-2xl text-blue-600 mb-2"></i>
                            <div class="text-sm text-gray-600">Last Location</div>
                            <div class="font-semibold text-gray-900"><?= $driver['last_location_update'] ? date('M j', strtotime($driver['last_location_update'])) : 'Never' ?></div>
                        </div>
                        <div class="text-center p-4 bg-purple-50 rounded-lg">
                            <i class="fas fa-search text-2xl text-purple-600 mb-2"></i>
                            <div class="text-sm text-gray-600">Search Radius</div>
                            <div class="font-semibold text-gray-900"><?= $driver['preferred_search_radius_km'] ?: 50 ?> km</div>
                        </div>
                        <div class="text-center p-4 bg-orange-50 rounded-lg">
                            <i class="fas fa-truck text-2xl text-orange-600 mb-2"></i>
                            <div class="text-sm text-gray-600">Driver ID</div>
                            <div class="font-semibold text-gray-900">#<?= str_pad($driver['id'], 4, '0', STR_PAD_LEFT) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-4">
                    <button type="submit" class="flex-1 bg-green-500 text-white py-3 px-6 rounded-lg font-semibold hover:bg-green-600 transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Update Profile
                    </button>
                    <a href="<?= base_url('dashboard/driver') ?>" class="flex-1 bg-gray-200 text-gray-700 py-3 px-6 rounded-lg font-semibold hover:bg-gray-300 transition-colors text-center">
                        <i class="fas fa-times mr-2"></i>
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>

    <?php
    $current_page = 'account';
    echo view('driver/templates/bottom_nav', ['current_page' => $current_page]);
    ?>

</div> <!-- Close pt-16 div from header -->

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profilePreview').src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
<style>
    /* Mobile button sizing tweaks */
    @media (max-width: 480px) {
        button,
        a[class*="rounded"][class*="px-"][class*="py-"] {
            padding: 6px 10px !important;
            font-size: 12px !important;
            line-height: 1.2 !important;
            border-radius: 6px;
        }
    }
</style>
</body>
</html>
