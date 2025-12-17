<?= view('branch/templates/header', ['page_title' => $page_title]) ?>

<div class="px-6 py-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Branch Orders</h1>
                <p class="text-gray-600 mt-2">Manage orders for your branch</p>
            </div>
            <div class="flex items-center space-x-4">
                <select id="statusFilter" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="accepted">Accepted</option>
                    <option value="completed">Completed</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
        </div>

        <!-- Orders List -->
        <?php if (!empty($orders)): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Order ID
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Items
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Vehicle
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($orders as $order): ?>
                                <tr class="hover:bg-gray-50 order-row" data-status="<?= esc($order['order_status']) ?>">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= esc($order['booking_reference']) ?>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            ID: #<?= $order['id'] ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?= date('M d, Y', strtotime($order['created_at'])) ?>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            <?= date('h:i A', strtotime($order['created_at'])) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?= count($order['items']) ?> item(s)
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            <?php
                                            $itemNames = array_slice(array_column($order['items'], 'listing_title'), 0, 2);
                                            echo esc(implode(', ', $itemNames));
                                            if (count($order['items']) > 2) echo '...';
                                            ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?= esc($order['vehicle_model'] ?? 'N/A') ?>
                                        </div>
                                        <?php if (!empty($order['estimated_arrival'])): ?>
                                            <div class="text-xs text-gray-500">
                                                ETA: <?= date('M d, h:i A', strtotime($order['estimated_arrival'])) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'accepted' => 'bg-blue-100 text-blue-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800'
                                        ];
                                        $statusColor = $statusColors[$order['order_status']] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusColor ?>">
                                            <?= ucfirst($order['order_status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="<?= base_url('branch/orders/view/' . $order['id']) ?>" 
                                           class="text-green-600 hover:text-green-900 mr-3">
                                            <i class="fas fa-eye mr-1"></i>View
                                        </a>
                                        <?php if ($order['order_status'] === 'pending'): ?>
                                            <button onclick="updateStatus(<?= $order['id'] ?>, 'accepted')" 
                                                    class="text-blue-600 hover:text-blue-900 mr-3">
                                                <i class="fas fa-check mr-1"></i>Accept
                                            </button>
                                            <button onclick="updateStatus(<?= $order['id'] ?>, 'rejected')" 
                                                    class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-times mr-1"></i>Reject
                                            </button>
                                        <?php elseif ($order['order_status'] === 'accepted'): ?>
                                            <button onclick="updateStatus(<?= $order['id'] ?>, 'completed')" 
                                                    class="text-green-600 hover:text-green-900">
                                                <i class="fas fa-check-circle mr-1"></i>Complete
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Summary Stats -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500">Total Orders</div>
                    <div class="text-2xl font-bold text-gray-900"><?= count($orders) ?></div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500">Pending</div>
                    <div class="text-2xl font-bold text-yellow-600">
                        <?= count(array_filter($orders, fn($o) => $o['order_status'] === 'pending')) ?>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500">Completed</div>
                    <div class="text-2xl font-bold text-green-600">
                        <?= count(array_filter($orders, fn($o) => $o['order_status'] === 'completed')) ?>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Orders Yet</h3>
                <p class="text-gray-600">Orders for your branch will appear here</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Status filter
    document.getElementById('statusFilter').addEventListener('change', function() {
        const selectedStatus = this.value;
        const rows = document.querySelectorAll('.order-row');
        
        rows.forEach(row => {
            if (selectedStatus === '' || row.dataset.status === selectedStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Update order status
    function updateStatus(orderId, newStatus) {
        if (!confirm(`Are you sure you want to ${newStatus} this order?`)) {
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= base_url('branch/orders/update-status/') ?>' + orderId;

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '<?= csrf_token() ?>';
        csrfInput.value = '<?= csrf_hash() ?>';
        form.appendChild(csrfInput);

        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = newStatus;
        form.appendChild(statusInput);

        document.body.appendChild(form);
        form.submit();
    }
</script>

<?= view('branch/templates/footer') ?>

