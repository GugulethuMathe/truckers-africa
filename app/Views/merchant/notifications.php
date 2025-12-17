<?php echo view('merchant/templates/header', ['page_title' => 'Notifications']); ?>

            <!-- Main Content -->
            <div class="px-6 py-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
                <?php if ($unread_count > 0): ?>
                <button onclick="markAllAsRead()" class="bg-brand-blue text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-check-double mr-2"></i>
                    Mark All Read
                </button>
                <?php endif; ?>
            </div>

            <!-- Notification Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="bg-blue-100 p-3 rounded-full mr-4">
                            <i class="fas fa-bell text-blue-600"></i>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Total Notifications</p>
                            <p class="text-2xl font-bold text-gray-900"><?= count($notifications) ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="bg-red-100 p-3 rounded-full mr-4">
                            <i class="fas fa-exclamation-circle text-red-600"></i>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Unread</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $unread_count ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="bg-green-100 p-3 rounded-full mr-4">
                            <i class="fas fa-check-circle text-green-600"></i>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Read</p>
                            <p class="text-2xl font-bold text-gray-900"><?= count($notifications) - $unread_count ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-0">
                    <?php if (!empty($notifications)): ?>
                        <?php foreach ($notifications as $index => $notification): ?>
                            <div class="notification-item p-6 <?= $index < count($notifications) - 1 ? 'border-b border-gray-200' : '' ?> 
                                        <?= $notification['is_read'] ? '' : 'bg-blue-50 border-l-4 border-blue-500' ?>" 
                                 data-id="<?= $notification['id'] ?>">
                                <div class="flex items-start">
                                    <!-- Notification Icon -->
                                    <div class="mr-4">
                                        <?php
                                        $iconClass = 'fas fa-bell text-gray-500';
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
                                                $iconClass = 'fas fa-flag-checkered text-indigo-600';
                                                $bgClass = 'bg-indigo-100';
                                                break;
                                            default:
                                                $iconClass = 'fas fa-info-circle text-gray-600';
                                                $bgClass = 'bg-gray-100';
                                        }
                                        ?>
                                        <div class="<?= $bgClass ?> p-3 rounded-full">
                                            <i class="<?= $iconClass ?>"></i>
                                        </div>
                                    </div>
                                    
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start mb-2">
                                            <h3 class="text-lg <?= $notification['is_read'] ? 'font-medium text-gray-900' : 'font-semibold text-gray-900' ?>">
                                                <?= esc($notification['title']) ?>
                                            </h3>
                                            
                                            <!-- Priority Badge -->
                                            <?php if ($notification['priority'] !== 'normal'): ?>
                                                <?php
                                                $priorityClass = 'bg-gray-100 text-gray-800';
                                                switch ($notification['priority']) {
                                                    case 'high':
                                                        $priorityClass = 'bg-yellow-100 text-yellow-800';
                                                        break;
                                                    case 'urgent':
                                                        $priorityClass = 'bg-red-100 text-red-800';
                                                        break;
                                                }
                                                ?>
                                                <span class="px-2 py-1 text-xs font-medium rounded-full <?= $priorityClass ?> uppercase">
                                                    <?= $notification['priority'] ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <p class="text-gray-600 mb-3">
                                            <?= esc($notification['message']) ?>
                                        </p>
                                        
                                        <div class="flex justify-between items-center">
                                            <small class="text-gray-500 text-sm">
                                                <i class="fas fa-clock mr-1"></i>
                                                <?= date('M j, Y \a\t g:i A', strtotime($notification['created_at'])) ?>
                                            </small>
                                            
                                            <div class="flex gap-2">
                                                <?php if (!$notification['is_read']): ?>
                                                    <button onclick="markAsRead(<?= $notification['id'] ?>)" 
                                                            class="px-3 py-1 text-sm border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors">
                                                        Mark as Read
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($notification['action_url'])): ?>
                                                    <a href="<?= esc($notification['action_url']) ?>" 
                                                       class="px-3 py-1 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                                                       onclick="markAsRead(<?= $notification['id'] ?>)">
                                                        <i class="fas fa-external-link-alt mr-1"></i>
                                                        View Details
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <div class="mb-4">
                                <i class="fas fa-bell-slash text-6xl text-gray-400"></i>
                            </div>
                            <h3 class="text-xl font-medium text-gray-600 mb-2">No Notifications</h3>
                            <p class="text-gray-500">You're all caught up! New notifications will appear here.</p>
                        </div>
                    <?php endif; ?>
                </div>
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
                notificationItem.classList.remove('bg-light', 'border-start', 'border-primary', 'border-4');
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

<?php echo view('merchant/templates/footer'); ?>
