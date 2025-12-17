<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'recipient_type',
        'recipient_id',
        'notification_type',
        'title',
        'message',
        'related_order_id',
        'is_read',
        'read_at',
        'action_url',
        'priority',
        'created_at'
    ];

    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = null;

    protected $validationRules = [
        'recipient_type' => 'required|in_list[merchant,driver,admin]',
        'recipient_id' => 'required|integer',
        'notification_type' => 'required|in_list[booking_request,booking_accepted,booking_rejected,booking_completed,booking_cancelled,service_reminder,general]',
        'title' => 'required|max_length[255]',
        'message' => 'required',
        'priority' => 'in_list[low,normal,high,urgent]'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Custom methods
    public function getNotificationsForUser($recipientType, $recipientId, $limit = 50, $unreadOnly = false, $offset = 0)
    {
        $builder = $this->where('recipient_type', $recipientType)
                        ->where('recipient_id', $recipientId);
        
        if ($unreadOnly) {
            $builder->where('is_read', 0);
        }
        
        return $builder->orderBy('created_at', 'DESC')
                      ->limit($limit, $offset)
                      ->findAll();
    }

    /**
     * Get total count of notifications for user
     */
    public function getTotalNotificationsForUser($recipientType, $recipientId, $unreadOnly = false)
    {
        $builder = $this->where('recipient_type', $recipientType)
                        ->where('recipient_id', $recipientId);
        
        if ($unreadOnly) {
            $builder->where('is_read', 0);
        }
        
        return $builder->countAllResults();
    }

    public function markAsRead($notificationId)
    {
        return $this->update($notificationId, [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function markAllAsRead($recipientType, $recipientId)
    {
        return $this->where('recipient_type', $recipientType)
                    ->where('recipient_id', $recipientId)
                    ->where('is_read', 0)
                    ->set([
                        'is_read' => 1,
                        'read_at' => date('Y-m-d H:i:s')
                    ])
                    ->update();
    }

    public function getUnreadCount($recipientType, $recipientId)
    {
        return $this->where('recipient_type', $recipientType)
                    ->where('recipient_id', $recipientId)
                    ->where('is_read', 0)
                    ->countAllResults();
    }

    public function createNotification($data)
    {
        $notification = [
            'recipient_type' => $data['recipient_type'],
            'recipient_id' => $data['recipient_id'],
            'notification_type' => $data['notification_type'],
            'title' => $data['title'],
            'message' => $data['message'],
            'related_order_id' => $data['related_order_id'] ?? null,
            'action_url' => $data['action_url'] ?? null,
            'priority' => $data['priority'] ?? 'normal',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->insert($notification);
    }

    public function deleteOldNotifications($daysToKeep = 90)
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$daysToKeep} days"));
        return $this->where('created_at <', $cutoffDate)->delete();
    }
}
