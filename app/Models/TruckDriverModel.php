<?php

namespace App\Models;

use CodeIgniter\Model;

class TruckDriverModel extends Model
{
    protected $table = 'truck_drivers';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'name',
        'email',
        'password_hash',
        'google_id',
        'surname',
        'contact_number',
        'whatsapp_number',
        'country_of_residence',
        'profile_image_url',
        'preferred_search_radius_km',
        'vehicle_type',
        'vehicle_registration',
        'license_number',
        'current_latitude',
        'current_longitude',
        'last_location_update',
        'preferred_currency'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    /**
     * Callback function to hash password before inserting/updating.
     * @param array $data
     * @return array
     */
    protected function hashPassword(array $data): array
    {
        if (!isset($data['data']['password'])) {
            return $data;
        }

        $data['data']['password_hash'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        unset($data['data']['password']);

        return $data;
    }

    /**
     * Find a truck driver by Google ID.
     * @param string $googleId
     * @return array|false
     */
    public function findByGoogleId(string $googleId)
    {
        return $this->where('google_id', $googleId)->first();
    }

    public function verifyPassword(string $email, string $password)
    {
        $driver = $this->where('email', $email)->first();

        if ($driver && password_verify($password, $driver['password_hash'])) {
            return $driver;
        }
        return false;
    }
}

