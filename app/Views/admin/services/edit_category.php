<?= view('admin/templates/header', ['page_title' => 'Edit Service Category']) ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex items-center mb-6">
        <a href="<?= site_url('admin/services/categories') ?>" class="text-indigo-600 hover:text-indigo-800 mr-4">
            <i class="ri-arrow-left-line text-xl"></i>
        </a>
        <h1 class="text-2xl font-bold">Edit Service Category</h1>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md max-w-lg mx-auto">
        <?php if (session()->has('errors')): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul>
                    <?php foreach (session('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('admin/services/categories/update/' . $category['id']) ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-4">
                <label for="name" class="block text-gray-700 font-semibold mb-2">Category Name</label>
                <input type="text" name="name" id="name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-600" value="<?= old('name', $category['name']) ?>" required>
            </div>
            <div class="mb-4">
                <label for="description" class="block text-gray-700 font-semibold mb-2">Description</label>
                <textarea name="description" id="description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-600" required><?= old('description', $category['description']) ?></textarea>
            </div>
            <div class="flex justify-between">
                <a href="<?= site_url('admin/services/categories') ?>" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">Cancel</a>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Update Category</button>
            </div>
        </form>
    </div>
</div>

<?= view('admin/templates/footer') ?>
