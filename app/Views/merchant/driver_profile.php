<?= view('merchant/templates/header', ['page_title' => $page_title]) ?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-gray-800 p-6">
            <div class="flex items-center space-x-6">
                <img class="h-24 w-24 rounded-full border-4 border-brand-yellow object-cover" src="<?= base_url('uploads/avatars/' . ($driver['avatar'] ?? 'default.png')) ?>" alt="Driver Avatar">
                <div>
                    <h1 class="text-3xl font-bold text-white"><?= esc($driver['username']) ?></h1>
                    <p class="text-gray-300">Driver Profile</p>
                </div>
            </div>
        </div>
        <div class="p-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Driver Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-gray-700">
                <div>
                    <strong class="block text-gray-500">Full Name:</strong>
                    <p class="text-lg"><?= esc($driver['first_name'] . ' ' . $driver['last_name']) ?></p>
                </div>
                <div>
                    <strong class="block text-gray-500">Email Address:</strong>
                    <p class="text-lg"><?= esc($driver['email']) ?></p>
                </div>
                <div>
                    <strong class="block text-gray-500">Phone Number:</strong>
                    <p class="text-lg"><?= esc($driver['phone_number'] ?? 'Not Provided') ?></p>
                </div>
                <div>
                    <strong class="block text-gray-500">Member Since:</strong>
                    <p class="text-lg"><?= date('M d, Y', strtotime($driver['created_at'])) ?></p>
                </div>
            </div>
            <div class="mt-8 border-t pt-6">
                <a href="<?= site_url('merchant/dashboard/orders') ?>" class="text-blue-600 hover:underline">&larr; Back to Orders</a>
            </div>
        </div>
    </div>
</div>

<?= view('merchant/templates/footer') ?>
