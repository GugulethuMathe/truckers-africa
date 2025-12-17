<?= view('admin/templates/header', ['page_title' => 'Edit Feature']) ?>

<div class="container mx-auto px-4 sm:px-8">
    <div class="py-8">
        <div>
            <h2 class="text-2xl font-semibold leading-tight">Edit Feature: <?= esc($feature['name']) ?></h2>
        </div>
        <div class="-mx-4 sm:-mx-8 px-4 sm:px-8 py-4 overflow-x-auto">
            <div class="inline-block min-w-full shadow rounded-lg overflow-hidden">
                <div class="bg-white p-6">
                    <?php if (session()->getFlashdata('errors')): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Oops!</strong>
                            <span class="block sm:inline">There were some errors with your submission.</span>
                            <ul>
                                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                    <li>- <?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?= site_url('admin/features/update/' . $feature['id']) ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="mb-4">
                            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Feature Name:</label>
                            <input type="text" id="name" name="name" value="<?= old('name', $feature['name']) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <div class="mb-4">
                            <label for="code" class="block text-gray-700 text-sm font-bold mb-2">Feature Code:</label>
                            <input type="text" id="code" name="code" value="<?= old('code', $feature['code']) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="e.g., UNLIMITED_LISTINGS">
                            <p class="text-gray-600 text-xs italic">A unique code for checking this feature programmatically.</p>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description:</label>
                            <textarea id="description" name="description" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?= old('description', $feature['description']) ?></textarea>
                        </div>

                        <div class="flex items-center justify-between">
                            <button type="submit" class="bg-indigo-600 text-white font-bold py-2 px-4 rounded hover:bg-indigo-700 focus:outline-none focus:shadow-outline">
                                Update Feature
                            </button>
                            <a href="<?= site_url('admin/features') ?>" class="inline-block align-baseline font-bold text-sm text-indigo-600 hover:text-indigo-800">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('admin/templates/footer') ?>
