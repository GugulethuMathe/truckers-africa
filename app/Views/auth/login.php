<?= view('templates/home-header', ['page_title' => 'Login', 'page_class' => 'login-page bg-gray-100']) ?>

<div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="w-full max-w-md px-8 py-10 bg-white rounded-lg shadow-md">
        
        <div class="text-center mb-8">
            <a href="<?= site_url('/') ?>">
                <img src="<?= base_url('assets/images/logo-icon-black.png') ?>" alt="Truckers Africa Logo" class="w-20 h-20 mx-auto mb-2">
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Welcome Back!</h1>
            <p class="text-gray-600">Log in to access your dashboard.</p>
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

        <!-- Display validation errors -->
        <?php if (session()->get('errors')) : ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul>
                <?php foreach (session()->get('errors') as $error) : ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('login') ?>" method="post">
            <?= csrf_field() ?>

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" id="email" name="email" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm" placeholder="you@example.com">
            </div>

            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" name="password" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm" placeholder="••••••••">
            </div>

            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-brand-blue focus:ring-brand-blue border-gray-300 rounded">
                    <label for="remember-me" class="ml-2 block text-sm text-gray-900">Remember me</label>
                </div>
                <div class="text-sm">
                    <a href="<?= site_url('auth/forgot-password') ?>" class="font-medium text-blue-800 hover:text-blue">Forgot your password?</a>
                </div>
            </div>

            <div>
                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-brand-blue hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-blue transition-all">
                    Log In
                </button>
            </div>
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                Don't have an account? <a href="<?= site_url('signup') ?>" class="font-medium text-brand-blue hover:text-blue-800">Sign up</a>
            </p>
        </div>
    </div>
</div>

<?= view('templates/home-footer') ?>
