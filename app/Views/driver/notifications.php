<?php echo view('driver/templates/header'); ?>

<div class="min-h-screen bg-gray-50 pb-20">
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Notifications</h1>
                <p class="text-gray-600">Stay updated with your orders and activities</p>
            </div>
            <?php if ($unread_count > 0): ?>
            <button onclick="markAllAsRead()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                Mark All Read
            </button>
            <?php endif; ?>
        </div>
        <style>
        /* Mobile button sizing tweaks */
        @media (max-width: 480px) {
            button,
            a[class*="rounded"][class*="px-"][class*="py-"] {
                padding: 6px 10px !important;
                font-size: 12px !important;
                line-height: 1.2 !important;
                border-radius: 6px;
            }
        }
        </style>

        <!-- Notification Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow-sm border">
                <div class="flex items-center">
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-bell text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Total Notifications</p>
                        <p class="text-2xl font-bold text-gray-800"><?= $total_notifications ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border">
                <div class="flex items-center">
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-exclamation-circle text-red-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Unread</p>
                        <p class="text-2xl font-bold text-gray-800"><?= $unread_count ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border">
                <div class="flex items-center">
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Read</p>
                        <p class="text-2xl font-bold text-gray-800"><?= $total_notifications - $unread_count ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="bg-white rounded-lg shadow-sm border">
            <?php if (!empty($notifications)): ?>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-item p-4 hover:bg-gray-50 transition-colors <?= $notification['is_read'] ? '' : 'bg-blue-50 border-l-4 border-l-blue-500' ?>" 
                             data-id="<?= $notification['id'] ?>">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <!-- Notification Icon -->
                                        <div class="mr-3">
                                            <?php
                                            $iconClass = 'fas fa-bell text-gray-400';
                                            $bgClass = 'bg-gray-100';
                                            
                                            switch ($notification['notification_type']) {
                                                case 'booking_request':
                                                    $iconClass = 'fas fa-shopping-cart text-blue-600';
                                                    $bgClass = 'bg-blue-100';
                                                    break;
                                                case 'booking_accepted':
                                                    $iconClass = 'fas fa-check-circle text-green-600';
                                                    $bgClass = 'bg-green-100';
                                                    break;
                                                case 'booking_rejected':
                                                    $iconClass = 'fas fa-times-circle text-red-600';
                                                    $bgClass = 'bg-red-100';
                                                    break;
                                                case 'booking_completed':
                                                    $iconClass = 'fas fa-flag-checkered text-purple-600';
                                                    $bgClass = 'bg-purple-100';
                                                    break;
                                                default:
                                                    $iconClass = 'fas fa-info-circle text-gray-600';
                                                    $bgClass = 'bg-gray-100';
                                            }
                                            ?>
                                            <div class="<?= $bgClass ?> p-2 rounded-full">
                                                <i class="<?= $iconClass ?>"></i>
                                            </div>
                                        </div>
                                        
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-800 <?= $notification['is_read'] ? '' : 'font-bold' ?>">
                                                <?= esc($notification['title']) ?>
                                            </h3>
                                            <p class="text-sm text-gray-600 mt-1">
                                                <?= esc($notification['message']) ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center justify-between mt-3">
                                        <div class="flex items-center text-xs text-gray-500">
                                            <i class="fas fa-clock mr-1"></i>
                                            <?= date('M j, Y \a\t g:i A', strtotime($notification['created_at'])) ?>
                                            
                                            <!-- Priority Badge -->
                                            <?php if ($notification['priority'] !== 'normal'): ?>
                                                <?php
                                                $priorityClass = 'bg-gray-100 text-gray-600';
                                                switch ($notification['priority']) {
                                                    case 'high':
                                                        $priorityClass = 'bg-orange-100 text-orange-600';
                                                        break;
                                                    case 'urgent':
                                                        $priorityClass = 'bg-red-100 text-red-600';
                                                        break;
                                                }
                                                ?>
                                                <span class="ml-2 px-2 py-1 rounded-full text-xs font-medium <?= $priorityClass ?>">
                                                    <?= ucfirst($notification['priority']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="flex items-center space-x-2">
                                            <?php if (!$notification['is_read']): ?>
                                                <button onclick="markAsRead(<?= $notification['id'] ?>)" 
                                                        class="text-xs text-blue-600 hover:text-blue-800">
                                                    Mark as Read
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($notification['action_url'])): ?>
                                                <a href="<?= esc($notification['action_url']) ?>" 
                                                   class="text-xs bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 transition-colors"
                                                   onclick="markAsRead(<?= $notification['id'] ?>)">
                                                    View Details
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <div class="mb-4">
                        <i class="fas fa-bell-slash text-6xl text-gray-300"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-800 mb-2">No Notifications</h3>
                    <p class="text-gray-600">You're all caught up! New notifications will appear here.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="mt-6 flex items-center justify-between bg-white px-4 py-3 rounded-lg shadow-sm border">
            <div class="flex flex-1 justify-between sm:hidden">
                <!-- Mobile pagination -->
                <?php if ($has_prev_page): ?>
                    <a href="<?= current_url() ?>?page=<?= $prev_page ?>" 
                       class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Previous
                    </a>
                <?php else: ?>
                    <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed">
                        Previous
                    </span>
                <?php endif; ?>
                
                <?php if ($has_next_page): ?>
                    <a href="<?= current_url() ?>?page=<?= $next_page ?>" 
                       class="relative ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Next
                    </a>
                <?php else: ?>
                    <span class="relative ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed">
                        Next
                    </span>
                <?php endif; ?>
            </div>
            
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing
                        <span class="font-medium"><?= (($current_page - 1) * $per_page) + 1 ?></span>
                        to
                        <span class="font-medium"><?= min($current_page * $per_page, $total_notifications) ?></span>
                        of
                        <span class="font-medium"><?= $total_notifications ?></span>
                        results
                    </p>
                </div>
                <div>
                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                        <!-- Previous button -->
                        <?php if ($has_prev_page): ?>
                            <a href="<?= current_url() ?>?page=<?= $prev_page ?>" 
                               class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                <span class="sr-only">Previous</span>
                                <i class="fas fa-chevron-left h-5 w-5" aria-hidden="true"></i>
                            </a>
                        <?php else: ?>
                            <span class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-300 ring-1 ring-inset ring-gray-300 cursor-not-allowed">
                                <span class="sr-only">Previous</span>
                                <i class="fas fa-chevron-left h-5 w-5" aria-hidden="true"></i>
                            </span>
                        <?php endif; ?>
                        
                        <!-- Page numbers -->
                        <?php 
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);
                        
                        if ($start_page > 1): ?>
                            <a href="<?= current_url() ?>?page=1" 
                               class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                1
                            </a>
                            <?php if ($start_page > 2): ?>
                                <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 focus:outline-offset-0">...</span>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <?php if ($i == $current_page): ?>
                                <span class="relative z-10 inline-flex items-center bg-blue-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                    <?= $i ?>
                                </span>
                            <?php else: ?>
                                <a href="<?= current_url() ?>?page=<?= $i ?>" 
                                   class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                    <?= $i ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($end_page < $total_pages): ?>
                            <?php if ($end_page < $total_pages - 1): ?>
                                <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 focus:outline-offset-0">...</span>
                            <?php endif; ?>
                            <a href="<?= current_url() ?>?page=<?= $total_pages ?>" 
                               class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                <?= $total_pages ?>
                            </a>
                        <?php endif; ?>
                        
                        <!-- Next button -->
                        <?php if ($has_next_page): ?>
                            <a href="<?= current_url() ?>?page=<?= $next_page ?>" 
                               class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                <span class="sr-only">Next</span>
                                <i class="fas fa-chevron-right h-5 w-5" aria-hidden="true"></i>
                            </a>
                        <?php else: ?>
                            <span class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-300 ring-1 ring-inset ring-gray-300 cursor-not-allowed">
                                <span class="sr-only">Next</span>
                                <i class="fas fa-chevron-right h-5 w-5" aria-hidden="true"></i>
                            </span>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript for notification management -->
<script>
function markAsRead(notificationId) {
    fetch(`<?= site_url('notifications/read/') ?>${notificationId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the notification item visually
            const notificationItem = document.querySelector(`[data-id="${notificationId}"]`);
            if (notificationItem) {
                notificationItem.classList.remove('bg-blue-50', 'border-l-4', 'border-l-blue-500');
                const markReadBtn = notificationItem.querySelector('button[onclick*="markAsRead"]');
                if (markReadBtn) {
                    markReadBtn.remove();
                }
            }
            
            // Update header notification count if it exists
            updateNotificationCount();
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

function markAllAsRead() {
    fetch('<?= site_url('notifications/read-all') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload the page to show updated state
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error marking all notifications as read:', error);
    });
}

function updateNotificationCount() {
    // Update header notification count if the function exists
    if (typeof updateHeaderNotificationCount === 'function') {
        updateHeaderNotificationCount();
    }
}
</script>

<?php 
$current_page = 'notifications';
echo view('driver/templates/bottom_nav', ['current_page' => $current_page]); 
?>

<?php echo view('driver/templates/footer'); ?>
