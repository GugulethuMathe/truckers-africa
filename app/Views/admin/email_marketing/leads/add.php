<?= view('admin/templates/header') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center space-x-4">
        <a href="<?= site_url('admin/email-marketing/leads') ?>" class="text-gray-600 hover:text-gray-900">
            <i class="ri-arrow-left-line text-2xl"></i>
        </a>
        <div>
            <p class="text-gray-600 mt-1">Add a new contact to your email marketing list</p>
        </div>
    </div>
</div>

                <!-- Error Messages -->
                <?php if (session()->has('error')): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                        <?= session('error') ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->has('errors')): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                        <ul class="list-disc list-inside">
                            <?php foreach (session('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Add Lead Form -->
                <div class="bg-white rounded-lg shadow-lg p-6 max-w-4xl">
                    <form action="<?= site_url('admin/email-marketing/leads/store') ?>" method="post">
                        <?= csrf_field() ?>

                        <!-- Personal Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="ri-user-line mr-2 text-blue-600"></i>
                                Personal Information
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        First Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           id="first_name"
                                           name="first_name"
                                           value="<?= old('first_name') ?>"
                                           required
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Last Name
                                    </label>
                                    <input type="text"
                                           id="last_name"
                                           name="last_name"
                                           value="<?= old('last_name') ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                        Email Address <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email"
                                           id="email"
                                           name="email"
                                           value="<?= old('email') ?>"
                                           required
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                                        Phone Number
                                    </label>
                                    <input type="text"
                                           id="phone_number"
                                           name="phone_number"
                                           value="<?= old('phone_number') ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Company Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="ri-building-line mr-2 text-blue-600"></i>
                                Company Information
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Company Name
                                    </label>
                                    <input type="text"
                                           id="company_name"
                                           name="company_name"
                                           value="<?= old('company_name') ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label for="lead_source" class="block text-sm font-medium text-gray-700 mb-2">
                                        Lead Source
                                    </label>
                                    <select id="lead_source"
                                            name="lead_source"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Select source...</option>
                                        <option value="manual" <?= old('lead_source') === 'manual' ? 'selected' : '' ?>>Manual Entry</option>
                                        <option value="website" <?= old('lead_source') === 'website' ? 'selected' : '' ?>>Website</option>
                                        <option value="referral" <?= old('lead_source') === 'referral' ? 'selected' : '' ?>>Referral</option>
                                        <option value="import" <?= old('lead_source') === 'import' ? 'selected' : '' ?>>Import</option>
                                        <option value="social_media" <?= old('lead_source') === 'social_media' ? 'selected' : '' ?>>Social Media</option>
                                        <option value="event" <?= old('lead_source') === 'event' ? 'selected' : '' ?>>Event</option>
                                        <option value="other" <?= old('lead_source') === 'other' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Location Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="ri-map-pin-line mr-2 text-blue-600"></i>
                                Location
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="country" class="block text-sm font-medium text-gray-700 mb-2">
                                        Country
                                    </label>
                                    <input type="text"
                                           id="country"
                                           name="country"
                                           value="<?= old('country') ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                                        City
                                    </label>
                                    <input type="text"
                                           id="city"
                                           name="city"
                                           value="<?= old('city') ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="ri-information-line mr-2 text-blue-600"></i>
                                Additional Information
                            </h3>
                            <div class="space-y-6">
                                <div>
                                    <label for="tags" class="block text-sm font-medium text-gray-700 mb-2">
                                        Tags (comma-separated)
                                    </label>
                                    <input type="text"
                                           id="tags"
                                           name="tags"
                                           value="<?= old('tags') ?>"
                                           placeholder="e.g., trucking, fleet owner, logistics"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <p class="text-sm text-gray-500 mt-1">Separate tags with commas</p>
                                </div>

                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                        Notes
                                    </label>
                                    <textarea id="notes"
                                              name="notes"
                                              rows="4"
                                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?= old('notes') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                            <a href="<?= site_url('admin/email-marketing/leads') ?>" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg flex items-center">
                                <i class="ri-save-line mr-2"></i>
                                Add Lead
                            </button>
                        </div>
                    </form>
                </div>

<?= view('admin/templates/footer') ?>
