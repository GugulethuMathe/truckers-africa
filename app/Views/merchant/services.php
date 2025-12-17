<?= view('merchant/templates/header', ['page_title' => $page_title]) ?>

<div class="px-6 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-2">Manage My Services</h1>
        <p class="text-gray-600 mb-6">Select all the services your business provides. This information will be used to help drivers find you.</p>
        
        <?php if (session()->get('message')) : ?>
            <div class="rounded-md bg-green-50 p-4 mb-6 border border-green-300">
                <p class="text-sm font-medium text-green-800"><?= session()->get('message') ?></p>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('merchant/services/update') ?>" method="post">
            <?= csrf_field() ?>
            <div class="bg-white p-8 rounded-lg shadow-md">
                <?php foreach($groupedServices as $category): ?>
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold border-b border-gray-200 pb-2 mb-4 text-gray-800"><?= esc($category['name']) ?></h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach($category['services'] as $service): ?>
                                <div>
                                    <label for="service_<?= $service['id'] ?>" class="flex items-center space-x-3 cursor-pointer">
                                        <input type="checkbox" name="services[]" value="<?= $service['id'] ?>" id="service_<?= $service['id'] ?>"
                                            class="h-5 w-5 rounded border-gray-300 text-brand-blue focus:ring-brand-blue"
                                            <?= in_array($service['id'], $merchantServiceIds) ? 'checked' : '' ?>
                                        >
                                        <span class="text-gray-700"><?= esc($service['name']) ?></span>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="bg-brand-blue text-white font-bold py-3 px-8 rounded-lg hover:bg-opacity-90">Update My Services</button>
            </div>
        </form>
    </div>
</div>

<?= view('merchant/templates/footer') ?>