<?= view('templates/header', ['page_title' => 'Create an Account']) ?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold text-center mb-6">Create Your Merchant Account</h1>

        <?php if (session()->has('errors')): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul>
                    <?php foreach (session('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('register/create-account') ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-4">
                <label for="name" class="block text-gray-700 font-semibold mb-2">Full Name</label>
                <input type="text" name="name" id="name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-600" value="<?= old('name') ?>" required>
            </div>
            <div class="mb-4">
                <label for="company_name" class="block text-gray-700 font-semibold mb-2">Company Name</label>
                <input type="text" name="company_name" id="company_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-600" value="<?= old('company_name') ?>" required>
            </div>
            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-semibold mb-2">Email Address</label>
                <input type="email" name="email" id="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-600" value="<?= old('email') ?>" required>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-700 font-semibold mb-2">Password</label>
                <input type="password" name="password" id="password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-600" required>
            </div>
            <div class="text-center">
                <p class="text-sm text-gray-600 mb-4">
                    By creating an account, you agree to our
                    <a href="<?= site_url('terms') ?>" class="font-medium text-indigo-600 hover:text-indigo-500">Terms & Conditions</a>.
                </p>
                <button type="submit" class="bg-indigo-600 text-white w-full px-4 py-2 rounded-md hover:bg-indigo-700">Create Account</button>
            </div>
        </form>
    </div>
</div>

<?= view('templates/footer') ?>
