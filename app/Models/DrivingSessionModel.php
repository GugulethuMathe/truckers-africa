<?php

namespace App\Models;

use CodeIgniter\Model;

class DrivingSessionModel extends Model
{
    protected $table = 'driving_sessions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = [
        'truck_driver_id',
        'session_start_time',
        'session_end_time',
        'status',
        'last_notified_at'
    ];

    /**
     * Start a new driving session for a truck driver.
     * @param int $truckDriverId
     * @return int|false The ID of the new session or false on failure.
     */
    public function start(int $truckDriverId)
    {
        $data = [
            'truck_driver_id'    => $truckDriverId,
            'session_start_time' => date('Y-m-d H:i:s'),
            'status'             => 'active'
        ];
        return $this->insert($data);
    }

    /**
     * Get the current active driving session for a driver.
     * @param int $truckDriverId
     * @return array|null
     */
    public function findActiveByDriverId(int $truckDriverId): ?array
    {
        return $this->select('*, TIMESTAMPDIFF(MINUTE, session_start_time, NOW()) AS active_minutes')
                    ->where('truck_driver_id', $truckDriverId)
                    ->where('status', 'active')
                    ->orderBy('session_start_time', 'DESC')
                    ->first();
    }

    /**
     * Update the status of a driving session.
     * @param int $sessionId
     * @param string $status 'paused' or 'completed'
     * @return bool
     */
    public function updateStatus(int $sessionId, string $status): bool
    {
        $data = ['status' => $status];
        if ($status === 'completed') {
            $data['session_end_time'] = date('Y-m-d H:i:s');
        }

        return $this->update($sessionId, $data);
    }

    /**
     * Update the last_notified_at timestamp for a session.
     * @param int $sessionId
     * @return bool
     */
    public function updateLastNotifiedAt(int $sessionId): bool
    {
        return $this->update($sessionId, ['last_notified_at' => date('Y-m-d H:i:s')]);
    }
}
