<?= view('merchant/templates/header', ['page_title' => 'Change Password']) ?>

<div class="px-6 py-8">
    <div class="max-w-2xl mx-auto">
        
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Change Password</h1>
            <p class="text-gray-600">Update your account password to keep your account secure.</p>
        </div>

        <?php if (session()->has('errors')) : ?>
            <div class="rounded-md bg-red-50 p-4 mb-6 border border-red-300">
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Please correct the following errors:</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            <?php foreach (session('errors') as $error) : ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (session()->has('message')) : ?>
            <div class="rounded-md bg-green-50 p-4 mb-6 border border-green-300">
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800"><?= session('message') ?></h3>
                </div>
            </div>
        <?php endif; ?>

        <?php if (session()->has('error')) : ?>
            <div class="rounded-md bg-red-50 p-4 mb-6 border border-red-300">
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800"><?= session('error') ?></h3>
                </div>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <form action="<?= site_url('profile/merchant/update-password') ?>" method="post">
                <?= csrf_field() ?>

                <div class="space-y-6">
                    <!-- Current Password -->
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                        <div class="mt-1 relative">
                            <input type="password" id="current_password" name="current_password" 
                                   class="block w-full rounded-md border-2 border-gray-400 px-3 py-2 shadow-sm focus:border-brand-blue focus:ring-brand-blue sm:text-sm" 
                                   required>
                            <button type="button" onclick="togglePassword('current_password')" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Enter your current password to confirm your identity.</p>
                    </div>

                    <!-- New Password -->
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                        <div class="mt-1 relative">
                            <input type="password" id="new_password" name="new_password" 
                                   class="block w-full rounded-md border-2 border-gray-400 px-3 py-2 shadow-sm focus:border-brand-blue focus:ring-brand-blue sm:text-sm" 
                                   required>
                            <button type="button" onclick="togglePassword('new_password')" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="mt-2">
                            <div class="text-xs text-gray-500 space-y-1">
                                <p>Password must contain:</p>
                                <ul class="list-disc pl-4 space-y-1">
                                    <li>At least 8 characters</li>
                                    <li>At least one uppercase letter</li>
                                    <li>At least one lowercase letter</li>
                                    <li>At least one number</li>
                                    <li>At least one special character (!@#$%^&*)</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Confirm New Password -->
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                        <div class="mt-1 relative">
                            <input type="password" id="confirm_password" name="confirm_password" 
                                   class="block w-full rounded-md border-2 border-gray-400 px-3 py-2 shadow-sm focus:border-brand-blue focus:ring-brand-blue sm:text-sm" 
                                   required>
                            <button type="button" onclick="togglePassword('confirm_password')" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Re-enter your new password to confirm.</p>
                    </div>
                </div>

                <div class="mt-8 flex justify-end space-x-4">
                    <a href="<?= site_url('merchant/dashboard') ?>" 
                       class="bg-gray-200 text-gray-800 font-semibold py-2 px-4 rounded-lg text-sm hover:bg-gray-300 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="bg-brand-blue text-white font-semibold py-2 px-4 rounded-lg text-sm hover:bg-blue-700 transition-colors">
                        Update Password
                    </button>
                </div>
            </form>
        </div>

        <!-- Security Tips -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Security Tips</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc pl-5 space-y-1">
                            <li>Use a unique password that you don't use elsewhere</li>
                            <li>Consider using a password manager</li>
                            <li>Don't share your password with anyone</li>
                            <li>Change your password regularly</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
    field.setAttribute('type', type);
    
    // Toggle eye icon (optional enhancement)
    const button = field.nextElementSibling;
    const svg = button.querySelector('svg');
    if (type === 'text') {
        svg.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
        `;
    } else {
        svg.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
        `;
    }
}

// Client-side password validation
document.getElementById('new_password').addEventListener('input', function() {
    const password = this.value;
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /\d/.test(password),
        special: /[!@#$%^&*]/.test(password)
    };
    
    // You can add visual feedback here if needed
    console.log('Password requirements:', requirements);
});

// Confirm password validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    
    if (confirmPassword && newPassword !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?= view('merchant/templates/footer') ?>
