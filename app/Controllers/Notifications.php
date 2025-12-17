<?php

namespace App\Controllers;

use App\Services\NotificationService;
use CodeIgniter\RESTful\ResourceController;

class Notifications extends ResourceController
{
    protected $notificationService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }

    /**
     * Get notifications for the current user (AJAX endpoint)
     */
    public function index()
    {
        // Check if user is logged in
        if (!session()->get('is_logged_in') && !session()->get('isLoggedIn')) {
            return $this->response->setJSON(['error' => 'Not authenticated'])->setStatusCode(401);
        }

        $userType = session()->get('user_type');
        $userId = session()->get('user_id');
        $unreadOnly = $this->request->getGet('unread_only') === 'true';
        $limit = (int) ($this->request->getGet('limit') ?? 20);

        $notifications = $this->notificationService->getNotifications($userType, $userId, $limit, $unreadOnly);
        $unreadCount = $this->notificationService->getUnreadCount($userType, $userId);

        return $this->response->setJSON([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
            'success' => true
        ]);
    }

    /**
     * Get unread notification count (AJAX endpoint)
     */
    public function unreadCount()
    {
        if (!session()->get('is_logged_in') && !session()->get('isLoggedIn')) {
            return $this->response->setJSON(['error' => 'Not authenticated'])->setStatusCode(401);
        }

        $userType = session()->get('user_type');
        $userId = session()->get('user_id');

        $count = $this->notificationService->getUnreadCount($userType, $userId);

        return $this->response->setJSON([
            'unread_count' => $count,
            'success' => true
        ]);
    }

    /**
     * Mark notification as read (AJAX endpoint)
     */
    public function markAsRead($notificationId = null)
    {
        if (!session()->get('is_logged_in') && !session()->get('isLoggedIn')) {
            return $this->response->setJSON(['error' => 'Not authenticated'])->setStatusCode(401);
        }

        if (!$notificationId) {
            return $this->response->setJSON(['error' => 'Notification ID required'])->setStatusCode(400);
        }

        $success = $this->notificationService->markAsRead($notificationId);

        return $this->response->setJSON([
            'success' => $success,
            'message' => $success ? 'Notification marked as read' : 'Failed to mark notification as read'
        ]);
    }

    /**
     * Mark all notifications as read (AJAX endpoint)
     */
    public function markAllAsRead()
    {
        if (!session()->get('is_logged_in') && !session()->get('isLoggedIn')) {
            return $this->response->setJSON(['error' => 'Not authenticated'])->setStatusCode(401);
        }

        $userType = session()->get('user_type');
        $userId = session()->get('user_id');

        $success = $this->notificationService->markAllAsRead($userType, $userId);

        return $this->response->setJSON([
            'success' => $success,
            'message' => $success ? 'All notifications marked as read' : 'Failed to mark notifications as read'
        ]);
    }

    /**
     * Show notifications page for drivers
     */
    public function driverNotifications()
    {
        if ((!session()->get('is_logged_in') && !session()->get('isLoggedIn')) || session()->get('user_type') !== 'driver') {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id');
        
        // Pagination parameters
        $perPage = 10; // Number of notifications per page
        $page = (int) ($this->request->getGet('page') ?? 1);
        $offset = ($page - 1) * $perPage;
        
        // Get notifications with pagination
        $notifications = $this->notificationService->getNotifications('driver', $userId, $perPage, false, $offset);
        $totalNotifications = $this->notificationService->getTotalNotifications('driver', $userId);
        $unreadCount = $this->notificationService->getUnreadCount('driver', $userId);
        
        // Calculate pagination info
        $totalPages = ceil($totalNotifications / $perPage);
        $hasNextPage = $page < $totalPages;
        $hasPrevPage = $page > 1;

        $data = [
            'page_title' => 'Notifications',
            'title' => 'Notifications',
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
            'total_notifications' => $totalNotifications,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'per_page' => $perPage,
            'has_next_page' => $hasNextPage,
            'has_prev_page' => $hasPrevPage,
            'next_page' => $hasNextPage ? $page + 1 : null,
            'prev_page' => $hasPrevPage ? $page - 1 : null
        ];

        return view('driver/notifications', $data);
    }

    /**
     * Show notifications page for merchants
     */
    public function merchantNotifications()
    {
        if ((!session()->get('is_logged_in') && !session()->get('isLoggedIn')) || session()->get('user_type') !== 'merchant') {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id');
        
        // Pagination parameters
        $perPage = 10; // Number of notifications per page
        $page = (int) ($this->request->getGet('page') ?? 1);
        $offset = ($page - 1) * $perPage;
        
        // Get notifications with pagination
        $notifications = $this->notificationService->getNotifications('merchant', $userId, $perPage, false, $offset);
        $totalNotifications = $this->notificationService->getTotalNotifications('merchant', $userId);
        $unreadCount = $this->notificationService->getUnreadCount('merchant', $userId);
        
        // Calculate pagination info
        $totalPages = ceil($totalNotifications / $perPage);
        $hasNextPage = $page < $totalPages;
        $hasPrevPage = $page > 1;

        $data = [
            'title' => 'Notifications',
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
            'total_notifications' => $totalNotifications,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'per_page' => $perPage,
            'has_next_page' => $hasNextPage,
            'has_prev_page' => $hasPrevPage,
            'next_page' => $hasNextPage ? $page + 1 : null,
            'prev_page' => $hasPrevPage ? $page - 1 : null
        ];

        return view('merchant/notifications', $data);
    }
}
