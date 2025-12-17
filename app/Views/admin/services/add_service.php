<?= view('admin/templates/header', ['page_title' => 'Add Service']) ?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Add New Service</h1>

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

        <form action="<?= site_url('admin/services/create') ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-4">
                <label for="name" class="block text-gray-700 font-semibold mb-2">Service Name</label>
                <input type="text" name="name" id="name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-600" value="<?= old('name') ?>" required>
            </div>
            <div class="mb-4">
                <label for="category_id" class="block text-gray-700 font-semibold mb-2">Category</label>
                <select name="category_id" id="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-600" required>
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= esc($category['id']) ?>" <?= old('category_id') == $category['id'] ? 'selected' : '' ?>><?= esc($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="text-right">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Add Service</button>
            </div>
        </form>
    </div>
</div>

<?= view('admin/templates/footer') ?>
