<?= view('driver/templates/header', ['page_title' => $title]) ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900"><?= $title ?></h1>
        <a href="/routes/new" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Plan New Route
        </a>
    </div>

    <?php if (empty($routes)): ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No routes found</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by planning your first route.</p>
            <div class="mt-6">
                <a href="/routes/new" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Plan New Route
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($routes as $route): ?>
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Route #<?= $route['id'] ?>
                            </h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Saved
                            </span>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-3 h-3 bg-green-500 rounded-full mt-1"></div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">From:</p>
                                    <p class="text-sm text-gray-600"><?= esc($route['origin_address']) ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-3 h-3 bg-red-500 rounded-full mt-1"></div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">To:</p>
                                    <p class="text-sm text-gray-600"><?= esc($route['destination_address']) ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-center text-sm text-gray-500">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Created: <?= date('M j, Y', strtotime($route['created_at'])) ?>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex space-x-3">
                            <a href="/routes/view/<?= $route['id'] ?>" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-3 rounded-md text-sm font-medium">
                                View Route
                            </a>
                            <button onclick="deleteRoute(<?= $route['id'] ?>)" class="flex-1 bg-red-600 hover:bg-red-700 text-white text-center py-2 px-3 rounded-md text-sm font-medium">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function deleteRoute(routeId) {
    if (confirm('Are you sure you want to delete this route?')) {
        fetch(`/routes/delete/${routeId}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting route: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the route.');
        });
    }
}
</script>

<?= view('driver/templates/footer') ?>
