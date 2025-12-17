<?= view('admin/templates/header', ['page_title' => 'Service Categories']) ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Service Categories</h1>
        <a href="<?= site_url('admin/services/categories/add') ?>" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
            <i class="ri-add-line mr-1"></i>Add New Category
        </a>
    </div>

    <?php if (session()->has('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?= session('success') ?></span>
        </div>
    <?php endif; ?>

    <?php if (session()->has('error')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?= session('error') ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Category Name</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Description</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="text-left py-3 px-4 font-medium"><?= esc($category['name']) ?></td>
                                <td class="text-left py-3 px-4"><?= esc($category['description']) ?></td>
                                <td class="text-left py-3 px-4">
                                    <a href="<?= site_url('admin/services/categories/edit/' . $category['id']) ?>" class="text-indigo-600 hover:text-indigo-800">
                                        <i class="ri-edit-line"></i> Edit
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center py-8 text-gray-500">
                                <i class="ri-folder-open-line text-4xl mb-2"></i>
                                <p>No categories found.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= view('admin/templates/footer') ?>
