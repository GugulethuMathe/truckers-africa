<?= view('templates/home-header', ['page_title' => 'Admin Reset Password', 'page_class' => 'bg-gray-100']) ?>

<div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="w-full max-w-md px-8 py-10 bg-white rounded-lg shadow-md">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Admin Panel</h1>
            <p class="text-gray-600">Reset your admin password.</p>
        </div>

        <!-- Flash Messages -->
        <?php if (session()->get('message')) : ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <p><?= session()->get('message') ?></p>
            </div>
        <?php endif; ?>

        <?php if (session()->get('error')) : ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <p><?= session()->get('error') ?></p>
            </div>
        <?php endif; ?>

        <?php if (session()->get('errors')) : ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul>
                <?php foreach (session()->get('errors') as $error) : ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('admin/reset-password') ?>" method="post">
            <?= csrf_field() ?>
            
            <input type="hidden" name="token" value="<?= esc($token) ?>">
            <input type="hidden" name="email" value="<?= esc($email) ?>">

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                <input type="password" id="password" name="password" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="••••••••">
                <p class="mt-1 text-sm text-gray-500">Password must be at least 8 characters long.</p>
            </div>

            <div class="mb-6">
                <label for="password_confirm" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                <input type="password" id="password_confirm" name="password_confirm" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="••••••••">
            </div>

            <div class="mb-6">
                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                    Reset Password
                </button>
            </div>
        </form>

        <div class="text-center">
            <p class="text-sm text-gray-600">
                <a href="<?= site_url('admin/login') ?>" class="font-medium text-indigo-600 hover:text-indigo-500">Back to Login</a>
            </p>
        </div>
    </div>
</div>

<?= view('templates/home-footer') ?>
